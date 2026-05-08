<?php
namespace Codilar\CartRestriction\Plugin;

use Magento\Checkout\Model\Cart;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;

class CartPlugin
{
    protected $customerSession;
    protected $scopeConfig;

    public function __construct(
        Session $customerSession,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->customerSession = $customerSession;
        $this->scopeConfig = $scopeConfig;
    }

    public function beforeAddProduct(Cart $subject, $productInfo, $requestInfo = null)
    {
        if (!$this->scopeConfig->getValue('codilar_cart/restriction/enabled')) {
            return [$productInfo, $requestInfo];
        }

        $product = $productInfo;
        if (is_numeric($productInfo)) {
            $product = $subject->getProduct($productInfo);
        }

        $priceThreshold = $this->scopeConfig->getValue('codilar_cart/restriction/price_threshold');
        $productPrice = $product->getFinalPrice();
        
        if ($productPrice < $priceThreshold) {
            $difference = $priceThreshold - $productPrice;
            $errorMessage = $this->scopeConfig->getValue('codilar_cart/restriction/error_message');
            
            if (empty($errorMessage)) {
                $errorMessage = 'Please add {{difference}} more to reach minimum order value of {{minimum}}.';
            }
            
            $message = str_replace(
                ['{{difference}}', '{{minimum}}'],
                [number_format($difference, 2), number_format($priceThreshold, 2)],
                $errorMessage
            );
            
            if (!$this->customerSession->isLoggedIn()) {
                throw new LocalizedException(__($message));
            }

            $customerGroupId = $this->customerSession->getCustomerGroupId();
            if ($customerGroupId == 1) { // General customer group
                throw new LocalizedException(__($message));
            }
        }

        return [$productInfo, $requestInfo];
    }
}