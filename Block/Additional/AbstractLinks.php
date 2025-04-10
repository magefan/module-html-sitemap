<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

declare(strict_types=1);

namespace Magefan\HtmlSitemap\Block\Additional;

use Magento\Framework\DataObject;
use Magefan\HtmlSitemap\Block\AbstractBlock;

abstract class AbstractLinks extends AbstractBlock
{
    /**
     * @var string
     */
    protected $type = 'additionallinks';

    /**
     * @return array
     */
    protected function getCollection()
    {
        $k = 'collection';
        if (null === $this->getData($k)) {
            $i = 0;
            $collection = [];
            $links = $this->config->getAdditionalLinks();
            $pageSize = ($this->_request->getFullActionName() == 'htmlsitemap_additional_links') ? count($links) : $this->getPageSize();
            foreach ($links as $link) {
                if (!empty($link['url'])) {
                    $collection[] = new DataObject([
                        'url' => $link['url'],
                        'name' => !empty($link['title']) ? $link['title'] : $link['url']
                    ]);
                }

                $i++;

                if ($i >= $pageSize) {
                    break;
                }
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
                    'url' => $collectionItem->getUrl(),
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
        return $this->getUrl('htmlsitemap/additional/links');
    }
}
