<?php

namespace Astrio\Robokassa\Controller\Checkout;

use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;

class Result extends \Astrio\Robokassa\Controller\AbstractController
{
    /**
     * Robokassa result.
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

        /** @var \Magento\Framework\Controller\Result\Raw $result */
        $result = $this->resultFactory->create(ResultFactory::TYPE_RAW);

        $requestData = $request->getParams();

        try {
//            if (!$this->robokassaConfig->getMode()) {
//                $requestContent = '{
//                    "OutSum": "2.000000",
//                    "InvId": "78",
//                    "Fee": "0",
//                    "EMail": "test@mail.ru",
//                    "SignatureValue": "C16AEB33AA6DC49056F73F65986B14D7",
//                    "PaymentMethod": "test",
//                    "IncCurrLabel": "BANKOCEAN2R",
//                    "Shp_": ""
//                }';
//                $requestData = json_decode($requestContent, true);
//            }

            $order = $this->orderRepository->get($requestData['InvId']);

            $password2 = $this->robokassaConfig->getPassword2();
            $grandTotal = round($order->getGrandTotal(), 2);
            $invId = $order->getId();
            $signatureValue = implode(':', [sprintf("%.6f", $grandTotal), $invId, $password2]);
            $signatureValue = md5($signatureValue);
            if (strtoupper($requestData['SignatureValue']) != strtoupper($signatureValue)) {
                throw new LocalizedException(__('SignatureValue not match with order id = %1', $order->getId()));
            }

            $result->setContents('OK' . $invId);
        } catch (\Exception $e) {
            $this->logger->error(json_encode($requestData));
            $this->logger->critical($e);
            $result->setContents('bad sign');
        }
        return $result;
    }
}
