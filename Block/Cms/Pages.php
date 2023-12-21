<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
declare(strict_types=1);

namespace Magefan\HtmlSitemap\Block\Cms;

class Pages extends AbstractPages
{

    /**
     * @return $this|Template
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $this->pageConfig->addRemotePageAsset(
            $this->getUrl('htmlsitemap/cms/pages'),
            'canonical',
            ['attributes' => ['rel' => 'canonical']]
        );

        $title = $this->getBlockTitle();

//        if ($title) {
//            $this->pageConfig->getTitle()->set( __('Sitemap') . ' - ' .  $this->getBlockTitle());
//        }
//        return $this;

        $metaTitle = $this->config->getConfig(self::XML_PATH_TO_PAGE_META_TITLE);
        $title = $this->config->getConfig(self::XML_PATH_TO_PAGE_TITLE);
        $metaDescription = $this->config->getConfig(self::XML_PATH_TO_PAGE_META_DESCRIPTION);
        $metaKeywords = $this->config->getConfig(self::XML_PATH_TO_PAGE_META_KEYWORDS);

        $this->pageConfig->getTitle()->set($metaTitle ?: $title);

        if (!empty($metaDescription)) {
            $this->pageConfig->setDescription($metaDescription);
        }

        if (!empty($metaKeywords)) {
            $this->pageConfig->setKeywords($metaKeywords);
        }

        $pageMainTitle = $this->getLayout()->getBlock('page.main.title');

        if ($pageMainTitle) {
            $pageMainTitle->setPageTitle(
                $this->_escaper->escapeHtml($title)
            );
        }

        return $this;
    }
}
