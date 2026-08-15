<?php

namespace MageOS\ShoppingFeed\Test\Unit\Model\Promotions\Provider;

use MageOS\ShoppingFeed\Model\Promotions\Provider\Map;
use PHPUnit\Framework\TestCase;

class MapTest extends TestCase
{
    public function testUsesCurrentGooglePromotionEnumValues(): void
    {
        $this->assertSame('all_products', Map::APPLICABILITY_ALL);
        $this->assertSame('specific_products', Map::APPLICABILITY_SPECIFIC);
        $this->assertSame('generic_code', Map::GENERIC_CODE);
        $this->assertSame('no_code', Map::NO_CODE);
    }

    public function testMapsCouponTypeToCurrentOfferType(): void
    {
        $serializer = $this->createMock('Magento\Framework\Serialize\SerializerInterface');
        $map = new Map($serializer);
        $rule = $this->getMockBuilder('Magento\SalesRule\Model\Rule')
            ->disableOriginalConstructor()
            ->addMethods(['getCouponType'])
            ->getMock();
        $rule->method('getCouponType')->willReturnOnConsecutiveCalls(1, 2);

        $this->assertSame('no_code', $map->mapOfferType($rule));
        $this->assertSame('generic_code', $map->mapOfferType($rule));
    }
}
