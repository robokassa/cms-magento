<?php

namespace Astrio\Robokassa\Helper;

use Magento\Framework\App\Helper\Context;

class Config extends \Magento\Framework\App\Helper\AbstractHelper
{
    const ROBOKASSA_API_URL = 'https://auth.robokassa.ru/Merchant/Index.aspx';
    const XML_PATH_ROBOKASSA_ACTIVE = 'payment/astrio_robokassa/active';
    const XML_PATH_ROBOKASSA_DESCRIPTION = 'payment/astrio_robokassa/description';
    const XML_PATH_ROBOKASSA_MODE = 'payment/astrio_robokassa/mode';
    const XML_PATH_ROBOKASSA_PAYMENT_MODE = 'payment/astrio_robokassa/payment_mode';
    const XML_PATH_ROBOKASSA_MERCHANT_LOGIN = 'payment/astrio_robokassa/merchant_login';
    const XML_PATH_ROBOKASSA_PASSWORD_1 = 'payment/astrio_robokassa/password_1';
    const XML_PATH_ROBOKASSA_PASSWORD_2 = 'payment/astrio_robokassa/password_2';
    const XML_PATH_ROBOKASSA_TEST_MERCHANT_LOGIN = 'payment/astrio_robokassa/test_merchant_login';
    const XML_PATH_ROBOKASSA_TEST_PASSWORD_1 = 'payment/astrio_robokassa/test_password_1';
    const XML_PATH_ROBOKASSA_TEST_PASSWORD_2 = 'payment/astrio_robokassa/test_password_2';
    const XML_PATH_ROBOKASSA_CULTURE = 'payment/astrio_robokassa/culture';
    const XML_PATH_ROBOKASSA_FISCALIZATION_ACTIVE = 'payment/astrio_robokassa/fiscalization/active';
    const XML_PATH_ROBOKASSA_FISCALIZATION_SNO = 'payment/astrio_robokassa/fiscalization/sno';
    const XML_PATH_ROBOKASSA_FISCALIZATION_PAYMENT_METHOD = 'payment/astrio_robokassa/fiscalization/payment_method';
    const XML_PATH_ROBOKASSA_FISCALIZATION_PAYMENT_OBJECT = 'payment/astrio_robokassa/fiscalization/payment_object';
    const XML_PATH_ROBOKASSA_FISCALIZATION_TAX = 'payment/astrio_robokassa/fiscalization/tax';
    

    protected $secondCheckPaymentMethods = [
        'full_prepayment',
        'prepayment',
        'advance'
    ];

    /**
     * @var \Magento\Store\Model\StoreManager
     */
    protected $storeManager;

    public function __construct(
        Context $context,
        \Magento\Store\Model\StoreManager $storeManager
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
    }

    /**
     * Is enabled.
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isEnabled($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ROBOKASSA_ACTIVE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get description.
     * @param int|null $storeId
     * @return mixed
     */
    public function getDescription($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ROBOKASSA_DESCRIPTION,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get mode.
     * @param int|null $storeId
     * @return bool
     */
    public function getMode($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ROBOKASSA_MODE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get merchant login.
     * @param int|null $storeId
     * @return mixed
     */
    public function getMerchantLogin($storeId = null)
    {
        if ($this->getMode($storeId)) {
            return $this->scopeConfig->getValue(
                self::XML_PATH_ROBOKASSA_MERCHANT_LOGIN,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            );
        } else {
            return $this->scopeConfig->getValue(
                self::XML_PATH_ROBOKASSA_TEST_MERCHANT_LOGIN,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }
    }

    /**
     * Get password 1.
     * @param int|null $storeId
     * @return mixed
     */
    public function getPassword1($storeId = null)
    {
        if ($this->getMode($storeId)) {
            return $this->scopeConfig->getValue(
                self::XML_PATH_ROBOKASSA_PASSWORD_1,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            );
        } else {
            return $this->scopeConfig->getValue(
                self::XML_PATH_ROBOKASSA_TEST_PASSWORD_1,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }
    }

    /**
     * Get password 2.
     * @param int|null $storeId
     * @return mixed
     */
    public function getPassword2($storeId = null)
    {
        if ($this->getMode($storeId)) {
            return $this->scopeConfig->getValue(
                self::XML_PATH_ROBOKASSA_PASSWORD_2,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            );
        } else {
            return $this->scopeConfig->getValue(
                self::XML_PATH_ROBOKASSA_TEST_PASSWORD_2,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }
    }

    /**
     * Get culture.
     * @param int|null $storeId
     * @return mixed
     */
    public function getCulture($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ROBOKASSA_CULTURE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Is fiscalization enabled.
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isFiscalizationEnabled($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ROBOKASSA_FISCALIZATION_ACTIVE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get sno.
     * @param int|null $storeId
     * @return mixed
     */
    public function getFiscalizationSno($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ROBOKASSA_FISCALIZATION_SNO,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
    /**
     * Get tax.
     * @param int|null $storeId
     * @return mixed
     */
    public function getFiscalizationPaymentMethod($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ROBOKASSA_FISCALIZATION_PAYMENT_METHOD,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
    /**
     * Get tax.
     * @param int|null $storeId
     * @return mixed
     */
    public function getFiscalizationPaymentObject($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ROBOKASSA_FISCALIZATION_PAYMENT_OBJECT,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
    /**
     * Get tax.
     * @param int|null $storeId
     * @return mixed
     */
    public function getFiscalizationTax($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ROBOKASSA_FISCALIZATION_TAX,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get site url.
     * @param int|null $storeId
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getSiteUrl($storeId = null)
    {
        return $this->storeManager->getStore($storeId)->getBaseUrl();
    }

    /**
     * Is second check enabled.
     *
     * @param null $storeId
     * @return bool
     */
    public function isSecondCheckEnabled($storeId = null)
    {
        return $this->isFiscalizationEnabled($storeId) &&
            in_array($this->getFiscalizationPaymentMethod($storeId), $this->secondCheckPaymentMethods);
    }

    public function isStepByStepPayment($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ROBOKASSA_PAYMENT_MODE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        ) == 1;
    }
}
