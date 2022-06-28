<?php
if (!defined('ABSPATH')) {
	exit();
}
/**
 * PostFinance Checkout WooCommerce
 *
 * This WooCommerce plugin enables to process payments with PostFinance Checkout (https://postfinance.ch/en/business/products/e-commerce/postfinance-checkout-all-in-one.html).
 *
 * @author wallee AG (http://www.wallee.com/)
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache Software License (ASL 2.0)
 */
/**
 * This class handles the required unique ids
 */
class WC_PostFinanceCheckout_Unique_Id {

	/**
	 * Register item id functions hooks
	 */
	public static function init(){
		add_filter('woocommerce_checkout_create_order_line_item', array(
			__CLASS__,
			'copy_unqiue_id_to_order_item' 
		), 10, 4);
		add_filter('woocommerce_checkout_create_order_fee_item', array(
			__CLASS__,
			'copy_unqiue_id_to_order_fee' 
		), 10, 4);
		add_filter('woocommerce_checkout_create_order_shipping_item', array(
			__CLASS__,
			'copy_unqiue_id_to_order_shipping' 
		), 10, 4);
	}

	public static function get_uuid(){
		$data = openssl_random_pseudo_bytes(16);
		$data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
		$data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
		
		return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
	}

	public static function copy_unqiue_id_to_order_item(WC_Order_Item_Product $item, $cart_item_key, $values, WC_Order $order = null){
		//We do not use the cart_item_key as it is deprecated
		$item->add_meta_data('_postfinancecheckout_unique_line_item_id', self::get_uuid(), true);
		return $item;
	}
	public static function copy_unqiue_id_to_order_shipping(WC_Order_Item_Shipping $item, $package_key, $package, WC_Order $order = null){
		$item->add_meta_data('_postfinancecheckout_unique_line_item_id', self::get_uuid(), true);
		return $item;
	}

	public static function copy_unqiue_id_to_order_fee(WC_Order_Item_Fee $item, $fee_key, $fee, WC_Order $order = null){
		$unique_id = null;
		if ($fee->amount < 0) {
			$unique_id = 'discount-' . $fee->id;
		}
		else {
			$unique_id = 'fee-' . $fee->id;
		}
		$item->add_meta_data('_postfinancecheckout_unique_line_item_id', $unique_id, true);
		return $item;
	}
}
WC_PostFinanceCheckout_Unique_Id::init();
