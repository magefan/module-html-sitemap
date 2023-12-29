<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

declare(strict_types=1);

namespace Magefan\HtmlSitemap\Block\Cms;

use Magefan\HtmlSitemap\Block\AbstractBlock;
use Magento\Framework\View\Element\Template;
use Magefan\HtmlSitemap\Model\Config;
use Magento\Framework\DataObject;
use Magento\Cms\Model\ResourceModel\Page\Collection;
use Magento\Cms\Model\ResourceModel\Page\CollectionFactory as PageCollectionFactory;

abstract class AbstractPages extends AbstractBlock
{

    /**
     * @var PageCollectionFactory
     */
    protected $pageCollectionFactory;

    /**
     * @var string
     */
    protected $type = 'cmspagelinks';

    /**
     * AbstractPages constructor.
     * @param Template\Context $context
     * @param Config $config
     * @param PageCollectionFactory $pageCollectionFactory
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Config $config,
        PageCollectionFactory $pageCollectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $config, $data);
        $this->pageCollectionFactory = $pageCollectionFactory;
    }

    /**
     * @return Collection
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getCollection()
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

    /**
     * @return array|mixed|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
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
     * @return string
     */
    public function getCurrentTypeHtmlSitemapUrl()
    {
        return $this->getUrl('htmlsitemap/cms/pages');
    }
}
