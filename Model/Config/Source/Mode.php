<?php

namespace Astrio\Robokassa\Model\Config\Source;

class Mode implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [['value' => 1, 'label' => __('Live')], ['value' => 0, 'label' => __('Test')]];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [0 => __('Test'), 1 => __('Live')];
    }
}