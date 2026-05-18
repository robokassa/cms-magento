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
                $orderItem->getRowTotalInclTax(),
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
                $shippingPrice,
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
     * @param float $amount
     * @param int $quantity
     * @param string $tax
     * @param string|null $paymentMethod
     * @param string|null $paymentObject
     * @return array
     */
    protected function getReceiptItem($name, $amount, $quantity, $tax, $paymentMethod, $paymentObject)
    {
        $receiptItem = [
            'name'     => mb_substr(trim(htmlspecialchars($name)), 0, 127, 'UTF-8'),
            'amount'   => round($amount, 2),
            'quantity' => $quantity,
            'tax'      => $tax
        ];

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
        $itemsAmount = array_sum(array_column($receiptItems, 'amount'));

        $discount = $orderAmount - $itemsAmount;
        if ($discount != 0) {
            $remaindertDiscount = $discount;
            foreach ($receiptItems as $index => &$receiptItem) {
                if ($index < count($receiptItems) - 1) {
                    $percentInOrder = $receiptItem['amount'] / $itemsAmount * 100;
                    $correctAmount  = round($percentInOrder * $discount / 100, 2);
                } else {
                    $correctAmount = $remaindertDiscount;
                }

                $receiptItem['amount'] += $correctAmount;

                $remaindertDiscount -= $correctAmount;
            }
            unset($receiptItem);
        }

        return $this->convertReceiptItemsToCosts($receiptItems, $orderAmount);
    }

    protected function convertReceiptItemsToCosts($receiptItems, $orderAmount)
    {
        $calculatedAmount = 0;
        foreach ($receiptItems as &$receiptItem) {
            $quantity = max(1, (int) $receiptItem['quantity']);
            $amount = (int) round($receiptItem['amount'] * 100);
            $cost = (int) round($amount / $quantity);

            $receiptItem['cost'] = round($cost / 100, 2);
            $calculatedAmount += $cost * $quantity;
            unset($receiptItem['amount']);
        }
        unset($receiptItem);

        $diff = (int) round($orderAmount * 100) - $calculatedAmount;
        foreach ($receiptItems as &$receiptItem) {
            $quantity = max(1, (int) $receiptItem['quantity']);
            if ($diff != 0 && $diff % $quantity == 0) {
                $receiptItem['cost'] = round($receiptItem['cost'] + ($diff / $quantity / 100), 2);
                $diff = 0;
                break;
            }
        }
        unset($receiptItem);

        if ($diff != 0) {
            $receiptItems = $this->splitReceiptItemForRoundingDiff($receiptItems, $diff);
        }

        return $receiptItems;
    }

    protected function splitReceiptItemForRoundingDiff($receiptItems, $diff)
    {
        foreach ($receiptItems as $index => $receiptItem) {
            $quantity = max(1, (int) $receiptItem['quantity']);
            if ($quantity <= 1) {
                continue;
            }

            $splitItem = $receiptItem;
            $receiptItems[$index]['quantity'] = $quantity - 1;
            $splitItem['quantity'] = 1;
            $splitItem['cost'] = round($splitItem['cost'] + ($diff / 100), 2);
            array_splice($receiptItems, $index + 1, 0, [$splitItem]);
            break;
        }

        return $receiptItems;
    }
}