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
     * Availability option
     *
     * @var bool
     */
    protected $_canOrder = true;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canAuthorize = true;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canCapture = true;

    /**
     * Availability option
     *
     * @var bool
     */
    protected $_canRefund = false;

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

    protected $robokassaConfig;
    protected $receiptConverter;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Payment\Model\Method\Logger $logger
     * @param ProFactory $proFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Checkout\Model\CartFactory $cartFactory
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Exception\LocalizedExceptionFactory $exception
     * @param \Magento\Sales\Api\TransactionRepositoryInterface $transactionRepository
     * @param Transaction\BuilderInterface $transactionBuilder
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Astrio\Robokassa\Helper\Config $robokassaConfig,
        \Astrio\Robokassa\Model\Api\Converter\Receipt $receiptConverter,

        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,

        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data
        );
        
        $this->robokassaConfig  = $robokassaConfig;
        $this->receiptConverter = $receiptConverter;
    }

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

    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $order = $payment->getOrder();
        
        $requestData = [
            'MerchantLogin' => $this->robokassaConfig->getMerchantLogin(),
            'OutSum'        => $amount,
            'InvoiceID'     => $order->getId(),
        ];

        if ($this->robokassaConfig->isFiscalizationEnabled()) {
            $receipt                = $this->receiptConverter->getReceipt($order);
            $requestData['Receipt'] = urlencode($receipt);
        }

        $requestData['SignatureValue'] = md5(implode(':', array_merge($requestData, [
            $this->robokassaConfig->getPassword1()
        ])));

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://auth.robokassa.ru/Merchant/Payment/Confirm');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($requestData));

        $response = curl_exec($ch);

        if ($response == '"success: true"') {
            $payment->setIsTransactionApproved(true);
            // $payment->setTransactionId($orderData->getTranActionId());
            // $payment->setParentTransactionId($parentTransId);
            $payment->setPreparedMessage('Payment capture');
            $payment->setShouldCloseParentTransaction(true);
            $payment->setIsTransactionClosed(0);

            if ($order->getState() === \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT) {
                $order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING);
            }

            $payment->registerCaptureNotification($amount, true);
            $order->save();

            $invoice = $payment->getCreatedInvoice();
            if ($invoice && !$order->getEmailSent()) {
                $this->_orderSender->send($order);
                
                $order->addStatusHistoryComment(
                    __('You notified customer about invoice #%1.', $invoice->getIncrementId())
                )
                    ->setIsCustomerNotified(true)
                    ->save();
            }
        } else {
            $payment->setIsTransactionDenied(true);
        }
    }
}