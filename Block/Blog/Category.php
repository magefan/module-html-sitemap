<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\HtmlSitemap\Block\Blog;

use Magefan\HtmlSitemap\Model\Config;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\StoreManagerInterface;
use Magefan\HtmlSitemap\Model\BlogFactory;
use Magefan\HtmlSitemap\Block\AbstractSitemap;
use Magento\Framework\UrlInterface;

class Category extends AbstractSitemap
{
    const XML_PATH_TO_BLOG_CATEGORY_TITLE = 'mfhs/blogcategorylinks/title';
    const XML_PATH_TO_BLOG_CATEGORY_DEPTH = 'mfhs/blogcategorylinks/maxdepth';
    const XML_PATH_TO_BLOG_CATEGORY_VIEW_MORE = 'mfhs/blogcategorylinks/displaymore';
    const XML_PATH_TO_BLOG_CATEGORY_LIMIT = 'mfhs/blogcategorylinks/maxnumberlinks';

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
     * @var BlogFactory
     */
    private $blogFactory;

    /**
     * Category constructor.
     * @param Template\Context $context
     * @param Config $config
     * @param StoreManagerInterface $storeManager
     * @param BlogFactory $blogFactory
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        UrlInterface $url,
        Config $config,
        StoreManagerInterface $storeManager,
        BlogFactory $blogFactory,
        array $data = []
    ) {
        parent::__construct($context, $url, $data);
        $this->config = $config;
        $this->storeManager = $storeManager;
        $this->blogFactory = $blogFactory;
        $this->ignoredLinks = $config->getIgnoredLinks();
    }

    /**
     * @return string
     */
    public function getBlockTitle()
    {
        return (string)$this->config->getConfig(self::XML_PATH_TO_BLOG_CATEGORY_TITLE);
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function showViewMore()
    {
        if ($this->config->getConfig(self::XML_PATH_TO_BLOG_CATEGORY_VIEW_MORE)
            && count($this->getAllGroupedChildes())> $this->config->getConfig(self::XML_PATH_TO_BLOG_CATEGORY_LIMIT)
        ) {
            return true;
        }
        return false;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getGroupedChildes()
    {
        $pageSize = $this->config->getConfig(self::XML_PATH_TO_BLOG_CATEGORY_LIMIT);
        $k = 'grouped_childs';
        if (!$this->hasData($k)) {
            $array = $this->blogFactory->createCategoryCollection()
                ->addActiveFilter()
                ->addStoreFilter($this->_storeManager->getStore()->getId());
            if (!empty($this->ignoredLinks)) {
                $array->addFieldToFilter('identifier', ['nin' => $this->config->getIgnoredLinks()]);
            }

            $array = $array->addFieldToFilter('mf_exclude_html_sitemap', 0)
                ->setOrder('position')
                ->getTreeOrderedArray();
            $array = array_slice($array, 0, $pageSize);


            foreach ($array as $key => $item) {
                $maxDepth = $this->config->getConfig(self::XML_PATH_TO_BLOG_CATEGORY_DEPTH);
                if ($maxDepth > 0 && $item->getLevel() >= $maxDepth) {
                    unset($array[$key]);
                }
            }

            $this->setData($k, $array);
        }

        return $this->getData($k);
    }

    /**
     * Get grouped categories
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAllGroupedChildes()
    {
        $k = 'all_grouped_childes';
        if (!$this->hasData($k)) {
            $array = $this->blogFactory->createCategoryCollection()
                ->addActiveFilter()
                ->addStoreFilter($this->_storeManager->getStore()->getId());
            if (!empty($this->ignoredLinks)) {
                $array->addFieldToFilter('identifier', ['nin' => $this->config->getIgnoredLinks()]);
            }

            $array = $array->addFieldToFilter('mf_exclude_html_sitemap', 0)
                ->setOrder('position')
                ->getTreeOrderedArray();

            foreach ($array as $key => $item) {
                $maxDepth = $this->config->getConfig(self::XML_PATH_TO_BLOG_CATEGORY_DEPTH);
                if ($maxDepth > 0 && $item->getLevel() >= $maxDepth) {
                    unset($array[$key]);
                }
            }

            $this->setData($k, $array);
        }

        return $this->getData($k);
    }

    /**
     * @return int
     */
    public function getSortOrder()
    {
        return $this->config->getSortOrder('blogcategorylinks');
    }

    public function toHtml()
    {
        if (!$this->config->isBlogEnabled()) {
            return '';
        }
        return parent::toHtml(); // TODO: Change the autogenerated stub
    }
}
