<?php
/**
 * CatalogUtils module for Magento
 *
 * @package     Yireo_CatalogUtils
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2018 Yireo (https://www.yireo.com/)
 * @license     OSL
 */

declare(strict_types=1);

namespace Yireo\CatalogUtils\Repository;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\Data\ProductSearchResultsInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Exception\StateException;
use Yireo\CatalogUtils\Repository\ProductSearchCriteriaBuilderFactory;

/**
 * Class ProductRepository
 * @package Yireo\CatalogUtils\Repository
 */
class ProductRepository implements ProductRepositoryInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var ProductSearchCriteriaBuilderFactory
     */
    private $productSearchCriteriaBuilderFactory;

    /**
     * @var ProductInterfaceFactory
     */
    private $productFactory;

    /**
     * ProductRepository constructor.
     * @param ProductRepositoryInterface $productRepository
     * @param ProductSearchCriteriaBuilderFactory $productSearchCriteriaBuilderFactory
     * @param ProductInterfaceFactory $productFactory
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        ProductSearchCriteriaBuilderFactory $productSearchCriteriaBuilderFactory,
        ProductInterfaceFactory $productFactory
    ) {
        $this->productRepository = $productRepository;
        $this->productSearchCriteriaBuilderFactory = $productSearchCriteriaBuilderFactory;
        $this->productFactory = $productFactory;
    }

    /**
     * @param string $sku
     * @return ProductInterface
     * @throws NoSuchEntityException
     */
    public function getBySku(string $sku): ProductInterface
    {
        return $this->productRepository->get($sku);
    }

    /**
     * @return ProductSearchCriteriaBuilder
     */
    public function getSearchCriteriaBuilder(): ProductSearchCriteriaBuilder
    {
        return $this->productSearchCriteriaBuilderFactory->create();
    }

    /**
     * @param array $data
     * @return ProductInterface
     */
    public function createNewProduct(array $data = []): ProductInterface
    {
        return $this->productFactory->create($data);
    }

    /**
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @return ProductInterface
     */
    public function getFirstProductInListing(SearchCriteriaBuilder $searchCriteriaBuilder): ProductInterface
    {
        $searchCriteriaBuilder->setPageSize(1);
        $searchCriteriaBuilder->setCurrentPage(0);
        $searchResults = $this->getList($searchCriteriaBuilder->create());
        $products = $searchResults->getItems();

        if (empty($products)) {
            throw new NotFoundException(__('Product not found'));
        }

        return array_shift($products);
    }

    /**
     * @param ProductInterface $product
     * @param false $saveOptions
     * @return ProductInterface
     * @throws StateException
     * @throws CouldNotSaveException
     * @throws InputException
     */
    public function save(ProductInterface $product, $saveOptions = false)
    {
        return $this->productRepository->save($product);
    }

    /**
     * @param string $sku
     * @param false $editMode
     * @param null $storeId
     * @param false $forceReload
     * @return ProductInterface
     * @throws NoSuchEntityException
     */
    public function get($sku, $editMode = false, $storeId = null, $forceReload = false)
    {
        return $this->productRepository->get($sku, $editMode, $storeId, $forceReload);
    }

    /**
     * @param int $productId
     * @param false $editMode
     * @param null $storeId
     * @param false $forceReload
     * @return ProductInterface
     * @throws NoSuchEntityException
     */
    public function getById($productId, $editMode = false, $storeId = null, $forceReload = false)
    {
        return $this->productRepository->getById($productId, $editMode, $storeId, $forceReload);
    }

    /**
     * @param ProductInterface $product
     * @return bool
     * @throws StateException
     */
    public function delete(ProductInterface $product)
    {
        return $this->productRepository->delete($product);
    }

    /**
     * @param string $sku
     * @return bool
     * @throws NoSuchEntityException
     * @throws StateException
     */
    public function deleteById($sku)
    {
        return $this->productRepository->deleteById($sku);
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return ProductSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        return $this->productRepository->getList($searchCriteria);
    }
}
