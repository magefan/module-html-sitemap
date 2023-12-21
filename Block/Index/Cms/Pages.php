<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
declare(strict_types=1);

namespace Magefan\HtmlSitemap\Block\Index\Cms;

use Magefan\HtmlSitemap\Block\Cms\AbstractPages;

class Pages extends AbstractPages
{
    /**
     * @return int
     */
    protected function getPageSize(): int
    {
        return (int)$this->config->getConfig(self::XML_PATH_TO_CMS_PAGE_LIMIT);
    }

    /**
     * @return int
     */
    public function getSortOrder(): int
    {
        return (int)$this->config->getSortOrder('cmspagelinks');
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function showViewMore(): bool
    {
        if ($this->config->getConfig(self::XML_PATH_TO_CMS_PAGE_VIEW_MORE)
            && $this->getCollection()->getSize() > $this->getPageSize()
        ) {
            return true;
        }
        return false;
    }
}
