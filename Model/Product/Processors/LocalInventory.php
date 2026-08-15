<?php

/**
 * RocketWeb
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category  RocketWeb
 * @package   MageOS_ShoppingFeed
 * @copyright Copyright (c) 2023 RocketWeb (http://rocketweb.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author    Rocket Web Inc.
 */
namespace MageOS\ShoppingFeed\Model\Product\Processors;

class LocalInventory extends \MageOS\ShoppingFeed\Model\Product\Processors\ProcessorAbstract
{
    // Dynamic directives which should be cached using inventorySourceId in the cacheKey
    public static $directives = ['directive_availability', 'directive_inventory_source', 'directive_quantity'];

    protected $allowed_parent = [
        \MageOS\ShoppingFeed\Model\Feed\Source\Product\AssociatedMode::ONLY_PARENT,
        \MageOS\ShoppingFeed\Model\Feed\Source\Product\AssociatedMode::BOTH_PARENT_ASSOCIATED
    ];

    protected $allowed_assoc = [
        \MageOS\ShoppingFeed\Model\Feed\Source\Product\AssociatedMode::ONLY_ASSOCIATED,
        \MageOS\ShoppingFeed\Model\Feed\Source\Product\AssociatedMode::BOTH_PARENT_ASSOCIATED
    ];

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;


    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Module\Manager $moduleManager
    ) {
        $this->objectManager = $objectManager;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->moduleManager = $moduleManager;
    }

    /**
     * @param array $rows
     * @return array
     */
    public function execute(array $rows) {
        $items = [];

        if ($this->usesDefaultStock() && $this->isMsiEnabled()) {

            // Get a list of source codes to restrict search of source items by product.
            $websiteCode = $this->getAdapter()->getFeed()->getStore()->getWebsite()->getCode();
            $stockResolver = $this->objectManager->create('Magento\InventorySalesApi\Api\StockResolverInterface');
            $stock = $stockResolver->execute("website", $websiteCode);
            $stockId = (int)$stock->getStockId();

            $sourceCodes = [];
            $searchCriteria = $this->searchCriteriaBuilder->addFilter('stock_id', $stockId)->create();
            $stockLinks = $this->objectManager->create('Magento\InventoryApi\Api\GetStockSourceLinksInterface');
            foreach ($stockLinks->execute($searchCriteria)->getItems() as $link) {
                $sourceCodes[] = $link->getSourceCode();
            }

            // Do not process associated products as part of the parent, they are processed separately here.
            $associatedProductAdapters = $this->getAdapter()->getData('associated_product_adapters');
            // TODO: find another wat to except assciates, some mappers would use associates to generate parent output
            $this->getAdapter()->setData('associated_product_adapters', []);


            $complexMode = \MageOS\ShoppingFeed\Model\Feed\Source\Product\AssociatedMode::ONLY_PARENT;
            if (method_exists($this->getAdapter(), 'getAssociatedProductsMode')) {
                $complexMode = $this->getAdapter()->getAssociatedProductsMode();
            }
            // disable duplicates check so that rows are not removed.
            // TODO: enhance duplicates check so that it does not have to be disabled here
            $this->getAdapter()->setTestMode();

            // Iterate inventory sources for current product
            if (in_array($complexMode, $this->allowed_parent)) {
                $sourceItems = $this->getSourceItems($this->getAdapter()->getProduct(), $sourceCodes);
                // NOTE configurable products hold source item at 'default' source_code only. it may not be linked to website.
                foreach ($sourceItems as $sourceItem) {
                    $adapter = $this->getAdapter();
                    $this->prepareAdapter($adapter, $sourceItem);
                    $items = array_merge($items, $this->getAdapter()->internalMap());
                }
            }

            // Iterate inventory sources for associated products
            if ($associatedProductAdapters && in_array($complexMode, $this->allowed_assoc)) {
                foreach ($associatedProductAdapters as $k => $associatedProductAdapter) {
                    $sourceItems = $this->getSourceItems($associatedProductAdapter->getProduct(), $sourceCodes);
                    foreach ($sourceItems as $sourceItem) {
                        $this->prepareAdapter($associatedProductAdapter, $sourceItem);
                        $this->getAdapter()->setData('associated_product_adapters', [$associatedProductAdapter]);
                        $items = array_merge($items, $this->getAdapter()->mapAssociatedProducts());
                    }
                }
            }
        }

        return !empty($items) ? $items : $rows;
    }

    /**
     * Uses MSI to get source items for a product
     *
     * @param $product
     * @param $sourceCodes
     * @return array
     */
    protected function getSourceItems($product, $sourceCodes) {
        if (!$this->isMsiEnabled()) {
            return [];
        }

        $searchCriteriaSourceItems = $this->searchCriteriaBuilder
            ->addFilter('source_code', $sourceCodes, 'in')
            ->addFilter('sku', $product->getSku(), 'eq')
            ->create();

        $sourceItemsRepository = $this->objectManager->create('Magento\InventoryApi\Api\SourceItemRepositoryInterface');
        return $sourceItemsRepository->getList($searchCriteriaSourceItems)
            ->getItems();
    }

    /**
     * Extends local inventory specific directives cache key to include sourceId
     * and sets the current sourceItem used by Mapper classes to generate output.
     *
     * @param $adapter
     * @param $sourceItem
     */
    protected function prepareAdapter($adapter, $sourceItem) {

        // Add cache key to column in the map for self::$directives
        $map = $adapter->getFeed()->getColumnsMap();
        foreach ($map as $k => $column) {
            if (in_array($column['attribute'], self::$directives)) {
                $map[$k]['cache_key'] = $sourceItem->getSourceItemId();
            }
        }

        // Update the map and set sourceItem context for adapter(s)
        $adapter->getFeed()->setColumnsMap($map);
        $adapter->setData('inventory_source_item', $sourceItem);
        // disable duplicates check so that rows are not removed.
        // TODO: enhance duplicates check so that it does not have to be disabled here
        $adapter->setTestMode();
    }

    /**
     * @return bool
     */
    protected function usesDefaultStock() {
        $useDefaultStock = $this->getAdapter()->getFeed()->getConfig('general_use_default_stock');
        $customStockAttribute = $this->getAdapter()->getFeed()->getConfig('general_stock_attribute_code');
        return $useDefaultStock || (!$useDefaultStock && empty($customStockAttribute));
    }

    /**
     * @return bool
     */
    protected function isMsiEnabled() {
        return $this->moduleManager->isEnabled('Magento_InventoryApi');
    }

}