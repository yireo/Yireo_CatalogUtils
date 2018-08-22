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
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Yireo\CatalogUtils\Repository\ProductSearchCriteriaBuilderFactory;

/**
 * Class ProductRepository
 * @package Yireo\CatalogUtils\Repository
 */
class ProductRepository
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
     * ProductRepository constructor.
     * @param ProductRepositoryInterface $productRepository
     * @param ProductSearchCriteriaBuilderFactory $productSearchCriteriaBuilderFactory
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        ProductSearchCriteriaBuilderFactory $productSearchCriteriaBuilderFactory
    ) {
        $this->productRepository = $productRepository;
        $this->productSearchCriteriaBuilderFactory = $productSearchCriteriaBuilderFactory;
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
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return $this->productRepository->$name($arguments);
    }
}
