<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\HtmlSitemap\Block\Additional;

use Magento\Framework\View\Element\Template;
use Magefan\HtmlSitemap\Model\Config;

class Additional extends Template
{
    const XML_PATH_TO_ADDITIONAL_BLOCK_TITLE = 'mfhs/additionallinks/title';
    const XML_PATH_TO_ADDITIONAL_LINKS = 'mfhs/additionallinks/links';
    const XML_PATH_TO_ADDITIONAL_LINKS_LIMIT = 'mfhs/additionallinks/maxnumberlinks';
    const XML_PATH_TO_ADDITIONAL_LINKS_MORE = 'mfhs/additionallinks/displaymore';

    /**
     * @var Config
     */
    private $config;

    /**
     * Additional constructor.
     * @param Template\Context $context
     * @param Config $config
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Config $config,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->config = $config;
    }

    /**
     * @return string
     */
    public function getBlockTitle()
    {
        return (string)$this->config->getConfig(self::XML_PATH_TO_ADDITIONAL_BLOCK_TITLE);
    }

    /**
     * @return mixed
     */
    protected function getAdditionalLinks()
    {
        return $this->config->getConfig(self::XML_PATH_TO_ADDITIONAL_LINKS);
    }

    /**
     * @return bool
     */
    public function showViewMore()
    {
        if ($this->config->getConfig(self::XML_PATH_TO_ADDITIONAL_LINKS_MORE)
            && count($this->getAllLinksArray())> $this->config->getConfig(self::XML_PATH_TO_ADDITIONAL_LINKS_LIMIT)
        ) {
            return true;
        }
        return false;
    }

    /**
     * @return array
     */
    public function getLinksArray()
    {
        $linksStr = $this->getAdditionalLinks();
        $links = preg_split('/\r+/', $linksStr);
        $linksArray = [];
        $pageSize = $this->config->getConfig(self::XML_PATH_TO_ADDITIONAL_LINKS_LIMIT);

        $i = 0;
        if (!empty($links)) {
            foreach ($links as $link) {
                if ($i >= $pageSize) {
                    break;
                }
                if (trim($link) !='') {
                    $linkElements = explode('|', $link);

                    if ($linkElements[0] == '/') {
                        $linkElements[0] = '';
                    }

                    if (!in_array(trim($linkElements[0]), $this->config->getIgnoredLinks(null, false))) {
                        $linksArray[] =['url' => $linkElements[0],'title' => trim($linkElements[1], '()')] ;
                        $i++;
                    }
                }
            }
        }
        return (array) $linksArray;
    }

    /**
     * @return array
     */
    public function getAllLinksArray()
    {
        $linksStr = $this->getAdditionalLinks();
        $links = preg_split('/\r+/', $linksStr);
        $linksArray = [];

        if (!empty($links)) {
            foreach ($links as $link) {
                if (trim($link) !='') {
                    $linkElements = explode('|', $link);

                    if ($linkElements[0] == '/') {
                        $linkElements[0] = '';
                    }

                    if (!in_array(trim($linkElements[0]), $this->config->getIgnoredLinks(null, false))) {
                        $linksArray[] =['url' => $linkElements[0],'title' => trim($linkElements[1], '()')] ;
                    }
                }
            }
        }
        return (array) $linksArray;
    }

    /**
     * @return int
     */
    public function getSortOrder()
    {
        return $this->config->getSortOrder('additionallinks');
    }

    /**
     * @return $this|Additional
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $title = $this->getBlockTitle();

        if ($title) {
            $this->pageConfig->getTitle()->set( __('Sitemap') . ' - ' .  $this->getBlockTitle());
        }

        return $this;
    }
}
