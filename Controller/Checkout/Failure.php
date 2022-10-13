<?php

namespace Astrio\Robokassa\Controller\Checkout;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;

class Failure extends \Astrio\Robokassa\Controller\AbstractController
{

    /**
     * Robokassa pay failure.
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
//                $requestContent = [
//                    'OutSum' => 37,
//                    'InvId' => 6,
//                    'Culture' => 'ru'
//                ];
//            }

//            $order = $this->orderRepository->get($requestData['InvId']);
//
//            $quote = $this->quoteRepository->get($order->getQuoteId());
//            $quote->setIsActive(1)->setReservedOrderId(null);
//            $this->quoteRepository->save($quote);
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
        $result->setPath('checkout/onepage/failure');
        return $result;
    }
}
