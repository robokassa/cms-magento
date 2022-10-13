<?php

namespace Astrio\Robokassa\Model\Config\Source;

class Tax implements \Magento\Framework\Data\OptionSourceInterface
{

    protected $tax = [
        'none' => 'Without VAT',
        'vat0' => 'VAT at 0%',
        'vat10' => 'VAT check at a rate of 10%',
        'vat110' => 'VAT check at the estimated rate 10/110',
        'vat20' => 'VAT check at a rate of 20%',
        'vat120' => 'VAT check at the estimated rate of 20/120',
    ];

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        foreach ($this->tax as $value => $label) {
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
        foreach ($this->tax as $value => $label) {
            $options[$value] = __($label);
        }
        return $options;
    }
}