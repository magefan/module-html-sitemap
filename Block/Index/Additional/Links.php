<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
declare(strict_types=1);

namespace Magefan\HtmlSitemap\Block\Index\Additional;

use Magefan\HtmlSitemap\Block\Additional\AbstractLinks;

class Links extends AbstractLinks
{
    /**
     * @return int
     */
    protected function getPageSize(): int
    {
        return (int)$this->config->getConfig(self::XML_PATH_TO_ADDITIONAL_LINKS_LIMIT);
    }

    /**
     * @return int
     */
    public function getSortOrder(): int
    {
        return (int)$this->config->getSortOrder('additionallinks');
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function showViewMore(): bool
    {
        if ($this->config->getConfig(self::XML_PATH_TO_ADDITIONAL_LINKS_MORE)
            && count($this->getItems()) > $this->getPageSize()
        ) {
            return true;
        }
        return false;
    }
}
