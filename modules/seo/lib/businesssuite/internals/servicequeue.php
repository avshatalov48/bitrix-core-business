<?php

namespace Bitrix\Seo\BusinessSuite\Internals;

use Bitrix\Main\Entity;

final class ServiceQueueTable extends Entity\DataManager
{
	/**
	 * Get table name.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_seo_service_queue';
	}

	/**
	 * Get map.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return [
			'ID' => [
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			],
			'TYPE' =>[
				'data_type' => 'string',
				'required' => true,
			],
			'SERVICE_TYPE' => [
				'data_type' => 'string',
				'required' => true,
			],
			'CLIENT_ID' => [
				'data_type' => 'integer',
				'required' => true,
			],
			'SORT' => [
				'data_type' => 'integer',
				'default_value' => 100,
				'required' => true,
			]
		];
	}


}