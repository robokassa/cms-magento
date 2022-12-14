<?php

namespace Astrio\Robokassa\Block\Widget;

class Redirect extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Sales\Model\Order\Config
     */
    protected $orderConfig;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;


    /**
     * @var \Astrio\Robokassa\Helper\Config
     */
    protected $robokassaConfig;

    /**
     * @var \Astrio\Robokassa\Model\Api\Converter\Receipt
     */
    protected $receiptConverter;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Astrio\Robokassa\Helper\Config $robokassaConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Sales\Model\Order\Config $orderConfig
     * @param \Astrio\Robokassa\Model\Api\Converter\Receipt $receiptConverter
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Astrio\Robokassa\Helper\Config $robokassaConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Astrio\Robokassa\Model\Api\Converter\Receipt $receiptConverter,
        \Magento\Framework\App\Http\Context $httpContext,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->robokassaConfig = $robokassaConfig;
        $this->checkoutSession = $checkoutSession;
        $this->orderConfig = $orderConfig;
        $this->receiptConverter = $receiptConverter;
        $this->_isScopePrivate = true;
        $this->httpContext = $httpContext;
    }


    /**
     * Get post data.
     *
     * @return array
     */
    public function getPostData()
    {
        $order = $this->checkoutSession->getLastRealOrder();
        $merchantLogin = $this->robokassaConfig->getMerchantLogin();
        $password1 = $this->robokassaConfig->getPassword1();
        $grandTotal = round($order->getBaseGrandTotal(), 2);
        $invId = $order->getId();
        $description = $this->robokassaConfig->getDescription($order->getStoreId()) ?? '';

        $data = [
            'MerchantLogin' => $merchantLogin,
            'OutSum' => $grandTotal,
            'InvId' => $invId,
            'Description' => $description,
            'Culture' => $this->robokassaConfig->getCulture($order->getStoreId())
//            'Shp_item' => '',
//            'IncCurrLabel' => 'BANKOCEAN2R',

        ];

        if ($order->getCustomerEmail()) {
            $data['Email'] = $order->getCustomerEmail();
        }

        $signatureArray = [$merchantLogin, $grandTotal, $invId];

        if (false && //???????????????? ???????????? ?? ??????????????.
            // ?? ?????????????? result ?????????????????????? ???????????????? ?????? ????????????????????????????????
            // ????-???? ???????? ?????????????????? ???????????????? ?? ?????????????????? ?????????? ???????????? ????-???? ?????????????? ?? ?????????????????????? ?????????????? ?? ??????????????????.
            in_array($outSumCurrency, \Astrio\Robokassa\Model\Payment\Method\Robokassa::AVAILABLE_OUT_SUM_CURRENCIES)) {
            $outSumCurrency = $order->getOrderCurrencyCode();
            $signatureArray[] = $outSumCurrency;
            $data['OutSumCurrency'] = $outSumCurrency;
        }

        if ($this->robokassaConfig->isFiscalizationEnabled()) {
            $receipt = $this->receiptConverter->getReceipt($order);
            $data['Receipt'] = urlencode($receipt);
            $signatureArray[] = $receipt;
        }

        $signatureArray[] = $password1;
        $data['SignatureValue'] = md5(implode(':', $signatureArray));

        if (!$this->robokassaConfig->getMode()) {
            $data['IsTest'] = 1;
        }
        return $data;
    }

    /**
     * @return string
     */
    public function getRobokassaApiUrl()
    {
        return \Astrio\Robokassa\Helper\Config::ROBOKASSA_API_URL;
    }
}
