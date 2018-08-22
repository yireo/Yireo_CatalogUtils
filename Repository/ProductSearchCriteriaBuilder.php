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
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Api\Filter;

/**
 * Class ProductSearchCriteriaBuilder
 * @package Yireo\CatalogUtils\Repository
 */
class ProductSearchCriteriaBuilder extends CommonSearchCriteriaBuilder
{
    /**
     * Add status filter
     *
     * @param int $status
     * @return bool
     * @throws LocalizedException
     */
    public function addStatusFilter(int $status)
    {
        $this->searchCriteriaBuilder->addFilter(
            new Filter([
                Filter::KEY_FIELD => ProductInterface::STATUS,
                Filter::KEY_CONDITION_TYPE => 'eq',
                Filter::KEY_VALUE => $status
            ])
        );

        return true;
    }
}
