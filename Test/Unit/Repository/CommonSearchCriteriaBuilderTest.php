<?php

declare(strict_types=1);

namespace Yireo\CatalogUtils\Test\Unit\Repository;

use Magento\Framework\Api\Filter;
use Magento\Framework\Api\ObjectFactory;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Store\Api\Data\WebsiteInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Yireo\CatalogUtils\Repository\CommonSearchCriteriaBuilder;

class CommonSearchCriteriaBuilderTest extends TestCase
{
    /**
     * @var Filter[]
     */
    private array $addedFilters = [];

    public function testAddSortByPrimaryKey(): void
    {
        $sortOrderBuilder = $this->getMockBuilder(SortOrderBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $sortOrderBuilder->expects($this->once())
            ->method('setDirection')
            ->with(SortOrder::SORT_DESC)
            ->willReturnSelf();
        $sortOrderBuilder->expects($this->once())
            ->method('setField')
            ->with('entity_id')
            ->willReturnSelf();

        $builder = $this->createBuilder(null, $sortOrderBuilder);
        $this->assertSame($builder, $builder->addSortByPrimaryKey());
    }

    public function testAddWebsiteIdFilterWithExplicitWebsiteId(): void
    {
        $storeManager = $this->createStoreManager(null);
        $storeManager->expects($this->never())->method('getWebsite');

        $builder = $this->createBuilder($storeManager);
        $this->assertTrue($builder->addWebsiteIdFilter(3));

        $builder->create();
        $this->assertCount(1, $this->addedFilters);
        $this->assertFilter($this->addedFilters[0], 'website_id', 'eq', 3);
    }

    public function testAddWebsiteIdFilterFallsBackToCurrentWebsite(): void
    {
        $builder = $this->createBuilder($this->createStoreManager(1));
        $this->assertTrue($builder->addWebsiteIdFilter());

        $builder->create();
        $this->assertCount(1, $this->addedFilters);
        $this->assertFilter($this->addedFilters[0], 'website_id', 'eq', 1);
    }

    public function testAddWebsiteIdFilterWithoutValidWebsiteId(): void
    {
        $builder = $this->createBuilder($this->createStoreManager(0));
        $this->assertFalse($builder->addWebsiteIdFilter());

        $builder->create();
        $this->assertCount(0, $this->addedFilters);
    }

    public function testAddWebsiteIdFilterRejectsNegativeWebsiteId(): void
    {
        $builder = $this->createBuilder($this->createStoreManager(1));
        $this->assertFalse($builder->addWebsiteIdFilter(-1));

        $builder->create();
        $this->assertCount(0, $this->addedFilters);
    }

    public function testAddWebsiteIdFilterRejectsNonIntegerWebsiteId(): void
    {
        $builder = $this->createBuilder($this->createStoreManager(1));

        $this->expectException(\TypeError::class);
        $builder->addWebsiteIdFilter('1,2');
    }

    public function testAddWebsiteIdFilterCastsCurrentWebsiteIdToInteger(): void
    {
        $website = $this->getMockBuilder(WebsiteInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $website->method('getId')->willReturn('2');
        $storeManager = $this->createStoreManager(null);
        $storeManager->method('getWebsite')->willReturn($website);

        $builder = $this->createBuilder($storeManager);
        $this->assertTrue($builder->addWebsiteIdFilter());

        $builder->create();
        $this->assertCount(1, $this->addedFilters);
        $this->assertSame(2, $this->addedFilters[0]->getValue());
    }

    private function assertFilter(Filter $filter, string $field, string $conditionType, mixed $value): void
    {
        $this->assertSame($field, $filter->getField());
        $this->assertSame($conditionType, $filter->getConditionType());
        $this->assertEquals($value, $filter->getValue());
    }

    private function createStoreManager(?int $websiteId): StoreManagerInterface&MockObject
    {
        $storeManager = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        if ($websiteId !== null) {
            $website = $this->getMockBuilder(WebsiteInterface::class)
                ->disableOriginalConstructor()
                ->getMock();
            $website->method('getId')->willReturn($websiteId);
            $storeManager->method('getWebsite')->willReturn($website);
        }

        return $storeManager;
    }

    private function createBuilder(
        ?StoreManagerInterface $storeManager = null,
        ?SortOrderBuilder $sortOrderBuilder = null
    ): CommonSearchCriteriaBuilder {
        $this->addedFilters = [];

        $objectFactory = $this->getMockBuilder(ObjectFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $filterGroup = $this->getMockBuilder(FilterGroup::class)
            ->disableOriginalConstructor()
            ->getMock();

        $filterGroupBuilder = $this->getMockBuilder(FilterGroupBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $filterGroupBuilder->method('setFilters')->willReturnSelf();
        $filterGroupBuilder->method('create')->willReturn($filterGroup);
        $filterGroupBuilder->method('addFilter')->willReturnCallback(
            function (Filter $filter) use ($filterGroupBuilder) {
                $this->addedFilters[] = $filter;
                return $filterGroupBuilder;
            }
        );

        if ($sortOrderBuilder === null) {
            $sortOrderBuilder = $this->getMockBuilder(SortOrderBuilder::class)
                ->disableOriginalConstructor()
                ->getMock();
        }

        return new CommonSearchCriteriaBuilder(
            $objectFactory,
            $filterGroupBuilder,
            $sortOrderBuilder,
            $storeManager ?? $this->createStoreManager(null)
        );
    }
}
