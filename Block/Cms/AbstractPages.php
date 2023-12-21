<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
declare(strict_types=1);

namespace Magefan\HtmlSitemap\Block\Cms;

use Magento\Framework\DataObject;
use Magento\Cms\Model\ResourceModel\Page\Collection;
use Magento\Framework\View\Element\Template;
use Magento\Cms\Model\ResourceModel\Page\CollectionFactory as PageCollectionFactory;
use Magefan\HtmlSitemap\Model\Config;

abstract class AbstractPages extends Template
{
    const XML_PATH_TO_CMS_PAGE_BLOCK_TITLE = 'mfhs/cmspagelinks/title';
    const XML_PATH_TO_CMS_PAGE_LIMIT = 'mfhs/cmspagelinks/maxnumberlinks';
    const XML_PATH_TO_CMS_PAGE_VIEW_MORE = 'mfhs/cmspagelinks/displaymore';
    const XML_PATH_TO_PAGE_TITLE = 'mfhs/seo/title';
    const XML_PATH_TO_PAGE_META_TITLE = 'mfhs/seo/metatitle';
    const XML_PATH_TO_PAGE_META_DESCRIPTION = 'mfhs/seo/metadescription';
    const XML_PATH_TO_PAGE_META_KEYWORDS = 'mfhs/seo/metakeywords';

    /**
     * @var PageCollectionFactory
     */
    protected $pageCollectionFactory;

    /**
     * @var Config
     */
    protected $config;

    /**
     * Page constructor.
     * @param Template\Context $context
     * @param PageCollectionFactory $pageCollectionFactory
     * @param Config $config
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        PageCollectionFactory $pageCollectionFactory,
        Config $config,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->pageCollectionFactory = $pageCollectionFactory;
        $this->config = $config;
    }

    /**
     * @return string
     */
    public function getBlockTitle()
    {
        return (string)$this->config->getConfig(self::XML_PATH_TO_CMS_PAGE_BLOCK_TITLE);
    }

    /**
     * @return Collection
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCmsPagesList()
    {
        $pages = $this->pageCollectionFactory->create()
            ->addStoreFilter($this->_storeManager->getStore()->getId());
        if (!empty($this->ignoredLinks)) {
            $pages->addFieldToFilter('identifier', ['nin' => $this->config->getIgnoredLinks()]);
        }

        $pages->addFieldToFilter('mf_exclude_html_sitemap', 0)
            ->addFieldToFilter('is_active', 1);

        return $pages;
    }

    /**
     * @return Collection
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCollection()
    {
        $k = 'collection';
        if (null === $this->getData($k)) {
            $items = $this->pageCollectionFactory->create()
                ->addFieldToFilter('is_active', 1)
                ->addStoreFilter($this->_storeManager->getStore()->getId());
            if (!empty($this->ignoredLinks)) {
                $items->addFieldToFilter('identifier', ['nin' => $this->config->getIgnoredLinks()]);
            }

            $items->addFieldToFilter('mf_exclude_html_sitemap', 0);
            if ($pageSize = $this->getPageSize()) {
                $items->setPageSize($pageSize);
            }
            $this->setData($k, $items);
        }
        return $this->getData($k);
    }

    public function getItems()
    {
        $k = 'items';
        if (null === $this->getData($k)) {
            $items = [];
            foreach ($this->getCollection() as $collectionItem) {
                $item = new DataObject([
                    'url' => $this->getBaseUrl() . $collectionItem->getIdentifier(),
                    'name' => $collectionItem->getTitle(),
                    'object' => $collectionItem
                ]);
                $items[] = $item;
            }
            $this->setData($k, $items);
        }

        return $this->getData($k);
    }

    /**
     * @return int
     */
    protected function getPageSize(): int
    {
        return 0;
    }

}
