<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

declare(strict_types=1);

namespace Magefan\HtmlSitemap\Block\Blog;

use Magefan\HtmlSitemap\Block\AbstractBlock;
use Magento\Framework\View\Element\Template;
use Magefan\HtmlSitemap\Model\Config;
use Magento\Framework\DataObject;
use Magefan\HtmlSitemap\Model\BlogFactory;

abstract class AbstractPosts extends AbstractBlock
{
    /**
     * @var BlogFactory
     */
    protected $blogFactory;

    /**
     * @var string
     */
    protected $type = 'blogpostlinks';

    /**
     * Post constructor.
     * @param Template\Context $context
     * @param Config $config
     * @param BlogFactory $blogFactory
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Config $config,
        BlogFactory $blogFactory,
        array $data = []
    ) {
        parent::__construct($context, $config, $data);
        $this->blogFactory = $blogFactory;
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCollection()
    {
        $k = 'collection';
        if (null === $this->getData($k)) {
            $collection = $this->blogFactory->createPostCollection()
                ->addActiveFilter()
                ->addStoreFilter($this->_storeManager->getStore()->getId())
                ->addFieldToFilter('mf_exclude_html_sitemap', 0);

            if ($ignoredLinks = $this->config->getIgnoredLinks()) {
                $collection->addFieldToFilter('identifier', ['nin' => $ignoredLinks]);
            }

            if ($pageSize = $this->getPageSize()) {
                $collection->setPageSize($pageSize);
            }
            $this->setData($k, $collection);
        }

        return $this->getData($k);
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getItems()
    {
        $k = 'items';
        if (null === $this->getData($k)) {
            $items = [];
            foreach ($this->getCollection() as $collectionItem) {
                $item = new DataObject([
                    'url' => $collectionItem->getPostUrl(),
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
     * @return string
     */
    public function getCurrentTypeHtmlSitemapUrl()
    {
        return $this->getUrl('htmlsitemap/blog/posts');
    }

    /**
     * @return string
     */
    public function toHtml()
    {
        if (!$this->config->isBlogEnabled()) {
            return '';
        }
        return parent::toHtml(); // TODO: Change the autogenerated stub
    }
}
