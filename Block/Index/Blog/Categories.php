<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

declare(strict_types=1);

namespace Magefan\HtmlSitemap\Block\Index\Blog;

use Magefan\HtmlSitemap\Block\Blog\AbstractCategories;

class Categories extends AbstractCategories
{
    use \Magefan\HtmlSitemap\Block\Index\Block;

    /**
     * @return int
     */
    protected function getMaxDepth(): int
    {
        return (int)$this->config->getBlockMaxDepth($this->type);
    }
}
