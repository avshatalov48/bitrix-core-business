<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Main\Mail\Internal;

use Bitrix\Main\ORM\Data;

/**
 * Class EventTypeTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_EventType_Query query()
 * @method static EO_EventType_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_EventType_Result getById($id)
 * @method static EO_EventType_Result getList(array $parameters = [])
 * @method static EO_EventType_Entity getEntity()
 * @method static \Bitrix\Main\Mail\Internal\EO_EventType createObject($setDefaultValues = true)
 * @method static \Bitrix\Main\Mail\Internal\EO_EventType_Collection createCollection()
 * @method static \Bitrix\Main\Mail\Internal\EO_EventType wakeUpObject($row)
 * @method static \Bitrix\Main\Mail\Internal\EO_EventType_Collection wakeUpCollection($rows)
 */
class EventTypeTable extends Data\DataManager
{
	const TYPE_EMAIL = 'email';
	const TYPE_SMS = 'sms';

	/**
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_event_type';
	}

	/**
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'LID' => array(
				'data_type' => 'string',
				'required' => true,
			),
			'EVENT_NAME' => array(
				'data_type' => 'string',
				'required' => true,
			),
			'NAME' => array(
				'data_type' => 'string',
			),
			'DESCRIPTION' => array(
				'data_type' => 'string',
			),
			'SORT' => array(
				'data_type' => 'integer',
				'required' => true,
				'default_value' => 100,
			),
			'EVENT_TYPE' => array(
				'data_type' => 'string',
			),
		);
	}
}
