<?php

namespace Bitrix\Sale\Internals;

use Bitrix\Main;

/**
 * Class CallbackRegistryTable
 * @package Bitrix\Sale\Internals
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_CallbackRegistry_Query query()
 * @method static EO_CallbackRegistry_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_CallbackRegistry_Result getById($id)
 * @method static EO_CallbackRegistry_Result getList(array $parameters = [])
 * @method static EO_CallbackRegistry_Entity getEntity()
 * @method static \Bitrix\Sale\Internals\EO_CallbackRegistry createObject($setDefaultValues = true)
 * @method static \Bitrix\Sale\Internals\EO_CallbackRegistry_Collection createCollection()
 * @method static \Bitrix\Sale\Internals\EO_CallbackRegistry wakeUpObject($row)
 * @method static \Bitrix\Sale\Internals\EO_CallbackRegistry_Collection wakeUpCollection($rows)
 */
class CallbackRegistryTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sale_documentgenerator_callback_registry';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return
		[
			'ID' =>
			[
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			],
			'DATE_INSERT' =>
			[
				'data_type' => 'datetime'
			],
			'DOCUMENT_ID' =>
			[
				'data_type' => 'integer'
			],
			'MODULE_ID' =>
			[
				'data_type' => 'string'
			],
			'CALLBACK_CLASS' =>
			[
				'data_type' => 'string'
			],
			'CALLBACK_METHOD' =>
			[
				'data_type' => 'string'
			],
		];
	}
}