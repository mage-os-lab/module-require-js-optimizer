<?php

namespace MageOS\RequireJsOptimizer\Service;

use Magento\Framework\Module\Dir\Reader;
use Magento\Framework\Filesystem\Io\File;
use MageOS\RequireJsOptimizer\Helper\Config as ConfigHelper;

class RequireJsConfigReplacer {

    public const REQUIREJS_FRONTEND_PATHS = [
        "view/frontend/requirejs-config.js",
        "view/base/requirejs-config.js"
    ];

    /**
     * @var Reader
     */
    protected Reader $moduleReader;

    /**
     * @var array
     */
    protected array $moduleIgnoreList;

    /**
     * @var array
     */
    protected array $map;

    /**
     * @var array
     */
    protected array $shim;

    /**
     * @var array
     */
    protected array $jsMappings;

    /**
     * @var File
     */
    protected File $fileIo;

    /**
     * @var ConfigHelper
     */
    protected ConfigHelper $configHelper;

    /**
     * @var array
     */
    protected array $mapRemovedReferences;

    /**
     * @param Reader $moduleReader
     * @param File $fileIo
     * @param ConfigHelper $configHelper
     * @param array $moduleIgnoreList
     * @param array $map
     * @param array $shim
     * @param array $jsMappings
     */
    public function __construct(
        Reader $moduleReader,
        File $fileIo,
        ConfigHelper $configHelper,
        array $moduleIgnoreList = [],
        array $map = [],
        array $shim = [],
        array $jsMappings = []
    ) {
        $this->moduleReader = $moduleReader;
        $this->fileIo = $fileIo;
        $this->configHelper = $configHelper;
        $this->moduleIgnoreList = $moduleIgnoreList;
        $this->map = $map;
        $this->shim = $shim;
        $this->mapRemovedReferences = [];
        $this->jsMappings = $jsMappings;
    }

    /**
     * @param string $filePath
     * @return void
     */
    public function generateOptimizedFile(string $filePath) {

        if ($this->configHelper->optimizeRequireJsConfig()) {

            $configContent = $this->fileIo->read($filePath);

            $configContent = $this->replaceMap($configContent, $this->map);

            $configContent = $this->replaceShim($configContent, $this->shim);

            $configContent = $this->replaceIgnoredModules($configContent);

            $configContent = $this->performJsMappings($configContent);

            $patternEmptyMap = '/map\s*:\s*{\s*\'\*\'\s*:\s*{\s*}\s*},?/s';
            $configContent = preg_replace($patternEmptyMap, '', $configContent);

            $patternEmptyShim = '/shim\s*:\s*{\s*}\s*,?/s';
            $configContent = preg_replace($patternEmptyShim, '', $configContent);

            $configContent = preg_replace("/\n{2,}/", "\n", $configContent);

            $this->fileIo->write($filePath, $configContent, 0664);
        }
    }

    /**
     * @param $configContent
     * @param array $map
     * @return array|mixed|string|string[]|null
     */
    public function replaceMap($configContent, array $map = []) {
        foreach ($map as $key => $value) {
            $pattern = '/^[ \t]*["\']?' . preg_quote($key, '/') . '["\']?\s*:\s*["\']' . preg_quote($value, '/') . '["\']\s*(,)?\s*$/m';
            $configContent = preg_replace($pattern, '', $configContent);
            $this->mapRemovedReferences[$key] = $value;
        }
        return $configContent;
    }

    /**
     * @param $configContent
     * @param array $shim
     * @return array|mixed|string|string[]|null
     */
    public function replaceShim($configContent, array $shim = []) {
        foreach ($shim as $key) {
            $pattern = '/^[ \t]*["\']?' . preg_quote($key, '/') . '["\']?\s*:\s*(\[[^\]]*\]|\{[^}]*\}|["\'][^"\']*["\'])\s*(,)?\s*$/m';
            $configContent = preg_replace($pattern, '', $configContent);
        }
        return $configContent;
    }

    /**
     * @param $configContent
     * @return array|mixed|string|string[]|null
     */
    public function replaceIgnoredModules($configContent) {

        $collectedConfigs = [];
        foreach ($this->moduleIgnoreList as $moduleName) {
            try {
                // Module absolute path (works for vendor/ and app/code/)
                $moduleDir = $this->moduleReader->getModuleDir('', $moduleName);

                foreach (self::REQUIREJS_FRONTEND_PATHS as $configPath) {
                    $fullPath = $moduleDir . '/' . $configPath;

                    if (file_exists($fullPath)) {
                        $content = file_get_contents($fullPath);
                        $collectedConfigs[$moduleName][$configPath] = $content;
                    }
                }
            } catch (\Exception $e) {
                echo "Module not found: $moduleName\n";
            }
        }

        foreach ($collectedConfigs as $module => $files) {
            foreach ($files as $path => $content) {
                $map = [];
                preg_match('/map\s*:\s*(\{[\s\S]*?\})\s*\}/', $content, $mapMatch);

                if (isset($mapMatch[1])) {
                    preg_match_all(
                        '/([a-zA-Z0-9_-]+|[\'"][^\'"]+[\'"])\s*:\s*[\'"]([^\'"]+)[\'"]/',
                        $mapMatch[1],
                        $mapMatches,
                        PREG_SET_ORDER
                    );
                    foreach ($mapMatches as $mapMatch) {
                        $key = trim($mapMatch[1]);
                        $value = trim($mapMatch[2]);
                        $map[$key] = $value;
                    }

                    $configContent = $this->replaceMap($configContent, $map);
                }

                preg_match('/shim\s*:\s*{(.*?)}\s*}/s', $content, $shimMatch);
                if (isset($shimMatch[1])) {
                    preg_match_all(
                        '/[\'"]([^\'"]+)[\'"]\s*:/',
                        $shimMatch[1],
                        $shimMatches
                    );
                    $shim = $shimMatches[1];
                    $configContent = $this->replaceShim($configContent, $shim);
                }
            }
        }
        return $configContent;
    }

    /**
     * @param $configContent
     * @return array|mixed|string|string[]|null
     */
    public function performJsMappings($configContent) {
        if (!empty($this->jsMappings)) {
            $newConfig = "require.config({\n    map: {\n        '*': {\n";
            foreach ($this->jsMappings as $k => $v) {
                $newConfig .= "            '$k': '$v',\n";
            }
            $newConfig = rtrim($newConfig, ",\n") . "\n";
            $newConfig .= "        }\n    }\n});\n\n";
            $configContent = $newConfig . $configContent;
        }
        if (!empty($this->mapRemovedReferences)) {
            $newConfig = "require.config({\n    paths: {\n     ";
            foreach ($this->mapRemovedReferences as $k => $v) {
                $k = str_replace("'", "", $k);
                $newConfig .= "            '$k': '$v',\n";
            }
            $newConfig = rtrim($newConfig, ",\n") . "\n";
            $newConfig .= "    }\n});\n\n";
            $configContent = $configContent . $newConfig;
        }
        return $configContent;
    }
}
