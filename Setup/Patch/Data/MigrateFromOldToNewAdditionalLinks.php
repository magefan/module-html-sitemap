<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\HtmlSitemap\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

class MigrateFromOldToNewAdditionalLinks implements DataPatchInterface, PatchVersionInterface
{
    const CONFIG_PATH_ADDITIONALLINKS = 'mfhs/additionallinks/links';

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * @return void
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();

        $connection = $this->moduleDataSetup->getConnection();

        $table = $this->moduleDataSetup->getTable('core_config_data');
        $select = $connection->select()
            ->from($table)
            ->where('path LIKE ?', self::CONFIG_PATH_ADDITIONALLINKS);

        $allData = $connection->fetchAll($select);

        foreach ($allData as $data) {
            $links = $data['value'];

            if (!$links || strpos($links, '|')  === false) {
                continue;
            }
            $links = str_replace(["\r\n", "\n\r", "\r", "\n"], [PHP_EOL, PHP_EOL, PHP_EOL, PHP_EOL], $links);
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

            if (!empty($newLinks)) {
                $connection->update(
                    $table,
                    ['value' => json_encode($newLinks)],
                    [
                        'path = ?' => self::CONFIG_PATH_ADDITIONALLINKS,
                        'config_id = ?' => $data['config_id']
                    ]
                );
            }

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
        return "2.0.4";
    }
}