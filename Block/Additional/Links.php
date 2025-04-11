<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

declare(strict_types=1);

namespace Magefan\HtmlSitemap\Block\Additional;

class Links extends AbstractLinks
{
    use \Magefan\HtmlSitemap\Block\Type\Block;

    /**
     * @return int
     */
    protected function getPageSize(): int
    {
        return count($this->config->getAdditionalLinks());
    }

    /**
     * @return bool
     */
    public function showViewMore(): bool
    {
        return false;
    }
}
