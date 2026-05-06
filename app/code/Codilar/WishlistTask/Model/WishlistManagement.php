<?php
namespace Codilar\WishlistTask\Model;

use Codilar\WishlistTask\Api\WishlistManagementInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Authorization\Model\UserContextInterface;
use Codilar\WishlistTask\Model\Service\AddProductService;
use Codilar\WishlistTask\Model\Service\RemoveProductService;
use Codilar\WishlistTask\Model\Service\GetProductsService;
use Codilar\WishlistTask\Model\Service\MoveToCartService;
use Codilar\WishlistTask\Model\Service\MoveToWishlistService;

class WishlistManagement implements WishlistManagementInterface
{
    protected $scopeConfig;
    protected $jsonHelper;
    protected $userContext;
    protected $addProductService;
    protected $removeProductService;
    protected $getProductsService;
    protected $moveToCartService;
    protected $moveToWishlistService;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        JsonHelper $jsonHelper,
        UserContextInterface $userContext,
        AddProductService $addProductService,
        RemoveProductService $removeProductService,
        GetProductsService $getProductsService,
        MoveToCartService $moveToCartService,
        MoveToWishlistService $moveToWishlistService
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->jsonHelper = $jsonHelper;
        $this->userContext = $userContext;
        $this->addProductService = $addProductService;
        $this->removeProductService = $removeProductService;
        $this->getProductsService = $getProductsService;
        $this->moveToCartService = $moveToCartService;
        $this->moveToWishlistService = $moveToWishlistService;
    }

    protected function getCustomerId()
    {
        return $this->userContext->getUserId();
    }

    public function addProduct($productSku, $qty = 1)
    {
        // Module is always enabled - no configuration check
        $customerId = $this->getCustomerId();
        if (!$customerId) {
            return $this->jsonHelper->jsonEncode(['success' => false, 'message' => 'Customer not authenticated']);
        }

        return $this->addProductService->execute($customerId, $productSku, $qty);
    }

    public function removeProduct($itemId)
    {
        // Module is always enabled - no configuration check
        $customerId = $this->getCustomerId();
        if (!$customerId) {
            return $this->jsonHelper->jsonEncode(['success' => false, 'message' => 'Customer not authenticated']);
        }

        return $this->removeProductService->execute($customerId, $itemId);
    }

    public function getProducts()
    {
        // Module is always enabled - no configuration check
        $customerId = $this->getCustomerId();
        if (!$customerId) {
            return $this->jsonHelper->jsonEncode(['success' => false, 'message' => 'Customer not authenticated']);
        }

        return $this->getProductsService->execute($customerId);
    }

    public function moveToCart($itemId)
    {
        // Module is always enabled - no configuration check
        $customerId = $this->getCustomerId();
        if (!$customerId) {
            return $this->jsonHelper->jsonEncode(['success' => false, 'message' => 'Customer not authenticated']);
        }

        return $this->moveToCartService->execute($customerId, $itemId);
    }

    public function moveToWishlist($quoteItemId)
    {
        // Module is always enabled - no configuration check
        $customerId = $this->getCustomerId();
        if (!$customerId) {
            return $this->jsonHelper->jsonEncode(['success' => false, 'message' => 'Customer not authenticated']);
        }

        return $this->moveToWishlistService->execute($customerId, $quoteItemId);
    }
}
