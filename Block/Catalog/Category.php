<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\HtmlSitemap\Block\Catalog;

use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Framework\View\Element\Template;
use Magefan\HtmlSitemap\Model\Config;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use \Magento\Catalog\Model\CategoryFactory;

class Category extends Template
{
    const XML_PATH_TO_CATALOG_CATEGORY_BLOCK_TITLE = 'mfhs/categorylinks/title';
    const XML_PATH_TO_CATALOG_CATEGORY_DEPTH = 'mfhs/categorylinks/maxdepth';
    const XML_PATH_TO_CATALOG_CATEGORY_VIEW_MORE = 'mfhs/categorylinks/displaymore';
    const XML_PATH_TO_CATALOG_CATEGORY_LIMIT = 'mfhs/categorylinks/maxnumberlinks';

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
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var CategoryFactory
     */
    private $categoryFactory;

    /**
     * @var array
     */
    private $excludedCategoriesIds = [];

    /**
     * @param Template\Context $context
     * @param Config $config
     * @param CollectionFactory $collectionFactory
     * @param CategoryFactory $categoryFactory
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Config $config,
        CollectionFactory $collectionFactory,
        CategoryFactory $categoryFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->config = $config;
        $this->collectionFactory = $collectionFactory;
        $this->categoryFactory = $categoryFactory;
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
            && count($this->getAllGroupedChildes()) > $this->config->getConfig(self::XML_PATH_TO_CATALOG_CATEGORY_LIMIT)
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
        $k = 'grouped_childes';
        if (!$this->hasData($k)) {
            $allGroupedChildes = $this->getAllGroupedChildes();
            $limit = $this->config->getConfig(self::XML_PATH_TO_CATALOG_CATEGORY_LIMIT);
            $groupedChildes = array_slice($allGroupedChildes, 0, $limit);
            $this->setData($k, $groupedChildes);
        }
        return $this->getData($k);
    }

    /**
     * @param bool $toLoad
     * @return Collection
     */
    public function getAllGroupedChildes()
    {
        $k = 'all_grouped_childes';
        if (!$this->hasData($k)) {
            $categories = $this->getStoreCategories();
            if (null !== $categories) {
                $this->setData($k, $this->getCategoryTree($categories));
            } else {
                $this->setData($k, []);
            }
        }

        return $this->getData($k);
    }

    /**
     * @return Collection|\Magento\Framework\Data\Collection\AbstractDb|null
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getStoreCategories()
    {
        $parent = $this->_storeManager->getStore()->getRootCategoryId();

        /**
         * Check if parent node of the store still exists
         */
        $category = $this->categoryFactory->create();
        /* @var $category ModelCategory */
        if (!$category->checkId($parent)) {
            return null;
        }

        $categories = $this->collectionFactory->create()
            ->addAttributeToSelect('*')
            ->addIsActiveFilter()
            ->addFieldToFilter('path', ['like' => '%/' . $parent . '/%']);


        $categories->setOrder('position', 'ASC');

        $ignoredLinks = $this->config->getIgnoredLinks();
        if (!empty($ignoredLinks)) {
            $categories->addAttributeToFilter('url_key', ['nin' => $ignoredLinks]);
        }

        $maxDepth = $this->config->getConfig(self::XML_PATH_TO_CATALOG_CATEGORY_DEPTH) + 1;
        if ($maxDepth) {
            $categories->addAttributeToFilter('level', ['lteq' => $maxDepth]);
        }

        if ($excludedCategoriesIds = $this->getExcludedCategoriesIds()) {
            $categories->addAttributeToFilter('entity_id', ['nin' => $excludedCategoriesIds]);
        }

        return $categories;
    }

    /**
     * Retrieve tree ordered categories
     * @return array
     */
    public function getCategoryTree($categories)
    {
        $tree = [];
        if ($childs = $this->getGroupedChilds($categories)) {
            $this->_toTree($categories, 0, $childs, $tree);
        }
        return $tree;
    }

    /**
     * Retrieve gruped category childs
     * @return array
     */
    public function getGroupedChilds($categories)
    {
        $childs = [];
        if (count($categories)) {
            foreach ($categories as $item) {
                $childs[$item->getLevel() > 2  ? $item->getParentId() : 0][] = $item;
            }
        }
        return $childs;
    }

    /**
     * Auxiliary function to build tree ordered array
     * @return array
     */
    protected function _toTree($categories, $itemId, $childs, &$tree)
    {
        if ($itemId) {
            $tree[] = $categories->getItemById($itemId);
        }

        if (isset($childs[$itemId])) {
            foreach ($childs[$itemId] as $i) {
                $this->_toTree($categories, $i->getId(), $childs, $tree);
            }
        }
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
