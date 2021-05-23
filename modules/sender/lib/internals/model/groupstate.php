<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender\Internals\Model;

use Bitrix\Main\Access\Entity\DataManager;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class GroupStateTable extends DataManager
{
	const STATES = [
		'CREATED' => 1,
		'IN_PROGRESS' => 2,
		'COMPLETED' => 3,
		'HALTED' => 4,
	];

	/**
	 * Get table name.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sender_group_state';
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
			'STATE' => array(
				'data_type' => 'integer',
			),
			'FILTER_ID' => array(
				'data_type' => 'string',
			),
			'ENDPOINT' => array(
				'data_type' => 'string',
			),
			'GROUP' => array(
				'data_type' => 'Bitrix\Sender\GroupTable',
				'reference' => array('=this.GROUP_ID' => 'ref.ID'),
			),
			'GROUP_ID' => array(
				'data_type' => 'integer',
				'required' => true,
			),
			'OFFSET' => array(
				'data_type' => 'integer',
			)
		);
	}
}