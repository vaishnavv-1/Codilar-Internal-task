<?php
namespace Codilar\WishlistTask\Model\Service;

use Magento\Wishlist\Model\WishlistFactory;
use Magento\Framework\Json\Helper\Data as JsonHelper;

class GetProductsService
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

    public function execute($customerId)
    {
        try {
            $wishlist = $this->wishlistFactory->create()->loadByCustomerId($customerId, true);
            $items = [];

            foreach ($wishlist->getItemCollection() as $item) {
                $product = $item->getProduct();
                $items[] = [
                    'item_id' => $item->getId(),
                    'product_id' => $product->getId(),
                    'name' => $product->getName(),
                    'sku' => $product->getSku(),
                    'price' => $product->getPrice(),
                    'qty' => $item->getQty(),
                    'added_at' => $item->getAddedAt()
                ];
            }

            return $this->jsonHelper->jsonEncode([
                'success' => true,
                'items' => $items,
                'total_count' => count($items)
            ]);
        } catch (\Exception $e) {
            return $this->jsonHelper->jsonEncode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
