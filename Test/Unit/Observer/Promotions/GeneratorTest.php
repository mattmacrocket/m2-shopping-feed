<?php

namespace MageOS\ShoppingFeed\Test\Unit\Observer\Promotions;

use MageOS\ShoppingFeed\Model\Promotions\Provider\Map;
use MageOS\ShoppingFeed\Observer\Promotions\Generator;
use PHPUnit\Framework\TestCase;

class GeneratorTest extends TestCase
{
    /**
     * @var Generator
     */
    private $observer;

    protected function setUp(): void
    {
        $this->observer = new Generator(
            $this->createMock('Magento\Framework\Json\Helper\Data'),
            $this->createMock('MageOS\ShoppingFeed\Model\Promotions\Provider'),
            $this->createMock(Map::class),
            $this->createMock('MageOS\ShoppingFeed\Model\Promotions\Provider\Collection'),
            $this->createMock('Magento\SalesRule\Model\RuleFactory'),
            $this->createMock('Magento\Framework\App\Filesystem\DirectoryList')
        );
    }

    public function testHeaderContainsRepeatedCurrentDestinations(): void
    {
        $header = $this->invoke('createFeedHeader');

        $this->assertSame(
            [
                'promotion_id',
                'product_applicability',
                'long_title',
                'promotion_effective_dates',
                'promotion_display_dates',
                'redemption_channel',
                'promotion_destination',
                'promotion_destination',
                'offer_type',
                'generic_redemption_code',
                'minimum_purchase_amount',
            ],
            $header
        );
    }

    public function testLineTargetsOnlineShoppingAdsAndFreeListings(): void
    {
        $rule = $this->createMock('Magento\SalesRule\Model\Rule');
        $map = $this->createMock(Map::class);
        $map->method('mapPromotionId')->willReturn('PROMO_1_9');
        $map->method('mapProductApplicability')->willReturn('all_products');
        $map->method('mapEffectiveDates')->willReturn('effective');
        $map->method('mapDisplayDates')->willReturn('display');
        $map->method('mapOfferType')->willReturn('no_code');
        $map->method('mapGenericRedemptionCode')->willReturn('');
        $map->method('mapMinimumPurchaseAmount')->willReturn('');

        $line = $this->invoke('createFeedLine', [1, $rule, $map, ['title' => 'Summer offer']]);

        $this->assertSame('online', $line[5]);
        $this->assertSame('shopping_ads', $line[6]);
        $this->assertSame('free_listings', $line[7]);
        $this->assertSame('no_code', $line[8]);
    }

    /**
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    private function invoke($method, array $arguments = [])
    {
        $reflection = new \ReflectionMethod($this->observer, $method);
        $reflection->setAccessible(true);
        return $reflection->invokeArgs($this->observer, $arguments);
    }
}
