<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

declare(strict_types=1);

namespace Magefan\HtmlSitemap\Block\Adminhtml\System\Config\Form;

class Info extends \Magefan\Community\Block\Adminhtml\System\Config\Form\Info
{
    /**
     * Return extension url
     * @return string
     */
    protected function getModuleUrl()
    {
        return 'https://mage' . 'fan.com/magento-2-html-sitemap-extension?utm_source=m2admin_html_sitemap_config&utm_medium=link&utm_campaign=regular';
    }
    /**
     * Return extension title
     * @return string
     */
    protected function getModuleTitle()
    {
        return 'HTML Sitemap Extension';
    }
}
