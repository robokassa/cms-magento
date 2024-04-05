<?php

namespace Astrio\Robokassa\Controller\Checkout;

use Magento\Framework\Controller\ResultFactory;

// require __DIR__ .'/../../vendor/php-jws/autoload.php';

class Hold extends \Astrio\Robokassa\Controller\AbstractController
{
    /**
     * Robokassa pay success.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {               
            /** @var \Magento\Framework\App\Request\Http $request */
            $request = $this->getRequest();

            if (!$request->isPost()) {
                throw new \Exception('Invalid request type');
            }

            $jwsString = file_get_contents('php://input');
            $jswKey = __DIR__ .'/../../result_sign_cert.cer';
                
            $jws = new \Gamegos\JWS\JWS();
            // $r = $jws->verify($jwsString, $jswKey);

            $request = $jws->decode($jwsString);
            if (empty($request) || empty($request['payload'])) {
                throw new \Exception('Invalid request data');
            }

            $requestData = $request['payload']['data'];
            if (empty($requestData['invId'])) {
                throw new \Exception('Invalid request data');
            }

            $order = $this->orderRepository->get($requestData['invId']);
            if (!$order) {
                throw new \Exception('Invalid request data');
            }

            if ($requestData['state'] == 'OK') {
                $payment = $order->getPayment();

                $payment->setTransactionId($requestData['opKey']);
                $order->save();

                // $payment->setPreparedMessage('Payment IPN Status: '. $orderData->getStatus());
                // $payment->setTransactionId()
                // $payment->setParentTransactionId()
                $payment->setIsTransactionClosed(0);
                $payment->registerAuthorizationNotification($requestData['incSum']);
                                
                $order->save();
            }

            /** @var \Magento\Framework\Controller\Result\Redirect $result */
            $result = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $result->setPath('checkout/onepage/success');
            
            return $result;
            
        } catch (\Exception $e) {
            $this->logger->debug(__($e->getMessage()));

            $resultForward = $this->resultForwardFactory->create();

            return $resultForward->forward('noroute');
        }
    }
}
