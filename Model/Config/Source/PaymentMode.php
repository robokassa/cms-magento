<?php

namespace Astrio\Robokassa\Model\Config\Source;

class PaymentMode implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [['value' => 0, 'label' => __('One step')], ['value' => 1, 'label' => __('Step by step')]];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [0 => __('One step'), 1 => __('Step by step')];
    }
}