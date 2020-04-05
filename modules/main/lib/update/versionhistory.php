<?php

namespace Bitrix\Main\Update;

use Bitrix\Main\Entity;

/**
 * Class VersionHistoryTable
 * @package Bitrix\Main\Update
 */
class VersionHistoryTable extends Entity\DataManager
{
	/**
	 * Returns DB table name for entity
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_sm_version_history';
	}

	/**
	 * Returns entity map definition.
	 *
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
			'DATE_INSERT' => array(
				'data_type' => 'datetime',
				'required' => true,
			),
			'VERSIONS' => array(
				'data_type' => 'text',
				'required' => true,
			),
		);
	}
}
