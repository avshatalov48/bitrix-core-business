<?php

namespace Bitrix\Seo\BusinessSuite\Internals;

use Bitrix\Main\Entity;

/**
 * Class ServiceQueueTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_ServiceQueue_Query query()
 * @method static EO_ServiceQueue_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_ServiceQueue_Result getById($id)
 * @method static EO_ServiceQueue_Result getList(array $parameters = array())
 * @method static EO_ServiceQueue_Entity getEntity()
 * @method static \Bitrix\Seo\BusinessSuite\Internals\EO_ServiceQueue createObject($setDefaultValues = true)
 * @method static \Bitrix\Seo\BusinessSuite\Internals\EO_ServiceQueue_Collection createCollection()
 * @method static \Bitrix\Seo\BusinessSuite\Internals\EO_ServiceQueue wakeUpObject($row)
 * @method static \Bitrix\Seo\BusinessSuite\Internals\EO_ServiceQueue_Collection wakeUpCollection($rows)
 */
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