<?php
/**
 *
 * WC_PostFinanceCheckout_Webhook_Transaction_Invoice Class
 *
 * PostFinanceCheckout
 * This plugin will add support for all PostFinanceCheckout payments methods and connect the PostFinanceCheckout servers to your WooCommerce webshop (https://postfinance.ch/en/business/products/e-commerce/postfinance-checkout-all-in-one.html).
 *
 * @category Class
 * @package  PostFinanceCheckout
 * @author   wallee AG (http://www.wallee.com/)
 * @license  http://www.apache.org/licenses/LICENSE-2.0 Apache Software License (ASL 2.0)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}
/**
 * Webhook processor to handle transaction completion state transitions.
 */
class WC_PostFinanceCheckout_Webhook_Transaction_Invoice extends WC_PostFinanceCheckout_Webhook_Order_Related_Abstract {


	/**
	 * Load entity.
	 *
	 * @param WC_PostFinanceCheckout_Webhook_Request $request request.
	 * @return object|\PostFinanceCheckout\Sdk\Model\TransactionInvoice
	 * @throws \PostFinanceCheckout\Sdk\ApiException ApiException.
	 * @throws \PostFinanceCheckout\Sdk\Http\ConnectionException ConnectionException.
	 * @throws \PostFinanceCheckout\Sdk\VersioningException VersioningException.
	 */
	protected function load_entity( WC_PostFinanceCheckout_Webhook_Request $request ) {
		$transaction_invoice_service = new \PostFinanceCheckout\Sdk\Service\TransactionInvoiceService( WC_PostFinanceCheckout_Helper::instance()->get_api_client() );
		return $transaction_invoice_service->read( $request->get_space_id(), $request->get_entity_id() );
	}

	/**
	 * Load transaction.
	 *
	 * @param mixed $transaction_invoice transaction invoice.
	 * @return \PostFinanceCheckout\Sdk\Model\Transaction
	 * @throws \PostFinanceCheckout\Sdk\ApiException ApiException.
	 * @throws \PostFinanceCheckout\Sdk\Http\ConnectionException ConnectionException.
	 * @throws \PostFinanceCheckout\Sdk\VersioningException VersioningException.
	 */
	protected function load_transaction( $transaction_invoice ) {
		/* @var \PostFinanceCheckout\Sdk\Model\TransactionInvoice $transaction_invoice */
		$transaction_service = new \PostFinanceCheckout\Sdk\Service\TransactionService( WC_PostFinanceCheckout_Helper::instance()->get_api_client() );
		return $transaction_service->read( $transaction_invoice->getLinkedSpaceId(), $transaction_invoice->getCompletion()->getLineItemVersion()->getTransaction()->getId() );
	}

	/**
	 * Get order id.
	 *
	 * @param mixed $transaction_invoice transaction invoice.
	 * @return int|string
	 */
	protected function get_order_id( $transaction_invoice ) {
		/* @var \PostFinanceCheckout\Sdk\Model\TransactionInvoice $transaction_invoice */
		return WC_PostFinanceCheckout_Entity_Transaction_Info::load_by_transaction( $transaction_invoice->getLinkedSpaceId(), $transaction_invoice->getCompletion()->getLineItemVersion()->getTransaction()->getId() )->get_order_id();
	}

	/**
	 * Get transaction invoice.
	 *
	 * @param mixed $transaction_invoice transaction invoice.
	 * @return int
	 */
	protected function get_transaction_id( $transaction_invoice ) {
		/* @var \PostFinanceCheckout\Sdk\Model\TransactionInvoice $transaction_invoice */
		return $transaction_invoice->getLinkedTransaction();
	}

	/**
	 * Process
	 *
	 * @param WC_Order $order order.
	 * @param mixed    $transaction_invoice transaction invoice.
	 * @return void
	 */
	protected function process_order_related_inner( WC_Order $order, $transaction_invoice ) {
		/* @var \PostFinanceCheckout\Sdk\Model\TransactionInvoice $transaction_invoice */
		switch ( $transaction_invoice->getState() ) {
			case \PostFinanceCheckout\Sdk\Model\TransactionInvoiceState::DERECOGNIZED:
				$order->add_order_note( __( 'Invoice Not Settled' ) );
				break;
			case \PostFinanceCheckout\Sdk\Model\TransactionInvoiceState::NOT_APPLICABLE:
			case \PostFinanceCheckout\Sdk\Model\TransactionInvoiceState::PAID:
				$order->add_order_note( __( 'Invoice Settled' ) );
				break;
			default:
				// Nothing to do.
				break;
		}
	}
}
