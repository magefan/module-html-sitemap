<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

declare(strict_types=1);

namespace Magefan\HtmlSitemap\Block\Type;

trait Block
{

    /**
     * @return array
     */
    public function getItemsGroupedByLetter()
    {
        $k = 'items_group_by_letter';
        if (null === $this->getData($k)) {
            $result = [];
            $items = $this->getItems();

            $letters = range('A', 'Z');
            $letters[] = '#';

            foreach ($letters as $letter) {
                foreach ($items as $item) {
                    if (mb_strtoupper(substr($item->getName(), 0, 1)) == $letter || $letter == '#') {
                        $result[$letter][] = $item;
                    }
                }
            }
            $this->setData($k, $result);
        }
        return $this->getData($k);
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $this->addBreadcrumbs();

        $this->pageConfig->addRemotePageAsset(
            $this->getCurrentTypeHtmlSitemapUrl(),
            'canonical',
            ['attributes' => ['rel' => 'canonical']]
        );

        $title = $this->getBlockTitle();

        if ($title) {
            $this->pageConfig->getTitle()->set(__('Sitemap') . ' - ' .  $this->getBlockTitle());
        }

        return $this;
    }

    /**
     * @return $this
     */
    protected function addBreadcrumbs()
    {
        if ($breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs')
        ) {
            $breadcrumbsBlock->addCrumb(
                'home',
                [
                    'label' => __('Home'),
                    'title' => __('Home'),
                    'link' => $this->_storeManager->getStore()->getBaseUrl()
                ]
            );

            $breadcrumbsBlock->addCrumb(
                'htmlsitemap',
                [
                    'label' => __('Sitemap'),
                    'title' => __('Sitemap'),
                    'link' => $this->_storeManager->getStore()->getBaseUrl() . 'htmlsitemap'
                ]
            );

            $breadcrumbsBlock->addCrumb(
                'htmlsitemap_' . $this->type,
                [
                    'label' => __(ucfirst($this->type)),
                    'title' => __(ucfirst($this->type)),
                ]
            );

        }

        return $this;
    }
}
