<?php
namespace Codilar\WishlistTask\Model\Service;

use Magento\Wishlist\Model\WishlistFactory;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Json\Helper\Data as JsonHelper;

class MoveToCartService
{
    protected $wishlistFactory;
    protected $quoteFactory;
    protected $cartRepository;
    protected $jsonHelper;

    public function __construct(
        WishlistFactory $wishlistFactory,
        QuoteFactory $quoteFactory,
        CartRepositoryInterface $cartRepository,
        JsonHelper $jsonHelper
    ) {
        $this->wishlistFactory = $wishlistFactory;
        $this->quoteFactory = $quoteFactory;
        $this->cartRepository = $cartRepository;
        $this->jsonHelper = $jsonHelper;
    }

    public function execute($customerId, $itemId)
    {
        try {
            $wishlist = $this->wishlistFactory->create()->loadByCustomerId($customerId, false); // Don't auto-create
            
            if (!$wishlist->getId()) {
                throw new LocalizedException(__('No wishlist found for customer'));
            }
            
            $item = $wishlist->getItem($itemId);

            if (!$item || $item->getWishlistId() != $wishlist->getId()) {
                throw new LocalizedException(__('Item not found in wishlist'));
            }

            $quote = $this->quoteFactory->create()->loadByCustomer($customerId);
            if (!$quote->getId()) {
                $quote->setCustomerId($customerId);
                $quote->setStoreId(1);
            }

            $product = $item->getProduct();
            $quote->addProduct($product, $item->getQty());
            $this->cartRepository->save($quote);

            $item->delete();
            $wishlist->save();

            return $this->jsonHelper->jsonEncode([
                'success' => true,
                'message' => 'Product moved to cart successfully',
                'quote_id' => $quote->getId()
            ]);
        } catch (\Exception $e) {
            return $this->jsonHelper->jsonEncode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
