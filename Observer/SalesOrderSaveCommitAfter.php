<?php

namespace Astrio\Robokassa\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class SalesOrderSaveCommitAfter implements ObserverInterface
{

    /**
     * @var \Astrio\Robokassa\Helper\Config
     */
    protected $robokassaConfig;

    /**
     * @var \Astrio\Robokassa\Model\Api\Robokassa
     */
    protected $apiRobokassa;

    public function __construct(
        \Astrio\Robokassa\Helper\Config $robokassaConfig,
        \Astrio\Robokassa\Model\Api\Robokassa $apiRobokassa
    ) {
        $this->robokassaConfig = $robokassaConfig;
        $this->apiRobokassa = $apiRobokassa;
    }

    /**
     * Add send sms to queue.
     *
     * @param Observer $observer
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getData('order');
        if (!$this->robokassaConfig->isSecondCheckEnabled($order->getStoreId())) {
            return;
        }
        if ($order->dataHasChangedFor('status') && $order->getStatus() == 'complete') {
            $this->apiRobokassa->sendSecondCheck($order);
        }
    }
}