<?php

namespace Astrio\Robokassa\Controller\Checkout;

use Magento\Framework\Controller\ResultFactory;

class Success extends \Astrio\Robokassa\Controller\AbstractController
{
    /**
     * Robokassa pay success.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\App\Request\Http $request */
        $request = $this->getRequest();

        if (!$request->isPost()) {
            $resultForward = $this->resultForwardFactory->create();
            $this->logger->debug(__('Invalid request method %1', $request->getMethod()));
            return $resultForward->forward('noroute');
        }

        /** @var \Magento\Framework\Controller\Result\Redirect $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        try {
            $requestData = $request->getParams();

//            if (!$this->robokassaConfig->getMode()) {
//                $requestContent = '{
//                    "OutSum": 39,
//                    "InvId": 7,
//                    "Fee": "0",
//                    "EMail": "test@mail.ru",
//                    "SignatureValue": "c69aa89418b812054750375475cb374f",
//                    "PaymentMethod": "test",
//                    "IncCurrLabel": "BANKOCEAN2R",
//                    "Shp_": ""
//                }';
//            }

            $order = $this->orderRepository->get($requestData['InvId']);

            /** @var \Magento\Sales\Model\Order\Payment $payment */
            // $payment = $order->getPayment();
            // $payment->setIsTransactionClosed(false);
            // $payment->capture();
            // $this->orderRepository->save($order);

            $quoteId = $order->getQuoteId();
            if ($quoteId) {
                $quote = $this->quoteRepository->get($quoteId);
                $quote->setIsActive(false);
                $this->quoteRepository->save($quote);
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
        $result->setPath('checkout/onepage/success');
        return $result;
    }
}
