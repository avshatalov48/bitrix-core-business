<?php

namespace Bitrix\Sale\Delivery\Rest;

/**
 * Class Handlers
 * @package Bitrix\Sale\Delivery\Rest
 */
class Handlers
{
	private const SCOPE = 'delivery';

	/**
	 * @return \array[][]
	 */
	public static function OnRestServiceBuildDescription(): array
	{
		return [
			self::SCOPE => [
				'sale.delivery.handler.add' => [HandlerService::class, 'addHandler'],
				'sale.delivery.handler.update' => [HandlerService::class, 'updateHandler'],
				'sale.delivery.handler.delete' => [HandlerService::class, 'deleteHandler'],
				'sale.delivery.handler.list' => [HandlerService::class, 'getHandlerList'],

				'sale.delivery.add' => [DeliveryService::class, 'addDelivery'],
				'sale.delivery.update' => [DeliveryService::class, 'updateDelivery'],
				'sale.delivery.delete' => [DeliveryService::class, 'deleteDelivery'],
				'sale.delivery.list' => [DeliveryService::class, 'getDeliveryList'],
				'sale.delivery.config.get' => [DeliveryService::class, 'getConfig'],
				'sale.delivery.config.update' => [DeliveryService::class, 'updateConfig'],
			],
		];
	}
}