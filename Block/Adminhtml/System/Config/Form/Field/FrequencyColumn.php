<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

declare(strict_types=1);

namespace Magefan\HtmlSitemap\Block\Adminhtml\System\Config\Form\Field;

use Magento\Framework\View\Element\Html\Select;

use Magento\Backend\Block\Template\Context;

class FrequencyColumn extends Select
{
    public function __construct(
        \Magento\Sitemap\Model\Config\Source\Frequency $frequency,
        Context $context,
        array $data = []
    ) {
        $this->_frequency = $frequency;
        parent::__construct($context, $data);
    }

    /**
     * Set "name" for <select> element
     *
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * Set "id" for <select> element
     *
     * @param $value
     * @return $this
     */
    public function setInputId($value)
    {
        return $this->setId($value);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml(): string
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->getSourceOptions());
        }
        return parent::_toHtml();
    }

    private function getSourceOptions(): array
    {
        return $this->_frequency->toOptionArray();
    }
}
