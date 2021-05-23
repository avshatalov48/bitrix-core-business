<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender\Internals\Model;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class GroupQueueTable extends Entity\DataManager
{
	const TYPE = [
		'POSTING' => 1,
		'REST' => 2,
	];

	/**
	 * Get table name.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sender_group_queue';
	}

	/**
	 * Get map.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'autocomplete' => true,
				'primary' => true,
			),
			'DATE_INSERT' => array(
				'data_type' => 'datetime',
			),
			'TYPE' => array(
				'data_type' => 'integer',
			),
			'ENTITY_ID' => array(
				'data_type' => 'integer',
			),
			'GROUP_ID' => array(
				'data_type' => 'integer',
			),
		);
	}
}