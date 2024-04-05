define(
    [
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/action/redirect-on-success',
        'mage/url'
    ],
    function (Component, redirectOnSuccessAction, url) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Astrio_Robokassa/payment/robokassa',
                redirectAfterPlaceOrder: false
            },
            afterPlaceOrder: function (data, event) {
                redirectOnSuccessAction.redirectUrl = url.build('robokassa/onepage/success');
                this.redirectAfterPlaceOrder = true

                // window.location.replace(url.build('robokassa/onepage/success'));
            }
        });
    }
);