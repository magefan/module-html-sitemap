<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\HtmlSitemap\Setup\Patch\Data;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class MigrateFromOldToNewAdditionalLinks implements DataPatchInterface, PatchVersionInterface
{
    const CONFIG_PATH_ADDITIONALLINKS = 'mfhs/additionallinks/links';
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var WriterInterface
     */
    private $configWriter;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param WriterInterface $configWriter
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        WriterInterface $configWriter,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->configWriter = $configWriter;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * @return void
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        foreach ($this->storeManager->getStores() as $store) {
            $scope = ScopeInterface::SCOPE_STORE;
            $scopeID = $store->getId();
            $links = $this->scopeConfig->getValue(
                self::CONFIG_PATH_ADDITIONALLINKS,
                $scope,
                $store->getId()
            );
            if (!$links) {
                continue;
            }
            $links = str_replace(["\n", "\r"], [PHP_EOL, PHP_EOL], $links);
            $links = explode(PHP_EOL, $links);

            $newLinks = [];
            foreach ($links as $key => $link) {
                $link = trim($link);
                $link = trim($link, '/');
                if (!$link) {
                    continue;
                }

                $linkData = explode('|', $link);
                if (isset($linkData[0])) {
                    $url = trim($linkData[0]);
                    $timestamp = round(microtime(true) * 1000);
                    $milliseconds = $timestamp % 1000;
                    $result = '_' . $timestamp . '_' . $milliseconds + $key;
                    $newLinks[$result] = [
                        'url' => $url,
                        'title' => isset($linkData[1]) ? $linkData[1] : $url,
                    ];
                }
            }

            if ($store->getCode() == 'default')
            {
                $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
                $scopeID = null;
            }

            $this->configWriter->save(self::CONFIG_PATH_ADDITIONALLINKS, json_encode($newLinks),$scope,$scopeID);
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    public static function getVersion()
    {
        return "2.2.3";
    }
}