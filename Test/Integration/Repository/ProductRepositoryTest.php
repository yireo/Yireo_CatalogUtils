<?php

declare(strict_types=1);

namespace Yireo\CatalogUtils\Test\Integration\Repository;

use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\Api\Filter;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Registry;
use PHPUnit\Framework\TestCase;
use Yireo\CatalogUtils\Repository\ProductRepository;
use Yireo\CatalogUtils\Repository\ProductSearchCriteriaBuilder;

class ProductRepositoryTest extends TestCase
{
    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws StateException
     * @throws NotFoundException
     */
    public function testFetchingSingleProductAndSavingIt()
    {
        $repository = $this->getProductRepository();
        $product = $repository->getFirstProductInListing($repository->getSearchCriteriaBuilder());
        $productId = $product->getId();
        $productSku = $product->getSku();
        $this->assertTrue($productId > 0);
        $this->assertNotEmpty($productSku);

        $product->setPrice(42);
        $repository->save($product);
        $product = $repository->getById($productId);
        $this->assertEquals($productSku, $product->getSku());
    }

    public function testGetSearchCriteriaBuilderReturnsNewInstances(): void
    {
        $repository = $this->getProductRepository();
        $firstBuilder = $repository->getSearchCriteriaBuilder();
        $secondBuilder = $repository->getSearchCriteriaBuilder();

        $this->assertInstanceOf(ProductSearchCriteriaBuilder::class, $firstBuilder);
        $this->assertNotSame($firstBuilder, $secondBuilder);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testGetBySku(): void
    {
        $product = $this->getProductRepository()->getBySku('simple');
        $this->assertSame('simple', $product->getSku());
        $this->assertTrue($product->getId() > 0);
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testCreateSaveAndDeleteNewProduct(): void
    {
        $repository = $this->getProductRepository();
        $product = $repository->createNewProduct(['data' => [
            'sku' => 'catalogutils-new',
            'name' => 'CatalogUtils New Product',
            'type_id' => Type::TYPE_SIMPLE,
            'attribute_set_id' => 4,
            'price' => 10,
            'visibility' => Visibility::VISIBILITY_BOTH,
            'status' => Status::STATUS_ENABLED,
            'website_ids' => [1],
        ]]);
        $this->assertSame('catalogutils-new', $product->getSku());
        $this->assertEmpty($product->getId());

        $repository->save($product);

        $savedProduct = $repository->getBySku('catalogutils-new');
        $this->assertTrue($savedProduct->getId() > 0);
        $this->assertSame('CatalogUtils New Product', $savedProduct->getName());

        $registry = ObjectManager::getInstance()->get(Registry::class);
        $registry->unregister('isSecureArea');
        $registry->register('isSecureArea', true);
        try {
            $this->assertTrue($repository->deleteById('catalogutils-new'));
        } finally {
            $registry->unregister('isSecureArea');
        }

        $this->expectException(NoSuchEntityException::class);
        $repository->get('catalogutils-new', false, null, true);
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testGetFirstProductInListingThrowsExceptionWithoutResults(): void
    {
        $repository = $this->getProductRepository();
        $searchCriteriaBuilder = $repository->getSearchCriteriaBuilder();
        $searchCriteriaBuilder->addFilter(new Filter([
            Filter::KEY_FIELD => 'sku',
            Filter::KEY_CONDITION_TYPE => 'eq',
            Filter::KEY_VALUE => 'does-not-exist',
        ]));

        $this->expectException(NotFoundException::class);
        $repository->getFirstProductInListing($searchCriteriaBuilder);
    }

    /**
     * @return ProductRepository
     */
    private function getProductRepository(): ProductRepository
    {
        return ObjectManager::getInstance()->get(ProductRepository::class);
    }
}
