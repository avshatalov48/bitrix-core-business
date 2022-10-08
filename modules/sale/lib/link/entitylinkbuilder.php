<?php

namespace Bitrix\Sale\Link;

/**
 * Links builder for sale entities.
 */
interface EntityLinkBuilder
{
	/**
	 * Order detail page.
	 *
	 * @param int $orderId
	 *
	 * @return string
	 */
	public function getOrderDetailUrl(int $orderId): string;

	/**
	 * Shipment detail page.
	 *
	 * @param int $orderId
	 * @param int $shipmentId
	 *
	 * @return string
	 */
	public function getShipmentDetailsLink(int $orderId, int $shipmentId): string;

	/**
	 * Payment detail page.
	 *
	 * @param int $orderId
	 * @param int $paymentId
	 *
	 * @return string
	 */
	public function getPaymentDetailsLink(int $orderId, int $paymentId): string;
}
