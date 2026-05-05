<?php
namespace Codilar\WishlistTask\Model\Service;

use Magento\Wishlist\Model\WishlistFactory;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\CartItemRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Json\Helper\Data as JsonHelper;

class MoveToWishlistService
{
    protected $wishlistFactory;
    protected $quoteFactory;
    protected $cartRepository;
    protected $cartItemRepository;
    protected $jsonHelper;

    public function __construct(
        WishlistFactory $wishlistFactory,
        QuoteFactory $quoteFactory,
        CartRepositoryInterface $cartRepository,
        CartItemRepositoryInterface $cartItemRepository,
        JsonHelper $jsonHelper
    ) {
        $this->wishlistFactory = $wishlistFactory;
        $this->quoteFactory = $quoteFactory;
        $this->cartRepository = $cartRepository;
        $this->cartItemRepository = $cartItemRepository;
        $this->jsonHelper = $jsonHelper;
    }

    public function execute($customerId, $quoteItemId)
    {
        try {
            $quote = $this->quoteFactory->create()->loadByCustomer($customerId);
            $quoteItem = $quote->getItemById($quoteItemId);

            if (!$quoteItem) {
                throw new LocalizedException(__('Item not found in cart'));
            }

            $product = $quoteItem->getProduct();
            $qty = $quoteItem->getQty();
            
            // Add to wishlist first
            $wishlist = $this->wishlistFactory->create()->loadByCustomerId($customerId, true);
            $wishlist->addNewItem($product, ['qty' => $qty]);
            $wishlist->save();

            // Delete cart item using repository
            $this->cartItemRepository->deleteById($quote->getId(), $quoteItemId);

            return $this->jsonHelper->jsonEncode([
                'success' => true,
                'message' => 'Product moved to wishlist successfully',
                'wishlist_id' => $wishlist->getId()
            ]);
        } catch (\Exception $e) {
            return $this->jsonHelper->jsonEncode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
