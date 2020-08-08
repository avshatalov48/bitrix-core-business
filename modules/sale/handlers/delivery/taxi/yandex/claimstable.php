<?php

namespace Sale\Handlers\Delivery\Taxi\Yandex;

use Bitrix\Main;

/**
 * Class ClaimsTable
 * @package Sale\Handlers\Delivery\Taxi\Yandex
 */
class ClaimsTable extends Main\Entity\DataManager
{
	/**
	 * @inheritdoc
	 */
	public static function getTableName()
	{
		return 'b_sale_delivery_yandex_taxi_claims';
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
			'SHIPMENT_ID' => [
				'data_type' => 'integer',
				'required' => true,
			],
			new Main\Entity\ReferenceField(
				'SHIPMENT',
				'\Bitrix\Sale\Internals\ShipmentTable',
				['=this.SHIPMENT_ID' => 'ref.ID']
			),
			'CREATED_AT' => [
				'data_type' => 'datetime',
				'required' => true,
			],
			'UPDATED_AT' => [
				'data_type' => 'datetime',
				'required' => true,
			],
			'FURTHER_CHANGES_EXPECTED' => [
				'data_type' => 'string',
			],
			'EXTERNAL_ID' => [
				'data_type' => 'string',
				'required' => true,
			],
			'EXTERNAL_STATUS' => [
				'data_type' => 'string',
				'required' => true,
			],
			'EXTERNAL_RESOLUTION' => [
				'data_type' => 'string',
			],
			'EXTERNAL_CREATED_TS' => [
				'data_type' => 'string',
				'required' => true,
			],
			'EXTERNAL_UPDATED_TS' => [
				'data_type' => 'string',
				'required' => true,
			],
			'EXTERNAL_CURRENCY' => ['data_type' => 'string'],
			'EXTERNAL_FINAL_PRICE' => ['data_type' => 'float'],
			'INITIAL_CLAIM' => ['data_type' => 'string']
		];
	}
}
