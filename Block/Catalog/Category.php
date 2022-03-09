<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\HtmlSitemap\Block\Catalog;

use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Framework\View\Element\Template;
use Magefan\HtmlSitemap\Model\Config;
use Magento\Catalog\Helper\Category as CategoryHelper;

class Category extends Template
{
    const XML_PATH_TO_CATALOG_CATEGORY_BLOCK_TITLE = 'mfhs/categorylinks/title';
    const XML_PATH_TO_CATALOG_CATEGORY_DEPTH = 'mfhs/categorylinks/maxdepth';
    const XML_PATH_TO_CATALOG_CATEGORY_VIEW_MORE = 'mfhs/categorylinks/displaymore';
    const XML_PATH_TO_CATALOG_CATEGORY_LIMIT = 'mfhs/categorylinks/maxnumberlinks';

    /**
     * @var CategoryHelper
     */
    private $categoryHelper;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var array
     */
    private $ignoredLinks;

    /**
     * Category constructor.
     * @param Template\Context $context
     * @param CategoryHelper $categoryHelper
     * @param Config $config
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        CategoryHelper $categoryHelper,
        Config $config,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->categoryHelper = $categoryHelper;
        $this->config = $config;
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
     */
    public function showViewMore()
    {
        if ($this->config->getConfig(self::XML_PATH_TO_CATALOG_CATEGORY_VIEW_MORE)
            && count($this->getAllGroupedChildes(true)) > $this->config->getConfig(self::XML_PATH_TO_CATALOG_CATEGORY_LIMIT)
        ) {
            return true;
        }
        return false;
    }

    /**
     * @return Collection
     */
    public function getGroupedChildes()
    {
        $pageSize = $this->config->getConfig(self::XML_PATH_TO_CATALOG_CATEGORY_LIMIT);
        $maxDepth = $this->config->getConfig(self::XML_PATH_TO_CATALOG_CATEGORY_DEPTH);
        $k = 'grouped_childes';

        if (!$this->hasData($k)) {
            $categories = $this->categoryHelper->getStoreCategories(false, true, false);
            if (!empty($this->ignoredLinks)) {
                $categories
                    ->addAttributeToFilter('url_key', ['nin' => $this->config->getIgnoredLinks()]);

            }

            if ($maxDepth) {
                $categories->addAttributeToFilter('level', ['lt' => $maxDepth]);
            }

            $categories->setPageSize($pageSize);

            $this->setData($k, $categories);
        }

        return $this->getData($k);
    }

    /**
     * @param bool $toLoad
     * @return Collection
     */
    public function getAllGroupedChildes($toLoad = false)
    {
        $maxDepth = $this->config->getConfig(self::XML_PATH_TO_CATALOG_CATEGORY_DEPTH);
        $k = 'all_grouped_childes';

        if (!$this->hasData($k)) {
            $categories = $this->categoryHelper->getStoreCategories(false, true, $toLoad);
            if (!empty($this->ignoredLinks)) {
                $categories->addAttributeToFilter('url_key', ['nin' => $this->config->getIgnoredLinks()]);
            }

            if ($maxDepth) {
                $categories->addAttributeToFilter('level', ['lt' => $maxDepth]);
            }

            $this->setData($k, $categories);
        }

        return $this->getData($k);
    }

    /**
     * @return int
     */
    public function getSortOrder()
    {
        return $this->config->getSortOrder('categorylinks');
    }
}
