<?php
declare(strict_types=1);

namespace MageOS\RequireJsOptimizer\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class Config implements ArgumentInterface
{
    private $scopeConfig;

    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    public function debug(): bool
    {
        return (bool) $this->scopeConfig->isSetFlag('dev/require_js_optimizer/debug');
    }
}
