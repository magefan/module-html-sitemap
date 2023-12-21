<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
declare(strict_types=1);

namespace Magefan\HtmlSitemap\Block\Index\Catalog;

use Magefan\HtmlSitemap\Block\Catalog\AbstractCategories;

class Categories extends AbstractCategories
{
    /**
     * @return int
     */
    protected function getPageSize(): int
    {
        return (int)$this->config->getConfig(self::XML_PATH_TO_CATALOG_CATEGORY_LIMIT);
    }

    /**
     * @return int
     */
    protected function getMaxDepth(): int
    {
        return (int)$this->config->getConfig(self::XML_PATH_TO_CATALOG_CATEGORY_DEPTH);
    }

    /**
     * @return int
     */
    public function getSortOrder(): int
    {
        return (int)$this->config->getSortOrder('categorylinks');
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function showViewMore(): bool
    {
        if ($this->config->getConfig(self::XML_PATH_TO_CATALOG_CATEGORY_VIEW_MORE)
            && count($this->getCollection()) > $this->getPageSize()
        ) {
            return true;
        }
        return false;
    }
}