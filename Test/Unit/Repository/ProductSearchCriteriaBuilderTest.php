<?php

declare(strict_types=1);

namespace Yireo\CatalogUtils\Test\Unit\Repository;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\ObjectFactory;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\Search\SearchCriteria;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;
use Yireo\CatalogUtils\Repository\ProductSearchCriteria;
use Yireo\CatalogUtils\Repository\ProductSearchCriteriaBuilder;

class ProductSearchCriteriaBuilderTest extends TestCase
{
    /**
     * @var Filter[]
     */
    private array $addedFilters = [];

    private ?string $createdType = null;

    /**
     * @var mixed[]
     */
    private array $createdArguments = [];

    public function testAddStatusFilter(): void
    {
        $builder = $this->createBuilder();
        $this->assertTrue($builder->addStatusFilter(1));

        $builder->create();
        $this->assertCount(1, $this->addedFilters);
        $this->assertSame(ProductInterface::STATUS, $this->addedFilters[0]->getField());
        $this->assertSame('eq', $this->addedFilters[0]->getConditionType());
        $this->assertSame(1, $this->addedFilters[0]->getValue());
    }

    public function testCombinedFiltersEndUpInSeparateFilterGroups(): void
    {
        $builder = $this->createBuilder();
        $builder->addStatusFilter(1);
        $builder->addWebsiteIdFilter(2);

        $builder->create();
        $this->assertSame(ProductSearchCriteria::class, $this->createdType);

        $data = $this->createdArguments['data'];
        $this->assertCount(2, $data[SearchCriteria::FILTER_GROUPS]);
        $this->assertSame(ProductInterface::STATUS, $this->addedFilters[0]->getField());
        $this->assertSame('website_id', $this->addedFilters[1]->getField());
    }

    private function createBuilder(): ProductSearchCriteriaBuilder
    {
        $objectFactory = $this->getMockBuilder(ObjectFactory::class)
            ->disableOriginalConstructor()
            ->getMock();
        $objectFactory->method('create')->willReturnCallback(
            function (string $type, array $arguments) {
                $this->createdType = $type;
                $this->createdArguments = $arguments;
                return $this->getMockBuilder(ProductSearchCriteria::class)
                    ->disableOriginalConstructor()
                    ->getMock();
            }
        );

        $filterGroupBuilder = $this->getMockBuilder(FilterGroupBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $filterGroupBuilder->method('setFilters')->willReturnSelf();
        $filterGroupBuilder->method('create')->willReturnCallback(
            fn () => $this->getMockBuilder(FilterGroup::class)->disableOriginalConstructor()->getMock()
        );
        $filterGroupBuilder->method('addFilter')->willReturnCallback(
            function (Filter $filter) use ($filterGroupBuilder) {
                $this->addedFilters[] = $filter;
                return $filterGroupBuilder;
            }
        );

        $sortOrderBuilder = $this->getMockBuilder(SortOrderBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $storeManager = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        return new ProductSearchCriteriaBuilder(
            $objectFactory,
            $filterGroupBuilder,
            $sortOrderBuilder,
            $storeManager
        );
    }
}
