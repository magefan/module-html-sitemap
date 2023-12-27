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
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Catalog\Model\CategoryFactory;

abstract class AbstractCategories extends AbstractBlock
{

    /**
     * @var array
     */
    protected $htmlCatTree = [];

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var array
     */
    protected $excludedCategoriesIds = [];

    /**
     * @var string
     */
    protected $type = 'categorylinks';

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
        parent::__construct($context, $config, $data);
        $this->collectionFactory = $collectionFactory;
        $this->categoryFactory = $categoryFactory;
    }

    /**
     * @return Collection|\Magento\Framework\Data\Collection\AbstractDb|null
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCollection()
    {
        $k = 'collection';
        if (null === $this->getData($k)) {
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

            if ($maxDepth = $this->getMaxDepth()) {
                $categories->addAttributeToFilter('level', ['lteq' => $maxDepth]);
            }

            if ($excludedCategoriesIds = $this->getExcludedCategoriesIds()) {
                $categories->addAttributeToFilter('entity_id', ['nin' => $excludedCategoriesIds]);
            }

            $this->setData($k, $categories);
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

            $collection = $this->getCategoryTree($this->getCollection());

            if ($limit = $this->getPageSize()) {
                $collection = array_slice($collection, 0, $limit);
            }
            foreach ($collection as $collectionItem) {
                $item = new DataObject([
                    'url' => $collectionItem->getUrl(),
                    'name' => $collectionItem->getName(),
                    'level' => $collectionItem->getLevel(),
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
        return $this->getUrl('htmlsitemap/catalog/categories');
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
    protected function getMaxDepth(): int
    {
        return 0;
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
}
