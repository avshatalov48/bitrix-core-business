<?php

namespace Bitrix\Sale\Link\EntityLinkBuilder;

use Bitrix\Main\Loader;
use Bitrix\Sale\Link\EntityLinkBuilder;

Loader::requireModule('crm');

/**
 * Builder crm links for sale entities.
 */
class CrmEntityLinkBuilder implements EntityLinkBuilder
{
	private \Bitrix\Crm\Service\Sale\EntityLinkBuilder\EntityLinkBuilder $builder;

	public function __construct()
	{
		$this->builder = \Bitrix\Crm\Service\Sale\EntityLinkBuilder\EntityLinkBuilder::getInstance();
	}

	/**
	 * Detail page of entity binded to order.
	 *
	 * @param int $orderId
	 *
	 * @return string
	 */
	public function getEntityDetailUrl(int $orderId): string
	{
		return (string)$this->builder->getEntityDetailUrlByOrderId($orderId);
	}

	/**
	 * @inheritDoc
	 */
	public function getOrderDetailUrl(int $orderId): string
	{
		return "/shop/orders/details/{$orderId}/";
	}

	/**
	 * @inheritDoc
	 */
	public function getShipmentDetailsLink(int $orderId, int $shipmentId): string
	{
		return (string)$this->builder->getShipmentDetailsLink($shipmentId);
	}

	/**
	 * @inheritDoc
	 */
	public function getPaymentDetailsLink(int $orderId, int $paymentId): string
	{
		return (string)$this->builder->getPaymentDetailsLink($paymentId);
	}
}
