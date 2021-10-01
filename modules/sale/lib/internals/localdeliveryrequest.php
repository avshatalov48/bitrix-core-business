<?php

namespace Bitrix\Sale\Internals;

use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity\ReferenceField;

/**
 * Class LocalDeliveryRequest
 * @package Bitrix\Sale\Internals
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_LocalDeliveryRequest_Query query()
 * @method static EO_LocalDeliveryRequest_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_LocalDeliveryRequest_Result getById($id)
 * @method static EO_LocalDeliveryRequest_Result getList(array $parameters = array())
 * @method static EO_LocalDeliveryRequest_Entity getEntity()
 * @method static \Bitrix\Sale\Internals\EO_LocalDeliveryRequest createObject($setDefaultValues = true)
 * @method static \Bitrix\Sale\Internals\EO_LocalDeliveryRequest_Collection createCollection()
 * @method static \Bitrix\Sale\Internals\EO_LocalDeliveryRequest wakeUpObject($row)
 * @method static \Bitrix\Sale\Internals\EO_LocalDeliveryRequest_Collection wakeUpCollection($rows)
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