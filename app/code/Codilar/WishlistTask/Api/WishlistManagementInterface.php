<?php
namespace Codilar\WishlistTask\Api;

interface WishlistManagementInterface
{
    /**
     * Add product to wishlist
     *
     * @param string $productSku
     * @param int $qty
     * @return string
     */
    public function addProduct($productSku, $qty = 1);

    /**
     * Remove product from wishlist
     *
     * @param int $itemId
     * @return string
     */
    public function removeProduct($itemId);

    /**
     * Get wishlist products
     *
     * @return string
     */
    public function getProducts();

    /**
     * Move product from wishlist to cart
     *
     * @param int $itemId
     * @return string
     */
    public function moveToCart($itemId);

    /**
     * Move product from cart to wishlist
     *
     * @param int $quoteItemId
     * @return string
     */
    public function moveToWishlist($quoteItemId);
}
