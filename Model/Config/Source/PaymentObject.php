<?php

namespace Astrio\Robokassa\Model\Config\Source;

class PaymentObject implements \Magento\Framework\Data\OptionSourceInterface
{
    protected $paymentObjects = [
        '0' => "Don't use option",
        'commodity' => 'Commodity',
        'excise' => 'Excise',
        'job' => 'Job',
        'service' => 'Service',
        'gambling_bet' => 'Gambling bet',
        'gambling_prize' => 'Gambling prize',
        'lottery' => 'Lottery',
        'lottery_prize' => 'Lottery Prize',
        'intellectual_activity' => 'Intellectual Activity',
        'payment' => 'Payment',
        'agent_commission' => 'Agent Commission',
        'composite' => 'Composite',
        'resort_fee' => 'Resort Fee',
        'another' => 'Another',
        'property_right' => 'Property Right',
        'non-operating_gain' => 'Non-operating Gain',
        'insurance_premium' => 'Insurance Premium',
        'sales_tax' => 'Sales Tax'
    ];

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        foreach ($this->paymentObjects as $value => $label) {
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
        foreach ($this->paymentObjects as $value => $label) {
            $options[$value] = __($label);
        }
        return $options;
    }
}