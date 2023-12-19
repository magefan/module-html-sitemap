<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\HtmlSitemap\Block;

use Magento\Framework\View\Element\Text;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Page\Config;

class Sitemap extends Text
{
    protected $url;

    protected $pageConfig;

    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        UrlInterface $url,
        Config $pageConfig,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->url = $url;
        $this->pageConfig = $pageConfig;
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _toHtml()
    {
        $this->setText('');
        $childNames = $this->getChildNames();

        usort($childNames, [$this, 'sortChild']);

        $layout = $this->getLayout();
        foreach ($childNames as $child) {
            $this->addText($layout->renderElement($child, false));
        }

        return parent::_toHtml();
    }

    /**
     * @param $a
     * @param $b
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function sortChild($a, $b)
    {
        $layout = $this->getLayout();
        $blockA = $layout->getBlock($a);
        $blockB = $layout->getBlock($b);
        if ($blockA && $blockB) {
            $r = $blockA->getSortOrder() > $blockB->getSortOrder() ? 1 : - 1;
            return $r;
        }

        return 0;
    }

    protected function _prepareLayout()
    {
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
        return parent::_prepareLayout();
    }
}
