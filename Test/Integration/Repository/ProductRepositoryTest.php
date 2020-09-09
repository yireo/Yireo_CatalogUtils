<?php

declare(strict_types=1);

namespace Yireo\CatalogUtils\Test\Integration\Repository;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Exception\StateException;
use PHPUnit\Framework\TestCase;
use Yireo\CatalogUtils\Repository\ProductRepository;

class ProductRepositoryTest extends TestCase
{
    /**
     * @magentoDataFixture Magento/Catalog/_files/attribute_set_with_product.php
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

    /**
     * @return ProductRepository
     */
    private function getProductRepository(): ProductRepository
    {
        return ObjectManager::getInstance()->get(ProductRepository::class);
    }
}
