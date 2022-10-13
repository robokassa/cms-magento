<?php

namespace Astrio\Robokassa\Plugin\Magento\Framework\Session;

use Magento\Framework\App\Request\Http;

class SessionStartChecker
{
    /**
     * @var string[]
     */
    private $disableSessionUrls = [
        'robokassa/checkout/result',
        'robokassa/checkout/success',
        'robokassa/checkout/failure'
    ];

    /**
     * @var Http
     */
    private $request;

    /**
     * @param Http $request
     */
    public function __construct(
        Http $request
    ) {
        $this->request = $request;
    }

    /**
     * Prevents session starting while instantiating Robokassa transparent redirect controller.
     *
     * @param \Magento\Framework\Session\SessionStartChecker $subject
     * @param bool $result
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterCheck(\Magento\Framework\Session\SessionStartChecker $subject, bool $result): bool
    {
        if ($result === false) {
            return false;
        }

        foreach ($this->disableSessionUrls as $url) {
            if (strpos((string)$this->request->getPathInfo(), $url) !== false) {
                return false;
            }
        }

        return true;
    }
}