<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\HtmlSitemap\Model\Config\Source;

class DisplayIn implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => '1',
                'label' => __('Top Links')
            ],
            [
                'value' => '2',
                'label' => __('Footer Links')
            ]
        ];
    }
}
