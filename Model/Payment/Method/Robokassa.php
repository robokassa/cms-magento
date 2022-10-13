<?php

namespace Astrio\Robokassa\Model\Payment\Method;

use Magento\Sales\Model\Order;

class Robokassa extends \Magento\Payment\Model\Method\AbstractMethod
{

    const PAYMENT_METHOD_CODE = 'astrio_robokassa';
    const AVAILABLE_OUT_SUM_CURRENCIES = ['USD', 'EUR', 'KZT'];

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_isOffline = false;

    /**
     * @var bool
     */
    protected $_canCapture = true;

    /**
     * @var bool
     */
    protected $_canRefund = true;

    /**
     * @var bool
     */
    protected $_isInitializeNeeded = true;

    /**
     * Payment method code
     *
     * @var string
     */
    protected $_code = self::PAYMENT_METHOD_CODE;

    /* @inheridoc */
    public function getConfigPaymentAction()
    {
        return 'custom';
    }

    /* @inheridoc */
    public function initialize($paymentAction, $stateObject)
    {
        $stateObject->setState(Order::STATE_PENDING_PAYMENT);
        $stateObject->setStatus(Order::STATE_PENDING_PAYMENT);
        return $this;
    }
}