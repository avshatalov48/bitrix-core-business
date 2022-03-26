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
				// handler
				'sale.delivery.handler.add' => [HandlerService::class, 'addHandler'],
				'sale.delivery.handler.update' => [HandlerService::class, 'updateHandler'],
				'sale.delivery.handler.delete' => [HandlerService::class, 'deleteHandler'],
				'sale.delivery.handler.list' => [HandlerService::class, 'getHandlerList'],
				// delivery service
				'sale.delivery.add' => [DeliveryService::class, 'addDelivery'],
				'sale.delivery.update' => [DeliveryService::class, 'updateDelivery'],
				'sale.delivery.delete' => [DeliveryService::class, 'deleteDelivery'],
				'sale.delivery.getList' => [DeliveryService::class, 'getDeliveryList'],
				'sale.delivery.config.get' => [DeliveryService::class, 'getConfig'],
				'sale.delivery.config.update' => [DeliveryService::class, 'updateConfig'],
				// delivery request
				'sale.delivery.request.update' => [RequestService::class, 'updateRequest'],
				'sale.delivery.request.delete' => [RequestService::class, 'deleteRequest'],
				'sale.delivery.request.sendmessage' => [RequestService::class, 'sendMessage'],
				// delivery extra services
				'sale.delivery.extra.service.add' => [ExtraServicesService::class, 'addExtraServices'],
				'sale.delivery.extra.service.update' => [ExtraServicesService::class, 'updateExtraServices'],
				'sale.delivery.extra.service.delete' => [ExtraServicesService::class, 'deleteExtraServices'],
				'sale.delivery.extra.service.get' => [ExtraServicesService::class, 'get'],
			],
		];
	}
}
