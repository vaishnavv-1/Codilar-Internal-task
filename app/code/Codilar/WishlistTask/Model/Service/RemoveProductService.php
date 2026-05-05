<?php
namespace Codilar\WishlistTask\Model\Service;

use Magento\Wishlist\Model\WishlistFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Json\Helper\Data as JsonHelper;

class RemoveProductService
{
    protected $wishlistFactory;
    protected $jsonHelper;

    public function __construct(
        WishlistFactory $wishlistFactory,
        JsonHelper $jsonHelper
    ) {
        $this->wishlistFactory = $wishlistFactory;
        $this->jsonHelper = $jsonHelper;
    }

    public function execute($customerId, $itemId)
    {
        try {
            $wishlist = $this->wishlistFactory->create()->loadByCustomerId($customerId, true);
            $item = $wishlist->getItem($itemId);
            
            if (!$item || $item->getWishlistId() != $wishlist->getId()) {
                throw new LocalizedException(__('Item not found in wishlist'));
            }

            $item->delete();
            $wishlist->save();

            return $this->jsonHelper->jsonEncode([
                'success' => true,
                'message' => 'Product removed from wishlist successfully'
            ]);
        } catch (\Exception $e) {
            return $this->jsonHelper->jsonEncode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
