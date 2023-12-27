<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

declare(strict_types=1);

namespace Magefan\HtmlSitemap\Block;

use Magefan\HtmlSitemap\Block\Type\Block;
use Magento\Framework\View\Element\Text;
use Magento\Store\Model\StoreManagerInterface;
use Magefan\HtmlSitemap\Model\Config;
use Magento\Framework\View\Page\Config as PageConfig;

class Index extends Text
{

    const XML_PATH_TO_PAGE_TITLE = 'mfhs/seo/title';
    const XML_PATH_TO_PAGE_META_TITLE = 'mfhs/seo/metatitle';
    const XML_PATH_TO_PAGE_META_DESCRIPTION = 'mfhs/seo/metadescription';
    const XML_PATH_TO_PAGE_META_KEYWORDS = 'mfhs/seo/metakeywords';

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var PageConfig
     */
    protected $pageConfig;

    /**
     * Index constructor.
     * @param \Magento\Framework\View\Element\Context $context
     * @param Config $config
     * @param StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        Config $config,
        StoreManagerInterface $storeManager,
        PageConfig $pageConfig,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->config = $config;
        $this->storeManager = $storeManager;
        $this->pageConfig = $pageConfig;
    }

    /**
     * @return $this|Template
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $this->addBreadcrumbs();

        $title = $this->config->getConfig(self::XML_PATH_TO_PAGE_TITLE) ?: '';
        $metaTitle = $this->config->getConfig(self::XML_PATH_TO_PAGE_META_TITLE) ?: '';
        $metaDescription = $this->config->getConfig(self::XML_PATH_TO_PAGE_META_DESCRIPTION) ?: '';
        $metaKeywords = $this->config->getConfig(self::XML_PATH_TO_PAGE_META_KEYWORDS) ?: '';

        $this->pageConfig->getTitle()->set($metaTitle);

        if ($metaDescription) {
            $this->pageConfig->setDescription($metaDescription);
        }

        if ($metaKeywords) {
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
                    'link' => $this->storeManager->getStore()->getBaseUrl()
                ]
            );

            $breadcrumbsBlock->addCrumb(
                'htmlsitemap',
                [
                    'label' => __('Sitemap'),
                    'title' => __('Sitemap'),
                ]
            );


        }

        return $this;
    }
}
