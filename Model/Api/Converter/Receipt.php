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
    public function getReceipt(\Magento\Sales\Model\Order $order, $forCapture = false)
    {
        $receipt = [];

        $sno = $this->robokassaConfig->getFiscalizationSno($order->getStoreId());

        if ($sno) {
            $receipt['sno'] = $sno;
        }

        $receipt['items'] = $this->getReceiptItems($order, false, $forCapture);

        $receipt = json_encode($receipt);
        $receipt = urlencode($receipt);
        return $receipt;
    }

    public function getReceiptItems(\Magento\Sales\Model\Order $order, $isSecondCheck = false, $forCapture = false)
    {
        $receiptItems = [];

        $paymentMethod = $isSecondCheck 
            ? 'full_payment' 
            : $this->robokassaConfig->getFiscalizationPaymentMethod($order->getStoreId());

        $paymentObject = $this->robokassaConfig->getFiscalizationPaymentObject($order->getStoreId());
        $tax           = $this->robokassaConfig->getFiscalizationTax($order->getStoreId());

        foreach ($order->getAllVisibleItems() as $orderItem) {
            /** @var \Magento\Sales\Model\Order\Item $orderItem */

            $qty = $forCapture
                ? $orderItem->getQtyInvoiced()
                : $orderItem->getQtyOrdered()
            ;

            if ($qty <= 0 || $orderItem->getRowTotalInclTax() <= 0) continue;

            $receiptItems[] = $this->getReceiptItem(
                $orderItem->getName(),
                // $orderItem->getBaseRowTotalInclTax(),
                ['sum' => $orderItem->getRowTotalInclTax(), 'cost' => $orderItem->getPriceInclTax()],
                (int) $qty,
                $tax,
                $paymentMethod,
                $paymentObject
            );
        }

        // $shippingPrice = $order->getBaseShippingInclTax();
        $shippingPrice = $order->getShippingInclTax();

        if ($shippingPrice && $shippingPrice > 0) {
            $receiptItems[] = $this->getReceiptItem(
                $order->getShippingDescription(),
                ['sum' => $shippingPrice, 'cost' => $shippingPrice],
                1,
                $tax,
                $paymentMethod,
                $paymentObject
            );
        }

        $receiptItems = $this->correctingReceiptAmounts($order, $receiptItems);

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
    protected function getReceiptItem($name, $amounts, $quantity, $tax, $paymentMethod, $paymentObject)
    {
        if (is_array($amounts)) {
            $sum  = $amounts['sum'];
            $cost = $amounts['cost'];
        } else {
            $sum  = $amounts;
            $cost = null;
        }

        $receiptItem = [
            'name'     => mb_substr(trim(htmlspecialchars($name)), 0, 127, 'UTF-8'),
            'sum'      => round($sum, 2),
            'quantity' => $quantity,
            'tax'      => $tax
        ];

        if (!is_null($cost)) {
            $receiptItem['cost'] = round($cost, 2);
        }

        if ($paymentMethod) {
            $receiptItem['payment_method'] = $paymentMethod;
        }

        if ($paymentObject) {
            $receiptItem['payment_object'] = $paymentObject;
        }

        return $receiptItem;
    }

    protected function correctingReceiptAmounts($order, $receiptItems)
    {
        $orderAmount = $order->getGrandTotal();
        $itemsAmount = array_sum(array_column($receiptItems, 'sum'));

        $discount = $orderAmount - $itemsAmount;
        if ($discount == 0) {
            return $receiptItems;
        }

        $remaindertDiscount = $discount;
        foreach ($receiptItems as $index => &$receiptItem) {
            if ($index < count($receiptItems) - 1) {
                $percentInOrder = $receiptItem['sum'] / $itemsAmount * 100;
                $correctAmount  = round($percentInOrder * $discount / 100, 2);
            } else {
                $correctAmount = $remaindertDiscount;
            }

            $receiptItem['sum'] += $correctAmount;

            if (isset($receiptItem['cost'])) {
                $receiptItem['cost'] = round($receiptItem['sum'] / $receiptItem['quantity'], 2);
            }

            $remaindertDiscount -= $correctAmount;
        }

        return $receiptItems;
    }
}