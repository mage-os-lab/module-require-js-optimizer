<?php

namespace MageOS\RequireJsOptimizer\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\State;
use Magento\Framework\App\RequestInterface;

class Config extends AbstractHelper {

    /**
     * @var RequestInterface
     */
    protected RequestInterface $request;

    /**
     * @var State
     */
    protected State $state;

    /**
     * @param Context $context
     * @param RequestInterface $request
     * @param State $state
     */
    public function __construct(
        Context $context,
        RequestInterface $request,
        State $state
    ) {
        $this->request = $request;
        $this->state = $state;
        return parent::__construct($context);
    }

    public function optimizeRequireJsConfig() {
        try {
            $areaCode = $this->state->getAreaCode();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $areaCode = 'cli';
        }

        if (
            $areaCode === 'cli' ||
            $areaCode === 'frontend' &&
            ($this->request->getModuleName() === "cms" || $this->request->getModuleName() === "catalog")
        ) {
            return true;
        }
        return false;
    }

}
