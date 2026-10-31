<?php

declare(strict_types=1);

namespace Yireo\CatalogUtils\Test\Integration\Repository;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Framework\Api\Filter;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Api\WebsiteRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Yireo\CatalogUtils\Repository\ProductRepository;
use Yireo\CatalogUtils\Repository\ProductSearchCriteria;
use Yireo\CatalogUtils\Repository\ProductSearchCriteriaBuilder;

class ProductSearchCriteriaBuilderTest extends TestCase
{
    public function testCreateReturnsProductSearchCriteria(): void
    {
        $searchCriteria = $this->getProductRepository()->getSearchCriteriaBuilder()->create();
        $this->assertInstanceOf(ProductSearchCriteria::class, $searchCriteria);
    }

    /**
     * @magentoDbIsolation disabled
     * @magentoDataFixture Magento/Catalog/_files/products_with_websites_and_stores.php
     */
    public function testAddWebsiteIdFilter(): void
    {
        $websiteRepository = ObjectManager::getInstance()->get(WebsiteRepositoryInterface::class);
        $secondWebsiteId = (int)$websiteRepository->get('test')->getId();

        $builder = $this->getProductRepository()->getSearchCriteriaBuilder();
        $this->addSkuFilter($builder, ['simple-1', 'simple-2']);
        $this->assertTrue($builder->addWebsiteIdFilter($secondWebsiteId));

        $this->assertSame(['simple-2'], $this->getSkus($builder));
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/multiple_products.php
     */
    public function testAddStatusFilterWithEnabledStatus(): void
    {
        $builder = $this->getProductRepository()->getSearchCriteriaBuilder();
        $this->addSkuFilter($builder, ['simple1', 'simple2', 'simple3']);
        $this->assertTrue($builder->addStatusFilter(Status::STATUS_ENABLED));

        $this->assertSame(['simple1', 'simple2'], $this->getSkus($builder));
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/multiple_products.php
     */
    public function testAddStatusFilterWithDisabledStatus(): void
    {
        $builder = $this->getProductRepository()->getSearchCriteriaBuilder();
        $this->addSkuFilter($builder, ['simple1', 'simple2', 'simple3']);
        $this->assertTrue($builder->addStatusFilter(Status::STATUS_DISABLED));

        $this->assertSame(['simple3'], $this->getSkus($builder));
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/multiple_products.php
     */
    public function testCombinedStatusAndWebsiteFilters(): void
    {
        $builder = $this->getProductRepository()->getSearchCriteriaBuilder();
        $this->addSkuFilter($builder, ['simple1', 'simple2', 'simple3']);
        $builder->addStatusFilter(Status::STATUS_ENABLED);
        $builder->addWebsiteIdFilter(1);

        $this->assertSame(['simple1', 'simple2'], $this->getSkus($builder));
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/multiple_products.php
     */
    public function testAddSortByPrimaryKey(): void
    {
        $repository = $this->getProductRepository();
        $builder = $repository->getSearchCriteriaBuilder();
        $this->addSkuFilter($builder, ['simple1', 'simple2', 'simple3']);
        $builder->addSortByPrimaryKey();

        $products = $repository->getList($builder->create())->getItems();
        $productIds = array_map(fn (ProductInterface $product) => (int)$product->getId(), array_values($products));
        $sortedProductIds = $productIds;
        rsort($sortedProductIds);
        $this->assertCount(3, $productIds);
        $this->assertSame($sortedProductIds, $productIds);

        $builder = $repository->getSearchCriteriaBuilder();
        $this->addSkuFilter($builder, ['simple1', 'simple2', 'simple3']);
        $builder->addSortByPrimaryKey();
        $firstProduct = $repository->getFirstProductInListing($builder);
        $this->assertSame($sortedProductIds[0], (int)$firstProduct->getId());
    }

    /**
     * @magentoAppArea frontend
     */
    public function testAddWebsiteIdFilterFallsBackToCurrentWebsite(): void
    {
        $builder = $this->getProductRepository()->getSearchCriteriaBuilder();
        $this->assertTrue($builder->addWebsiteIdFilter());

        $filters = [];
        foreach ($builder->create()->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                $filters[$filter->getField()] = $filter->getValue();
            }
        }

        $this->assertArrayHasKey('website_id', $filters);
        $this->assertEquals(1, $filters['website_id']);
    }

    /**
     * @param string[] $skus
     */
    private function addSkuFilter(ProductSearchCriteriaBuilder $builder, array $skus): void
    {
        $builder->addFilter(new Filter([
            Filter::KEY_FIELD => 'sku',
            Filter::KEY_CONDITION_TYPE => 'in',
            Filter::KEY_VALUE => $skus,
        ]));
    }

    /**
     * @return string[]
     */
    private function getSkus(ProductSearchCriteriaBuilder $builder): array
    {
        $products = $this->getProductRepository()->getList($builder->create())->getItems();
        $skus = array_map(fn (ProductInterface $product) => (string)$product->getSku(), $products);
        sort($skus);

        return array_values($skus);
    }

    private function getProductRepository(): ProductRepository
    {
        return ObjectManager::getInstance()->get(ProductRepository::class);
    }
}
