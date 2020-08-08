<?php

namespace Sale\Handlers\Delivery\Taxi\Yandex;

use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Delivery\ExtraServices\Checkbox;
use Bitrix\Sale\Delivery\ExtraServices\Enum;

/**
 * Class OrderEntityProvider
 * @package Sale\Handlers\Delivery\Taxi\Yandex
 */
class EntityProvider
{
	/**
	 * @return OrderPropertyGroup
	 */
	public function getGroup(): OrderPropertyGroup
	{
		return new OrderPropertyGroup(
			'DELIVERY_SERVICE',
			Loc::getMessage('SALE_YANDEX_TAXI_DELIVERY_PROP_GROUP_NAME')
		);
	}

	/**
	 * @return OrderProperty
	 */
	public function getPropertyFrom(): OrderProperty
	{
		return $this->buildAddressProperty(
			'DELIVERY_SERVICE_ADDRESS_FROM',
			Loc::getMessage('SALE_YANDEX_TAXI_PROP_FROM_NAME')
		);
	}

	/**
	 * @return OrderProperty
	 */
	public function getPropertyTo(): OrderProperty
	{
		return $this->buildAddressProperty(
			'DELIVERY_SERVICE_ADDRESS_TO',
			Loc::getMessage('SALE_YANDEX_TAXI_PROP_TO_NAME')
		);
	}

	/**
	 * @return OrderProperty
	 */
	public function getCommentProperty(): OrderProperty
	{
		$orderProperty = new OrderProperty(
			'COMMENT_FOR_DRIVER',
			'STRING',
			Loc::getMessage('SALE_YANDEX_TAXI_COMMENT_FOR_DRIVER')
		);

		return $orderProperty
			->setIsRequired(false)
			->setIsMultiple(false)
			->setSettings(['MULTILINE' => 'Y']);
	}

	/**
	 * @return ExtraService
	 */
	public function getVehicleTypeExtraService(): ExtraService
	{
		$expressValue = 'express';

		return (new ExtraService())
			->setCode('VEHICLE_TYPE')
			->setName(Loc::getMessage('SALE_YANDEX_TAXI_VEHICLE_TYPE'))
			->setClassName('\\' . Enum::class)
			->setInitValue($expressValue)
			->setParams(
				[
					'PRICES' => [
						'express' => [
							'TITLE' => Loc::getMessage('SALE_YANDEX_TAXI_VEHICLE_TYPE_CAR'),
							'PRICE' => 0.00,
							'CODE' => $expressValue,
						],
					],
				]
			);
	}

	/**
	 * @return ExtraService
	 */
	public function getDoorDeliveryExtraService(): ExtraService
	{
		return (new ExtraService())
			->setCode('DOOR_DELIVERY')
			->setName(Loc::getMessage('SALE_YANDEX_TAXI_TO_DOOR_DELIVERY'))
			->setClassName('\\' . Checkbox::class)
			->setInitValue('Y')
			->setParams(['PRICE' => 0.00,]);
	}

	/**
	 * @param string $code
	 * @param string $name
	 * @return OrderProperty
	 */
	private function buildAddressProperty(string $code, string $name): OrderProperty
	{
		return (new OrderProperty($code, 'ADDRESS', $name))
			->setIsRequired(true)
			->setIsMultiple(false);
	}
}
