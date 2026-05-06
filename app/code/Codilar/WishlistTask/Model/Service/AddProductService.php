<?php
namespace Codilar\WishlistTask\Model\Service;

use Magento\Wishlist\Model\WishlistFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Json\Helper\Data as JsonHelper;

class AddProductService
{
    protected $wishlistFactory;
    protected $productRepository;
    protected $jsonHelper;

    public function __construct(
        WishlistFactory $wishlistFactory,
        ProductRepositoryInterface $productRepository,
        JsonHelper $jsonHelper
    ) {
        $this->wishlistFactory = $wishlistFactory;
        $this->productRepository = $productRepository;
        $this->jsonHelper = $jsonHelper;
    }

    public function execute($customerId, $productSku, $qty = 1)
    {
        try {
            $product = $this->productRepository->get($productSku);
            $wishlist = $this->wishlistFactory->create()->loadByCustomerId($customerId, true);
            $wishlist->addNewItem($product, ['qty' => $qty]);
            $wishlist->save();

            return $this->jsonHelper->jsonEncode([
                'success' => true,
                'message' => 'Product added to wishlist successfully',
                'wishlist_id' => $wishlist->getId()
            ]);
        } catch (\Exception $e) {
            return $this->jsonHelper->jsonEncode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}