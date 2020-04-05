<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Main\Mail\Internal;

use Bitrix\Main\ORM\Data;

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
