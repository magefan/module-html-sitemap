<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */
declare(strict_types=1);

namespace Magefan\HtmlSitemap\Block\Additional;

use Magento\Framework\View\Element\Template;
use Magefan\HtmlSitemap\Model\Config;

abstract class AbstractLinks extends Template
{
    const XML_PATH_TO_ADDITIONAL_BLOCK_TITLE = 'mfhs/additionallinks/title';
    const XML_PATH_TO_ADDITIONAL_LINKS = 'mfhs/additionallinks/links';
    const XML_PATH_TO_ADDITIONAL_LINKS_LIMIT = 'mfhs/additionallinks/maxnumberlinks';
    const XML_PATH_TO_ADDITIONAL_LINKS_MORE = 'mfhs/additionallinks/displaymore';

    /**
     * @var Config
     */
    protected $config;

    /**
     * Additional constructor.
     * @param Template\Context $context
     * @param Config $config
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Config $config,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->config = $config;
    }

    /**
     * @return string
     */
    public function getBlockTitle()
    {
        return (string)$this->config->getConfig(self::XML_PATH_TO_ADDITIONAL_BLOCK_TITLE);
    }

    /**
     * @return mixed
     */
    protected function getAdditionalLinks()
    {
        return $this->config->getConfig(self::XML_PATH_TO_ADDITIONAL_LINKS);
    }

    /**
     * @return array
     */
    public function getItems()
    {
        $k = 'items';
        if (null === $this->getData($k)) {
            $linksStr = $this->getAdditionalLinks();
            $links = preg_split('/\r+/', $linksStr);
            $items = [];

            $i = 0;
            if (!empty($links)) {
                foreach ($links as $link) {
                    if ($pageSize = $this->getPageSize()) {
                        if ($i >= $pageSize) {
                            break;
                        }
                    }
                    if (trim($link) != '') {
                        $linkElements = explode('|', $link);

                        if ($linkElements[0] == '/') {
                            $linkElements[0] = '';
                        }

                        if (!in_array(trim($linkElements[0]), $this->config->getIgnoredLinks(null, false))) {
                            $items[] = ['url' => $linkElements[0], 'title' => trim($linkElements[1], '()')];
                            $i++;
                        }
                    }
                }
            }
            $this->setData($k, $items);
        }
        return $this->getData($k);
    }

    /**
     * @return int
     */
    protected function getPageSize(): int
    {
        return 0;
    }
}
