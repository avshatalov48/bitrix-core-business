<?php

namespace Bitrix\Main\Update;

use Bitrix\Main\Entity;

/**
 * Class VersionHistoryTable
 * @package Bitrix\Main\Update
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_VersionHistory_Query query()
 * @method static EO_VersionHistory_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_VersionHistory_Result getById($id)
 * @method static EO_VersionHistory_Result getList(array $parameters = [])
 * @method static EO_VersionHistory_Entity getEntity()
 * @method static \Bitrix\Main\Update\EO_VersionHistory createObject($setDefaultValues = true)
 * @method static \Bitrix\Main\Update\EO_VersionHistory_Collection createCollection()
 * @method static \Bitrix\Main\Update\EO_VersionHistory wakeUpObject($row)
 * @method static \Bitrix\Main\Update\EO_VersionHistory_Collection wakeUpCollection($rows)
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
