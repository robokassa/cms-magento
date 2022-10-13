define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'astrio_robokassa',
                component: 'Astrio_Robokassa/js/view/payment/method-renderer/robokassa-method'
            },
        );
        return Component.extend({});
    }
);