<?php

namespace Modules\Shop\Contracts;

/**
 * 장바구니 인터페이스
 * Cart Interface
 */

interface ShopCartInterface
{

    /**
     * 세션ID리턴
     * Get session Id
     * @return string
     */
    public function getSessionId();

    /**
     * 현재 장바구니의 인스턴스를 설정 또는 가져오기 (default, whishlist..etc)
     * Set & Get the current cart instance. (default, whishlist...etc)
     *
     * @param string|null $instance
     * @return self
     */
    public function instance($instance = null);

    /**
     * 사용자 인스턴스
     * User Instance
     *
     * @return \Modules\User\Entities\UserInterface
     */
    public function user();

    /**
     * 장바구니상품과 1대다 관계
     * One-to-Many relations with Item.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items();

    /**
     * 장바구니에 상품 추가
     * Adds item to cart.
     *
     * @param int                   $shopId
     * @param int                   $productId   Product to add
     * @param int                   $quantity         Item quantity in cart.
     * @param array                 $options     Selected options for product
     * @param string                $note        Note for item
     */
    public function add($shopId, $productId, int $quantity = 1, array $options = [], $note = null);

    /**
     * Removes an item from the cart.
     * Returns flag indicating if removal was successful.
     *
     * @param mixed $itemId   Item id to remove
     *
     * @return bool
     */
    public function remove($itemId);

    /**
     * Whipes put cart
     */
    public function flush();

    /**
     * Get count of cart items
     * @return int
     */
    public function count();

    /**
     * Returns total price of all the items in cart.
     *
     * @return float
     */
    public function getTotalPrice();

    /**
     * Returns total tax of all the items in cart.
     *
     * @return float
     */
    public function getTotalTax();

    /**
     * Returns total discount amount based on all coupons applied.
     *
     * @return float
     */
    public function getTotalDiscount();

    /**
     * Returns total amount to be charged base on total price, tax and discount.
     *
     * @return float
     */
    public function getTotal();

    /**
     * 장바구니 주문처리
     * Place Order of Cart
     * @param  array  $data
     * @return array
     */
    public function placeOrder(array $data);

}
