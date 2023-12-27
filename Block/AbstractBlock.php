<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 *
 * Glory to Ukraine! Glory to the heroes!
 */

declare(strict_types=1);

namespace Magefan\HtmlSitemap\Block;

use Magento\Framework\View\Element\Template;
use Magefan\HtmlSitemap\Model\Config;

abstract class AbstractBlock extends Template
{

    /**
     * @var Config
     */
    protected $config;

    /**
     * AbstractBlock constructor.
     * @param Template\Context $context
     * @param Config $config
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Config $config,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->config = $config;
    }

    /**
     * @var string
     */
    protected $type = '';

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    abstract protected function getCollection();

    /**
     * @return array|mixed|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    abstract public function getItems();

    /**
     * @return bool
     */
    public function showBlockTitle(): bool
    {
        return false;
    }

    /**
     * @return int
     */
    protected function getPageSize(): int
    {
        return 0;
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function showViewMore(): bool
    {
        if ($this->config->getBlockViewMore($this->type)
            && $this->getPageSize() && count($this->getCollection()) >= $this->getPageSize()
        ) {
            return true;
        }
        return false;
    }

    /**
     * @return string
     */
    abstract public function getCurrentTypeHtmlSitemapUrl();

    /**
     * @return string
     */
    public function getBlockTitle(): string
    {
        return (string)$this->config->getBlockTitle($this->type);
    }
}