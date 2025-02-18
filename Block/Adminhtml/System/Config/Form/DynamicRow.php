<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\HtmlSitemap\Block\Adminhtml\System\Config\Form;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

class DynamicRow extends AbstractFieldArray
{

    /**
     * Prepare rendering the new field by adding all the needed columns
     */
    protected function _prepareToRender()
    {
        $this->addColumn('url', [
            'style' => 'width:170px',
            'label' => __('URL'),
            'class' => 'required-entry'
        ]);
        $this->addColumn('title', [
            'label' => __('Title'),
            'class' => 'required-entry',
            'style' => 'width:200px'
        ]);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $html = parent::_getElementHtml($element);
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $secureHtmlRenderer = $objectManager->get(\Magefan\Community\Api\SecureHtmlRendererInterface::class);
        $script = '
                document.addEventListener("DOMContentLoaded", function(event) {
                    require([
                        \'jquery\',
                        \'Magento_Theme/js/sortable\'
                    ], function ($) {
                        var element = $(\'#row_mfhs_additionallinks_links\');
                        function applyChanges() {
                            element.find(\'tbody\').find(\'tr\').each(function() {
                                if (!$(this).find(\'.draggable-handle\').length) { 
                                    var handle = $(\'<td><div class="draggable-handle" data-bind="afterRender: $data.initListeners"></div></td>\');
                                    $(this).prepend(handle);
                                }
                            });
                            element.find(\'tbody\').sortable({
                                containment: "parent",
                                items: \'tr\',
                                tolerance: \'pointer\'
                            });
                        };
                        setTimeout(function () {
                            element.find(\'tfoot\').find(\'.col-actions-add\').attr(\'colspan\', \'4\');
                            element.find(\'thead\').find(\'tr\').prepend(\'<th></th>\');
                            applyChanges();
                        }, 1000);
                        $(\'.action-add\').on(\'click\', function() {
                            applyChanges();
                        });
                    });
                });
            ';
        $html .= $secureHtmlRenderer->renderTag('script', [], $script, false);
        return $html;
    }
}
