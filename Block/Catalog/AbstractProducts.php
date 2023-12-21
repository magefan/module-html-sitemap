<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
declare(strict_types=1);

namespace Magefan\HtmlSitemap\Block\Catalog;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magefan\HtmlSitemap\Model\Config;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Framework\View\Element\Template;
use Magento\Framework\DataObject;

abstract class AbstractProducts extends Template
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
    protected $status;

    /**
     * @var Visibility
     */
    protected $visibility;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var
     */
    protected $excludedProductsIds;

    /**
     * Product constructor.
     * @param Template\Context $context
     * @param CollectionFactory $productCollectionFactory
     * @param Status $status
     * @param Visibility $visibility
     * @param Config $config
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        CollectionFactory $productCollectionFactory,
        Status $status,
        Visibility $visibility,
        Config $config,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->productCollectionFactory = $productCollectionFactory;
        $this->status = $status;
        $this->visibility = $visibility;
        $this->config = $config;
    }

    /**
     * @return string
     */
    public function getBlockTitle()
    {
        return (string)$this->config->getConfig(self::XML_PATH_TO_CATALOG_CATEGORY_BLOCK_TITLE);
    }

    /**
     * @return Collection
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCollection()
    {
        $k = 'collection';
        if (null === $this->getData($k)) {
            $items = $this->productCollectionFactory->create()
                ->addAttributeToSelect('name')
                ->addAttributeToFilter('status', ['in' => $this->status->getVisibleStatusIds()]);
            if (!empty($this->ignoredLinks)) {
                $items->addAttributeToFilter('url_key', ['nin' => $this->config->getIgnoredLinks()]);
            }

            if ($this->getExcludedProductsIds()) {
                $items->addAttributeToFilter('entity_id', ['nin' => $this->getExcludedProductsIds()]);
            }

            $items
                ->setVisibility($this->visibility->getVisibleInSiteIds())
                ->addStoreFilter($this->_storeManager->getStore()->getId());

            if ($pageSize = $this->getPageSize()) {
                $items->setPageSize($pageSize);
            }

            $this->setData($k, $items);
        }
        return $this->getData($k);
    }

    /**
     * @return array|mixed|null
     */
    public function getItems()
    {
        $k = 'items';
        if (null === $this->getData($k)) {
            $items = [];
            foreach ($this->getCollection() as $collectionItem) {
                $item = new DataObject([
                    'url' => $collectionItem->getProductUrl(),
                    'name' => $collectionItem->getName(),
                    'object' => $collectionItem
                ]);
                $items[] = $item;
            }
            $this->setData($k, $items);
        }

        return $this->getData($k);
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
     * @return int
     */
    protected function getPageSize(): int
    {
        return 0;
    }
}
