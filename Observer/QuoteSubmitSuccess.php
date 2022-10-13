<?php

namespace Astrio\Robokassa\Observer;

use Magento\Framework\Event\Observer;
use Magento\Quote\Model\Quote;

class QuoteSubmitSuccess implements \Magento\Framework\Event\ObserverInterface
{

    public function execute(Observer $observer)
    {
        /* @var Quote $quote */
        $quote = $observer->getEvent()->getQuote();
        /* @var \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getOrder();
        if ($quote &&
            $order &&
            $order->getPayment() &&
            $order->getPayment()->getMethod() == \Astrio\Robokassa\Model\Payment\Method\Robokassa::PAYMENT_METHOD_CODE
        ) {
            $quote->setIsActive(true);
        }
    }
}