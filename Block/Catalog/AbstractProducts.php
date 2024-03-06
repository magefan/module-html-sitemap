<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

declare(strict_types=1);

namespace Magefan\HtmlSitemap\Block\Catalog;

use Magefan\HtmlSitemap\Block\AbstractBlock;
use Magento\Framework\View\Element\Template;
use Magefan\HtmlSitemap\Model\Config;
use Magento\Framework\DataObject;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;

abstract class AbstractProducts extends AbstractBlock
{

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
     * @var array
     */
    protected $excludedProductsIds;

    /**
     * @var string
     */
    protected $type = 'productlinks';

    /**
     * Product constructor.
     * @param Template\Context $context
     * @param Config $config
     * @param CollectionFactory $productCollectionFactory
     * @param Status $status
     * @param Visibility $visibility
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Config $config,
        CollectionFactory $productCollectionFactory,
        Status $status,
        Visibility $visibility,
        array $data = []
    ) {
        parent::__construct($context, $config, $data);
        $this->productCollectionFactory = $productCollectionFactory;
        $this->status = $status;
        $this->visibility = $visibility;
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
     * @return string
     */
    public function getCurrentTypeHtmlSitemapUrl()
    {
        return $this->getUrl('htmlsitemap/catalog/products');
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
}
