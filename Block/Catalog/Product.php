<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\HtmlSitemap\Block\Catalog;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magefan\HtmlSitemap\Model\Config;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\StoreManagerInterface;

class Product extends Template
{
    const XML_PATH_TO_CATALOG_CATEGORY_BLOCK_TITLE = 'mfhs/productlinks/title';
    const XML_PATH_TO_CATALOG_PRODUCTS_LIMIT = 'mfhs/productlinks/maxnumberlinks';
    const XML_PATH_TO_CATALOG_PRODUCT_VIEW_MORE = 'mfhs/categorylinks/displaymore';

    /**
     * @var CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var Status
     */
    private $status;

    /**
     * @var Visibility
     */
    private $visibility;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var array
     */
    private $ignoredLinks;

    /**
     * @var
     */
    private $excludedProductsIds;

    /**
     * Product constructor.
     * @param Template\Context $context
     * @param CollectionFactory $productCollectionFactory
     * @param Status $status
     * @param Visibility $visibility
     * @param Config $config
     * @param StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        CollectionFactory $productCollectionFactory,
        Status $status,
        Visibility $visibility,
        Config $config,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->productCollectionFactory = $productCollectionFactory;
        $this->status = $status;
        $this->visibility = $visibility;
        $this->config = $config;
        $this->storeManager = $storeManager;
        $this->ignoredLinks = $config->getIgnoredLinks();
    }

    /**
     * @return string
     */
    public function getBlockTitle()
    {
        return (string)$this->config->getConfig(self::XML_PATH_TO_CATALOG_CATEGORY_BLOCK_TITLE);
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function showViewMore()
    {
        if ($this->config->getConfig(self::XML_PATH_TO_CATALOG_PRODUCT_VIEW_MORE)
            && count($this->getAllProductCollection()) > $this->config->getConfig(self::XML_PATH_TO_CATALOG_PRODUCTS_LIMIT)
        ) {
            return true;
        }
        return false;
    }

    /**
     * @return Collection
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProductCollection()
    {
        $pageSize = $this->config->getConfig(self::XML_PATH_TO_CATALOG_PRODUCTS_LIMIT);
        $products = $this->productCollectionFactory->create()
            ->addAttributeToSelect('name')
            ->addAttributeToFilter('status', ['in' => $this->status->getVisibleStatusIds()]);
        if (!empty($this->ignoredLinks)) {
            $products->addAttributeToFilter('url_key', ['nin' => $this->config->getIgnoredLinks()]);
        }

        if ($this->getExcludedProductsIds()) {
            $products->addAttributeToFilter('entity_id', ['nin' => $this->getExcludedProductsIds()]);
        }

        $products
            ->setVisibility($this->visibility->getVisibleInSiteIds())
            ->addStoreFilter($this->storeManager->getStore()->getId())
            ->setPageSize($pageSize);

        return $products;
    }

    /**
     * @return Collection
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAllProductCollection()
    {
        $products = $this->productCollectionFactory->create()
            ->addAttributeToSelect('name')
            ->addAttributeToFilter('status', ['in' => $this->status->getVisibleStatusIds()]);
        if (!empty($this->ignoredLinks)) {
            $products->addAttributeToFilter('url_key', ['nin' => $this->config->getIgnoredLinks()]);
        }

        if ($this->getExcludedProductsIds()) {
            $products->addAttributeToFilter('entity_id', ['nin' => $this->getExcludedProductsIds()]);
        }

        $products
            ->setVisibility($this->visibility->getVisibleInSiteIds())
            ->addStoreFilter($this->storeManager->getStore()->getId());

        return $products;
    }

    /**
     * @return int
     */
    public function getSortOrder()
    {
        return $this->config->getSortOrder('productlinks');
    }

    /**
     * @return array
     */
    private function getExcludedProductsIds()
    {
        if (!$this->excludedProductsIds) {
            $this->excludedProductsIds = $this->productCollectionFactory->create()
                ->addAttributeToFilter('mf_exclude_html_sitemap', 1)->getAllIds();
        }

        return $this->excludedProductsIds;
    }


    /**
     * @return $this|Product
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $title = $this->getBlockTitle();

        if ($title) {
            $this->pageConfig->getTitle()->set( __('Sitemap') . ' - ' .  $this->getBlockTitle());
        }

        return $this;
    }
}
