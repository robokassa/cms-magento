<?php

namespace Astrio\Robokassa\Model\Api\Converter;

class Receipt
{

    /**
     * @var \Astrio\Robokassa\Helper\Config
     */
    protected $robokassaConfig;

    public function __construct(\Astrio\Robokassa\Helper\Config $robokassaConfig)
    {
        $this->robokassaConfig = $robokassaConfig;
    }

    /**
     * Get receipt.
     *
     * @param \Magento\Sales\Model\Order $order
     * @return string
     */
    public function getReceipt(\Magento\Sales\Model\Order $order)
    {
        $receipt = [];

        $sno = $this->robokassaConfig->getFiscalizationSno($order->getStoreId());

        if ($sno) {
            $receipt['sno'] = $sno;
        }

        $receipt['items'] = $this->getReceiptItems($order);

        $receipt = json_encode($receipt);
        $receipt = urlencode($receipt);
        return $receipt;
    }

    public function getReceiptItems(\Magento\Sales\Model\Order $order, $isSecondCheck = false)
    {
        $receiptItems = [];

        $paymentMethod = $isSecondCheck ?
            'full_payment' : $this->robokassaConfig->getFiscalizationPaymentMethod($order->getStoreId());
        $paymentObject = $this->robokassaConfig->getFiscalizationPaymentObject($order->getStoreId());
        $tax = $this->robokassaConfig->getFiscalizationTax($order->getStoreId());

        foreach ($order->getAllVisibleItems() as $orderItem) {
            /** @var \Magento\Sales\Model\Order\Item $orderItem */
            $receiptItems[] = $this->getReceiptItem(
                $orderItem->getName(),
                $orderItem->getBaseRowTotalInclTax(),
                (int)$orderItem->getQtyOrdered(),
                $tax,
                $paymentMethod,
                $paymentObject
            );
        }

        $shippingPrice = $order->getBaseShippingInclTax();
        if ($shippingPrice && $shippingPrice > 0) {
            $receiptItems[] = $this->getReceiptItem(
                $order->getShippingDescription(),
                $shippingPrice,
                1,
                $tax,
                $paymentMethod,
                $paymentObject
            );
        }
        return $receiptItems;
    }

    /**
     * Get receipt item.
     *
     * @param string $name
     * @param float $sum
     * @param int $quantity
     * @param string $tax
     * @param string|null $paymentMethod
     * @param string|null $paymentObject
     * @return array
     */
    protected function getReceiptItem($name, $sum, $quantity, $tax, $paymentMethod, $paymentObject)
    {
        $receiptItem = [
            'name' => mb_substr(trim(htmlspecialchars($name)), 0, 127, 'UTF-8'),
            'sum' => round($sum, 2),
            'quantity' => $quantity,
            'tax' => $tax
        ];
        if ($paymentMethod) {
            $receiptItem['payment_method'] = $paymentMethod;
        }
        if ($paymentObject) {
            $receiptItem['payment_object'] = $paymentObject;
        }
        return $receiptItem;
    }
}