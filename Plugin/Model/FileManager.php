<?php

namespace MageOS\RequireJsOptimizer\Plugin\Model;

use MageOS\RequireJsOptimizer\Service\RequireJsConfigReplacer;

class FileManager {

    /**
     * @var RequireJsConfigReplacer
     */
    protected RequireJsConfigReplacer $requireJsConfigReplacer;

    /**
     * @param RequireJsConfigReplacer $requireJsConfigReplacer
     */
    public function __construct(
        RequireJsConfigReplacer $requireJsConfigReplacer
    ) {
        $this->requireJsConfigReplacer = $requireJsConfigReplacer;
    }

    public function afterCreateRequireJsConfigAsset(
        \Magento\RequireJs\Model\FileManager $subject,
        \Magento\Framework\View\Asset\File $result
    ) {
        $this->requireJsConfigReplacer->generateOptimizedFile($result->getSourceFile());
        return $result;
    }

}
