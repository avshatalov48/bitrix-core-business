<?php

namespace Bitrix\Sale\Internals;

use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity\ReferenceField;

/**
 * Class LocalDeliveryRequest
 * @package Bitrix\Sale\Internals
 */
class LocalDeliveryRequestTable extends DataManager
{
	/**
	 * @inheritdoc
	 */
	public static function getTableName()
	{
		return 'b_sale_local_delivery_requests';
	}

	/**
	 * @inheritdoc
	 */
	public static function getMap()
	{
		return [
			'ID' => [
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true
			],
			'DELIVERY_SERVICE_ID' => [
				'data_type' => 'integer',
				'required' => true,
			],
			'SHIPMENT_ID' => [
				'data_type' => 'integer',
				'required' => true,
			],
			'CREATED_AT' => [
				'data_type' => 'datetime',
				'required' => true,
			],
			'EXTERNAL_ID' => [
				'data_type' => 'string',
				'required' => true,
			],
			new ReferenceField(
				'SHIPMENT',
				'\Bitrix\Sale\Internals\ShipmentTable',
				['=this.SHIPMENT_ID' => 'ref.ID']
			),
		];
	}
}

/*
 * `ID` int NOT NULL AUTO_INCREMENT,
  `CREATED_AT` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `SHIPMENT_ID` int NOT NULL,
  `DELIVERY_SERVICE_ID` int NOT NULL,
  `EXTERNAL_ID` varchar(255) NOT NULL,
 * */