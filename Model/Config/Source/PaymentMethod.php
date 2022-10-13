<?php

namespace Astrio\Robokassa\Model\Config\Source;

class PaymentMethod implements \Magento\Framework\Data\OptionSourceInterface
{
    protected $paymentMethods = [
        '0' => "Don't use option",
        'full_prepayment' => 'Prepayment 100%',
        'prepayment' => 'Prepayment',
        'advance' => 'Advance',
        'full_payment' => 'Full payment',
        'partial_payment' => 'Partial payment and credit',
        'credit' => 'Transfer on credit',
        'credit_payment' => 'Credit payment'
    ];

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        foreach ($this->paymentMethods as $value => $label) {
            $options[] = [
                'value' => $value,
                'label' => __($label)
            ];
        }
        return $options;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $options = [];
        foreach ($this->paymentMethods as $value => $label) {
            $options[$value] = __($label);
        }
        return $options;
    }
}