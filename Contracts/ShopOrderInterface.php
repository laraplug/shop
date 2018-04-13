<?php

namespace Modules\Shop\Contracts;

/**
 * 주문 인터페이스
 * Order Interface
 */

interface ShopOrderInterface
{

    /**
     * One-to-One relations with the user model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user();

    /**
     * One-to-Many relations with Item.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function items();

    /**
     * One-to-Many relations with Item.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function transactions();

    /**
     * Returns flag indicating if order is lock and cant be modified by the user.
     * An order is locked the moment it enters pending status.
     *
     * @return bool
     */
    public function getIsLockedAttribute();

    /**
     * Returns order name
     *
     * @return int
     */
    public function getNameAttribute();

    /**
     * Returns user name of order
     *
     * @return int
     */
    public function getUserNameAttribute();

    /**
     * Returns user phone number of order
     *
     * @return int
     */
    public function getUserPhoneAttribute();

    /**
     * Returns user email of order
     *
     * @return int
     */
    public function getUserEmailAttribute();

    /**
     * Returns payment method of order.
     *
     * @return float
     */
    public function getPaymentGatewayAttribute();

    /**
     * Returns payment method of order.
     *
     * @return float
     */
    public function getPaymentMethodAttribute();

    /**
     * Returns item count of order.
     *
     * @return int
     */
    public function getCountAttribute();

    /**
     * Returns total price of all the items in order.
     *
     * @return float
     */
    public function getTotalPriceAttribute();

    /**
     * Returns total tax of all the items in order.
     *
     * @return float
     */
    public function getTotalTaxAttribute();

    /**
     * Returns total tax of all the items in order.
     *
     * @return float
     */
    public function getTotalShippingAttribute();

    /**
     * Returns total discount amount based on all coupons applied.
     *
     * @return float
     */
    public function getTotalDiscountAttribute();

    /**
     * Returns total amount to be charged base on total price, tax and discount.
     *
     * @return float
     */
    public function getTotalAttribute();

    /**
     * Returns small thumnail url
     *
     * @return float
     */
    public function getSmallThumbAttribute();

    /**
     * Returns status code of order
     *
     * @return bool
     */
    public function getStatusCode();

    /**
     * Set order status by code
     *
     * @param string $statusCode Status code.
     *
     * @return bool
     */
    public function setStatusCode($statusCode);

    /**
     * Creates the order's transaction.
     *
     * @param int    $userId        User ID
     * @param string $gatewayId     Gateway.
     * @param string $paymentMethod Payment Method.
     * @param int    $transactionId Transaction ID.
     * @param string $currencyCode  Currency Code.
     * @param int    $amount        Amount of money.
     * @param string $message   Transaction detail.
     *
     * @return object
     */
    public function placeTransaction($userId, $gatewayId, $paymentMethod, $transactionId, $currencyCode, $amount, $message = '');

    /**
     * Creates the order's transportation.
     *
     * @param int    $userId        User ID
     * @param string $gatewayId     Gateway.
     * @param string $shippingMethod Shipping Method.
     * @param int    $transportationId Transportation ID.
     * @param string $currencyCode  Currency Code.
     * @param int    $fee        Shipping Fee.
     * @param string $message   Transportation detail.
     *
     * @return object
     */
    public function placeTransportation($userId, $gatewayId, $shippingMethod, $transportationId, $currencyCode, $fee, $message = '');

    /**
     * Convert cart item into order item
     * @param array $items
     * @return void
     */
    public function importItems(array $items);

}
