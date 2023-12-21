<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
declare(strict_types=1);

namespace Magefan\HtmlSitemap\Block\Index\Blog;

use \Magefan\HtmlSitemap\Block\Blog\AbstractPosts;

class Posts extends AbstractPosts
{
    /**
     * @return int
     */
    protected function getPageSize(): int
    {
        return (int)$this->config->getConfig(self::XML_PATH_TO_BLOG_POSTS_LIMIT);
    }

    /**
     * @return int
     */
    public function getSortOrder(): int
    {
        return (int)$this->config->getSortOrder('blogpostlinks');
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function showViewMore(): bool
    {
        if ($this->config->getConfig(self::XML_PATH_TO_BLOG_POST_VIEW_MORE)
            && $this->getCollection()->getSize() > $this->getPageSize()
        ) {
            return true;
        }
        return false;
    }
}
