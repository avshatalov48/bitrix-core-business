<?php

namespace Bitrix\Sale\Internals;

use Bitrix\Main;

/**
 * Class CustomFieldsTable
 * @package Bitrix\Sale\Internals
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_CustomFields_Query query()
 * @method static EO_CustomFields_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_CustomFields_Result getById($id)
 * @method static EO_CustomFields_Result getList(array $parameters = [])
 * @method static EO_CustomFields_Entity getEntity()
 * @method static \Bitrix\Sale\Internals\EO_CustomFields createObject($setDefaultValues = true)
 * @method static \Bitrix\Sale\Internals\EO_CustomFields_Collection createCollection()
 * @method static \Bitrix\Sale\Internals\EO_CustomFields wakeUpObject($row)
 * @method static \Bitrix\Sale\Internals\EO_CustomFields_Collection wakeUpCollection($rows)
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