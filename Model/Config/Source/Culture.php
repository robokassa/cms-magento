<?php

namespace Astrio\Robokassa\Model\Config\Source;

class Culture implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [['value' => 'ru', 'label' => __('Russian')], ['value' => 'en', 'label' => __('English')]];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return ['ru' => __('Russian'), 'en' => __('English')];
    }
}