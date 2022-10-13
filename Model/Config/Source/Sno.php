<?php

namespace Astrio\Robokassa\Model\Config\Source;

class Sno implements \Magento\Framework\Data\OptionSourceInterface
{
    protected $sno = [
        '0' => "Don't use option",
        'osn' => 'Total CH',
        'usn_income' => 'Simplified CH (income)',
        'usn_income_outcome' => 'Simplified ST (income minus expenses)',
        'esn' => 'Single agricultural tax',
        'patent' => 'Patent CH'
    ];

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        foreach ($this->sno as $value => $label) {
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
        foreach ($this->sno as $value => $label) {
            $options[$value] = __($label);
        }
        return $options;
    }
}