<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

declare(strict_types=1);

namespace Magefan\HtmlSitemap\Block\Index;

trait Block
{
    /**
     * @return int
     */
    protected function getPageSize(): int
    {
        return (int)$this->config->getBlockPageSize($this->type);
    }

    /**
     * @return int
     */
    public function getSortOrder(): int
    {
        return (int)$this->config->getSortOrder($this->type);
    }

    /**
     * @return bool
     */
    public function showBlockTitle(): bool
    {
        return true;
    }
}