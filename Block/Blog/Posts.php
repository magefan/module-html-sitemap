<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
declare(strict_types=1);

namespace Magefan\HtmlSitemap\Block\Blog;

class Posts extends AbstractPosts
{

    /**
     * @return $this|Post
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $this->pageConfig->addRemotePageAsset(
            $this->getUrl('htmlsitemap/blog/posts'),
            'canonical',
            ['attributes' => ['rel' => 'canonical']]
        );

        $title = $this->getBlockTitle();
        if ($title) {
            $this->pageConfig->getTitle()->set( __('Sitemap') . ' - ' .  $this->getBlockTitle());
        }

        return $this;
    }
}
