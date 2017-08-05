<?php
/**
 * CatalogUtils module for Magento
 *
 * @package     Yireo_CatalogUtils
 * @author      Yireo (https://www.yireo.com/)
 * @copyright   Copyright 2017 Yireo (https://www.yireo.com/)
 * @license     OSL
 */

namespace Yireo\CatalogUtils\Repository;

use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Api\Filter;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;

/**
 * Class CommonSearchCriteriaBuilder
 * @package Yireo\CatalogUtils\Repository
 */
class CommonSearchCriteriaBuilder
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * CommonSearchCriteriaBuilder constructor.
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        StoreManagerInterface $storeManager
    )
    {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->storeManager = $storeManager;
    }

    /**
     * Add sorting by primary key
     */
    public function addSortByPrimaryKey()
    {
        return $this->searchCriteriaBuilder->addSortOrder('entity_id', AbstractCollection::SORT_ORDER_DESC);
    }

    /**
     * Add website_id folder
     *
     * @param int $websiteId
     * @return bool
     */
    public function addWebsiteIdFilter($websiteId = 0)
    {
        if (!$websiteId) {
            $websiteId = $this->storeManager->getWebsite()->getId();
        }

        if (!$websiteId > 0) {
            return false;
        }

        $this->searchCriteriaBuilder->addFilter(
            new Filter([
                Filter::KEY_FIELD => 'website_id',
                Filter::KEY_CONDITION_TYPE => 'eq',
                Filter::KEY_VALUE => $websiteId
            ]));

        return true;
    }

    /**
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        return $this->searchCriteriaBuilder->$name($arguments);
    }
}