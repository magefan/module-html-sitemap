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
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;

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
     * @var array
     */
    private $arrayGetCatById = [];

    /**
     * @var array
     */
    private $htmlCatTree = [];

    /**
     * @var Config
     */
    private $config;

    /**
     * @var array
     */
    private $ignoredLinks;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var array
     */
    private $excludedCategoriesIds = [];

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
        CollectionFactory $collectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->categoryHelper = $categoryHelper;
        $this->config = $config;
        $this->ignoredLinks = $config->getIgnoredLinks();
        $this->collectionFactory = $collectionFactory;
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
                $categories->addAttributeToFilter('url_key', ['nin' => $this->config->getIgnoredLinks()]);
            }

            if ($maxDepth) {
                $categories->addAttributeToFilter('level', ['lt' => $maxDepth]);
            }

            if ($this->getExcludedCategoriesIds()) {
                $categories->addAttributeToFilter('entity_id', ['nin' => $this->getExcludedCategoriesIds()]);
            }

            $categories->setPageSize($pageSize);

            $this->setData($k, $this->getCategoryTree($categories));
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

            if ($this->getExcludedCategoriesIds()) {
                $categories->addAttributeToFilter('entity_id', ['nin' => $this->getExcludedCategoriesIds()]);
            }

            $this->setData($k, $this->getCategoryTree($categories));
        }

        return $this->getData($k);
    }

    /**
     * Return sorted array - in possition that required to build category tree
     * @param $categories
     * @return array
     */
    private function getCategoryTree($categories)
    {
        if (!count($categories)) {
            return $categories;
        }

        $categories->setOrder('level','ASC');

        foreach ($categories as $category) {
            $this->arrayCatByLevels[$category->getLevel()][$category->getParentId()][$category->getId()] = $category->getId();
            $this->arrayGetCatById[$category->getId()] = $category;
        }

        $level = key(reset($this->arrayCatByLevels));

        $baseCategories = reset($this->arrayCatByLevels);
        $baseCategories = reset($baseCategories);

        foreach ($baseCategories as $baseCategoryId) {
            $findChild = function($lev, $id ) use ( &$findChild ) {
                if (isset($this->arrayCatByLevels[$lev][$id])) {
                    $this->htmlCatTree[] = $this->arrayGetCatById[$id];
                    foreach ($this->arrayCatByLevels[$lev][$id] as $chiedlId) {
                        $findChild($lev+1, $chiedlId);
                    }
                } else {
                    $this->htmlCatTree[] = $this->arrayGetCatById[$id];
                }
            };

            $findChild($level + 1, $baseCategoryId);
        }

        return $this->htmlCatTree;
    }

    /**
     * @return int
     */
    public function getSortOrder()
    {
        return $this->config->getSortOrder('categorylinks');
    }

    /**
     * @return array
     */
    private function getExcludedCategoriesIds()
    {
        if (!$this->excludedCategoriesIds) {
            $this->excludedCategoriesIds = $this->collectionFactory->create()
                ->addAttributeToFilter('mf_exclude_html_sitemap', 1)->getAllIds();
        }

        return $this->excludedCategoriesIds;
    }

    /**
     * @return $this|Category
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
