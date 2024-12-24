<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

declare(strict_types=1);

namespace Magefan\HtmlSitemap\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\Serialize\SerializerInterface;
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

    /**
     * Ignored links
     */
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
     * @var array
     */
    private $ignoredLinks;

    /**
     * @var ModuleListInterface
     */
    private $moduleList;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param SerializerInterface $serializer
     * @param ScopeConfigInterface $scopeConfig
     * @param ModuleListInterface|null $moduleList
     */
    public function __construct(
        SerializerInterface $serializer,
        ScopeConfigInterface $scopeConfig,
        ModuleListInterface $moduleList = null
    ) {
        $this->serializer = $serializer;
        $this->scopeConfig = $scopeConfig;
        $this->moduleList = $moduleList ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(ModuleListInterface::class);
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
     * @param string $name
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
        if (null === $this->ignoredLinks) {
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

            $this->ignoredLinks = (array)$ignoredLinks;
        }
        return $this->ignoredLinks;
    }

    /**
     * @param null $storeId
     * @return bool
     */
    public function isBlogEnabled($storeId = null)
    {
        return $this->getConfig(self::XML_PATH_BLOG_EXTENSION_ENABLED, $storeId)
            && $this->moduleList->getOne('Magefan_Blog');
    }

    /**
     * @param string $type
     * @param null $storeId
     * @return string
     */
    public function getBlockTitle(string $type, $storeId = null): string
    {
        return $this->getConfig('mfhs/' . $type . '/title');
    }

    /**
     * @param string $type
     * @param null $storeId
     * @return string
     */
    public function getBlockViewMore(string $type, $storeId = null): string
    {
        return $this->getConfig('mfhs/' . $type . '/displaymore');
    }

    /**
     * @param string $type
     * @param null $storeId
     * @return string
     */
    public function getBlockPageSize(string $type, $storeId = null): string
    {
        return $this->getConfig('mfhs/' . $type . '/maxnumberlinks');
    }

    /**
     * @param string $type
     * @param null $storeId
     * @return string
     */
    public function getBlockMaxDepth(string $type, $storeId = null): string
    {
        return $this->getConfig('mfhs/' . $type . '/maxdepth');
    }

    /**
     * @param null $storeId
     * @return array
     */
    public function getAdditionalLinks($storeId = null): array
    {
        $data = $this->getConfig('mfhs/additionallinks/links', $storeId);

        $additionalLinks = [];
        if ($data) {
            $values = $this->serializer->unserialize($data);
            foreach ($values as $item) {
                $additionalLinks[] = $item;
            }
        }
        return $additionalLinks;

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
