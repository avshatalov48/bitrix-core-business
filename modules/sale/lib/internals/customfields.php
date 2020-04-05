<?php

namespace Bitrix\Sale\Internals;

use Bitrix\Main;

/**
 * Class CustomFieldsTable
 * @package Bitrix\Sale\Internals
 */
class CustomFieldsTable extends Main\Entity\DataManager
{
	const ENTITY_TYPE_SHIPMENT = 'SHIPMENT';

	public static function getTableName()
	{
		return 'b_sale_order_entities_custom_fields';
	}

	public static function getMap()
	{
		return [
			'ID' => [
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true
			],
			'ENTITY_ID' => [
				'data_type' => 'integer',
				'required'   => true
			],
			'ENTITY_TYPE' => [
				'data_type' => 'string',
				'required'   => true
			],
			'ENTITY_REGISTRY_TYPE' => [
				'data_type' => 'string',
				'required'   => true
			],
			'FIELD' => [
				'data_type' => 'string',
				'required'   => true
			],
		];
	}
}