<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\HtmlSitemap\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\UrlInterface;

abstract class AbstractSitemap extends Template
{

    protected $url;

    public function __construct(
        Template\Context $context,
        UrlInterface $url,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->url = $url;
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $title = $this->getBlockTitle();

        if ($title) {
            $this->pageConfig->getTitle()->set( __('Sitemap') . ' - ' .  $this->getBlockTitle());
        }

        $canonicalUrl = $this->url->getCurrentUrl();
        $page = (int)$this->_request->getParam($this->getPageParamName());
        if ($page > 1) {
            $canonicalUrl .= ((false === strpos($canonicalUrl, '?')) ? '?' : '&')
                . 'p=' . $page;
        }

        $this->pageConfig->addRemotePageAsset(
            $canonicalUrl,
            'canonical',
            ['attributes' => ['rel' => 'canonical']]
        );

        return $this;
    }
}