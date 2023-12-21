<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
declare(strict_types=1);

namespace Magefan\HtmlSitemap\Block\Catalog;


class Categories extends AbstractCategories
{

    /**
     * @return $this|Category
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $this->pageConfig->addRemotePageAsset(
            $this->getUrl('htmlsitemap/catalog/categories'),
            'canonical',
            ['attributes' => ['rel' => 'canonical']]
        );

        $title = $this->getBlockTitle();

        if ($title) {
            $this->pageConfig->getTitle()->set( __('Sitemap') . ' - ' .  $this->getBlockTitle());
        }

        return $this;
    }
}
