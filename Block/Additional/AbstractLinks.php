<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
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
            $pageSize = $this->getPageSize();
            $collection = [];
            $links = $this->config->getAdditionalLinks() ?: '';
            $links = str_replace(["\n", "\r"], [PHP_EOL, PHP_EOL], $links);
            $links = explode(PHP_EOL, $links);
            foreach ($links as $link) {
                $link = trim($link);
                $link = trim($link, '/');
                if (!$link) {
                    continue;
                }

                $linkData = explode('|', $link);

                $url = trim($linkData[0]);

                if (in_array($url, $this->config->getIgnoredLinks(null, false))) {
                    continue;
                }


                $collection[] = new DataObject([
                    'url' => $linkData[0],
                    'name' => isset($linkData[1]) ? $linkData[1] : $linkData[0]
                ]);
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
