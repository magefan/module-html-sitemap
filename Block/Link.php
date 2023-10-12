<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\HtmlSitemap\Block;

use Magefan\HtmlSitemap\Model\Config;

class Link extends \Magento\Framework\View\Element\Html\Link
{
    /**
     * @var \Magefan\HtmlSitemap\Model\Url
     */
    protected $_url;

    /**
     * @var Config
     */
    private Config $config;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\UrlInterface $url
     * @param Config $config
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\UrlInterface $url,
        Config $config,
        array $data = []
    ) {
        $this->_url = $url;
        $this->config = $config;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getHref()
    {
        return $this->_url->getBaseUrl() . 'htmlsitemap';
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->_scopeConfig->getValue(
            \Magefan\HtmlSitemap\Model\Config::XML_PATH_HOMEPAGE_TITLE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     */
    public function getOpenLinks()
    {
        return $this->_scopeConfig->getValue(
            \Magefan\HtmlSitemap\Model\Config::XML_PATH_OPENLINKS,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->_scopeConfig->getValue(
            \Magefan\HtmlSitemap\Model\Config::XML_PATH_EXTENSION_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        )) {
            return '';
        }
        return parent::_toHtml();
    }

    /**
     * @return bool
     */
    public function canShowSitemapLink(): bool
    {
        return in_array('1', $this->getDisplayIn());
    }

    /**
     * @return array
     */
    private function getDisplayIn(): array
    {
        return explode(',', $this->config->displayIn() ?: '');
    }
}
