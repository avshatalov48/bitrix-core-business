<?php

namespace Bitrix\Sale\Internals;

use Bitrix\Main;

/**
 * Class CallbackRegistryTable
 * @package Bitrix\Sale\Internals
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