<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\HtmlSitemap\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    /**
     * Extension enabled config path
     */
    const XML_PATH_EXTENSION_ENABLED = 'mfhs/general/enabled';

    /**
     * HTML Sitemap homepage title
     */
    const XML_PATH_HOMEPAGE_TITLE = 'mfhs/index_page/title';

    /**
     * Display in config path
     */
    const XML_PATH_DISPLAY_IN = 'mfhs/general/displayin';

    /**
     * Openlinks config path
     */
    const XML_PATH_OPENLINKS = 'mfhs/general/openlinks';

    const XML_PATH_TO_IGNORE_LIST = 'mfhs/ignored/links';

    /**
     * Blog extension enabled config path
     */
    const XML_PATH_BLOG_EXTENSION_ENABLED = 'mfblog/general/enabled';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Config constructor.
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Retrieve true if blog module is enabled
     *
     * @param null $storeId
     * @return bool
     */
    public function isEnabled($storeId = null)
    {
        return (bool)$this->getConfig(
            self::XML_PATH_EXTENSION_ENABLED,
            $storeId
        );
    }

    /**
     * @param $name
     * @param null $storeId
     * @return int
     */
    public function getSortOrder($name, $storeId = null)
    {
        $path = 'mfhs/' . $name . '/position';
        return (int)$this->getConfig($path, $storeId);
    }

    /**
     * @param null $storeId
     * @param bool $toUrlKeys
     * @return array
     */
    public function getIgnoredLinks($storeId = null, $toUrlKeys = true)
    {
        $ignoredLinks = [];

        $ignoredLinksFromConfig = $this->getConfig(self::XML_PATH_TO_IGNORE_LIST, $storeId);
        if ($ignoredLinksFromConfig) {
            $ignoredLinks = preg_split('/\s+/', $ignoredLinksFromConfig);
        }

        if ($toUrlKeys) {
            $urlKeys = [];

            foreach ($ignoredLinks as $link) {
                $link = basename($link);
                if (stripos($link, '.html') !== false) {
                    $urlKeys[] = str_replace('.html', '', $link);
                } else {
                    $urlKeys[] = $link;
                }
            }

            return (array)$urlKeys;
        }

        return (array)$ignoredLinks;
    }

    /**
     * @param null $storeId
     * @return bool
     */
    public function isBlogEnabled($storeId = null)
    {
        return (bool)$this->getConfig(self::XML_PATH_BLOG_EXTENSION_ENABLED, $storeId);
    }

    /**
     * Retrieve store config value
     * @param string $path
     * @param null $storeId
     * @return mixed
     */
    public function getConfig($path, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function displayIn($storeId = null)
    {
        return $this->getConfig(
            self::XML_PATH_DISPLAY_IN,
            $storeId
        );
    }
}
