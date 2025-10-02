<?php

namespace MageOS\RequireJsOptimizer\Plugin;

use Magento\Framework\View\Asset\ContextInterface;
use Magento\Framework\View\Asset\File\FallbackContext;
use Magento\Framework\View\Asset\Repository;
use MageOS\RequireJsOptimizer\Helper\Config as ConfigHelper;
use Magento\Framework\App\RequestInterface;

class ChangeRequireJsConfig {

    const CONFIG_FILE_NAME = 'requirejs-config-optimized.js';

    /**
     * @var ContextInterface|FallbackContext
     */
    private $staticContext;

    /**
     * @var ConfigHelper
     */
    private ConfigHelper $configHelper;

    /**
     * @param Repository $assetRepo
     * @param ConfigHelper $configHelper
     */
    public function __construct(
        Repository $assetRepo,
        ConfigHelper $configHelper
    ) {
        $this->staticContext = $assetRepo->getStaticViewFileContext();
        $this->configHelper = $configHelper;
    }
    /**
     * Get path to configuration file
     *
     * @return string
     */
    public function afterGetConfigFileRelativePath(
        \Magento\Framework\RequireJs\Config $subject,
        string $result
    )
    {
        if ($this->configHelper->optimizeRequireJsConfig()) {
            $result =  $this->staticContext->getConfigPath() . '/' . self::CONFIG_FILE_NAME;
        }
        return $result;
    }

}
