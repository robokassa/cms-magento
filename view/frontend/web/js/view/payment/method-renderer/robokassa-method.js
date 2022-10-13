define(
    [
        'Magento_Checkout/js/view/payment/default',
        'mage/url'
    ],
    function (Component, url) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Astrio_Robokassa/payment/robokassa',
                redirectAfterPlaceOrder: false
            },
            afterPlaceOrder: function (data, event) {
                window.location.replace(url.build('robokassa/onepage/success'));
            }
        });
    }
);