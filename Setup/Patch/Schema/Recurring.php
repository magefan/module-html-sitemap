<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\HtmlSitemap\Setup\Patch\Schema;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;

class Recurring implements SchemaPatchInterface, PatchRevertableInterface
{

    /**
     * @var SchemaSetupInterface
     */
    private $schemaSetup;

    /**
     * Constructor.
     *
     * @param SchemaSetupInterface $schemaSetup
     */
    public function __construct(SchemaSetupInterface $schemaSetup)
    {
        $this->schemaSetup = $schemaSetup;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $setup = $this->schemaSetup;
        $setup->startSetup();
        $connection = $setup->getConnection();

        foreach (['magefan_blog_post', 'magefan_blog_category'] as $tableName) {
            $tableName = $setup->getTable($tableName);

            if ($connection->isTableExists($tableName) == true
                && !$connection->tableColumnExists($tableName, 'mf_exclude_html_sitemap')) {
                $columns = [
                    'mf_exclude_html_sitemap' => [
                        'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                        'nullable' => false,
                        'comment' => 'Exclude From HTML Sitemap',
                        'default' => '0'
                    ],
                ];

                foreach ($columns as $name => $definition) {
                    $connection->addColumn($tableName, $name, $definition);
                }
            }
        }

        $setup->endSetup();
    }

    /**
     * Revert function
     */
    public function revert()
    {
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
}
