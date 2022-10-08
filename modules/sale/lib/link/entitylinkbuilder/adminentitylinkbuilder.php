<?php

namespace Bitrix\Sale\Link\EntityLinkBuilder;

use Bitrix\Sale\Link\EntityLinkBuilder;

/**
 * Builder admin links for sale entities.
 */
class AdminEntityLinkBuilder implements EntityLinkBuilder
{
	/**
	 * @inheritDoc
	 */
	public function getOrderDetailUrl(int $orderId): string
	{
		return 'sale_order_view.php?' . http_build_query([
			'ID' => $orderId,
			'lang' => LANGUAGE_ID,
		]);
	}

	/**
	 * @inheritDoc
	 */
	public function getShipmentDetailsLink(int $orderId, int $shipmentId): string
	{
		return 'sale_order_shipment_edit.php?' . http_build_query([
			'order_id' => $orderId,
			'shipment_id' => $shipmentId,
			'lang' => LANGUAGE_ID,
		]);
	}

	/**
	 * @inheritDoc
	 */
	public function getPaymentDetailsLink(int $orderId, int $paymentId): string
	{
		return 'sale_order_payment_edit.php?' . http_build_query([
			'order_id' => $orderId,
			'payment_id' => $paymentId,
			'lang' => LANGUAGE_ID,
		]);
	}
}
