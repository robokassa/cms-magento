<?php

namespace Astrio\Robokassa\Model\Api;

class Robokassa
{

    const API_ROBOXCHANGE_URL = 'https://ws.roboxchange.com/';

    /**
     * @var \Astrio\Robokassa\Model\Api\Converter\Receipt
     */
    protected $receiptConverter;

    /**
     * @var \Astrio\Robokassa\Helper\Config
     */
    protected $robokassaConfig;

    /**
     * @var \Magento\Framework\HTTP\ClientFactory
     */
    protected $curlFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    public function __construct(
        \Magento\Framework\HTTP\ClientFactory $curlFactory,
        \Astrio\Robokassa\Model\Api\Converter\Receipt $receiptConverter,
        \Astrio\Robokassa\Helper\Config $robokassaConfig,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->curlFactory = $curlFactory;
        $this->receiptConverter = $receiptConverter;
        $this->robokassaConfig = $robokassaConfig;
        $this->logger = $logger;
    }

    /**
     * Send second check.
     *
     * @param \Magento\Sales\Model\Order $order
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function sendSecondCheck(\Magento\Sales\Model\Order $order)
    {
        /** @var array $fields */
        $fields = [
            'merchantId' => $this->robokassaConfig->getMerchantLogin(),
            'id' => $order->getId() + 1,
            'originId' => $order->getId(),
            'operation' => 'sell',
            'url' => urlencode($this->robokassaConfig->getSiteUrl()),
            'total' => round($order->getBaseGrandTotal(), 2),
            'items' => $this->receiptConverter->getReceiptItems($order),
            'payments' => [
                [
                    'type' => 2,
                    'sum' => round($order->getBaseGrandTotal(), 2)
                ]
            ],
            'vats' => [[
                'type' => $this->robokassaConfig->getFiscalizationTax(),
                'sum' => round($order->getBaseTaxAmount(), 2)
            ]]
        ];

        if ($this->robokassaConfig->getFiscalizationSno()) {
            $fields['sno'] = $this->robokassaConfig->getFiscalizationSno();
        }
        if ($order->getCustomerEmail()) {
            $fields['client']['email'] = $order->getCustomerEmail();
        }
        if ($order->getShippingAddress() && $order->getShippingAddress()->getTelephone()) {
            $fields['client']['phone'] = $order->getShippingAddress()->getTelephone();
        }
        $startupHash = $this->formatSignFinish(base64_encode($this->formatSignReplace(json_encode($fields))));

        $sign = $this->formatSignFinish(base64_encode(md5(
            $startupHash . $this->robokassaConfig->getPassword1($order->getStoreId())
        )));

        $url = self::API_ROBOXCHANGE_URL . 'RoboFiscal/Receipt/Attach';
        $curl = $this->curlFactory->create();
        $curl->setOption(CURLOPT_RETURNTRANSFER, 1);
        $curl->setOption(CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($startupHash . '.' . $sign)
        ]);
        $curl->post($url, $startupHash . '.' . $sign);
        $result = $curl->getBody();

        $this->logger->debug(json_encode($fields));
        $this->logger->debug($startupHash . '.' . $sign);
        $this->logger->debug($result);

        return $result;
    }

    /**
     * Подготовка строки после кодирования в base64
     *
     * @param $string
     * @return string|string[]|null
     */
    protected static function formatSignFinish($string)
    {
        return preg_replace('/^(.*?)(=*)$/', '$1', $string);
    }

    /**
     * Подготовка строки перед кодированием в base64
     *
     * @param $string
     * @return string
     */
    protected static function formatSignReplace($string)
    {
        return strtr(
            $string,
            [
                '+' => '-',
                '/' => '_',
            ]
        );
    }
}
