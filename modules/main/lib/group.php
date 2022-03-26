<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2014 Bitrix
 */
namespace Bitrix\Main;

use Bitrix\Main\Entity;

/**
 * Class GroupTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Group_Query query()
 * @method static EO_Group_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_Group_Result getById($id)
 * @method static EO_Group_Result getList(array $parameters = [])
 * @method static EO_Group_Entity getEntity()
 * @method static \Bitrix\Main\EO_Group createObject($setDefaultValues = true)
 * @method static \Bitrix\Main\EO_Group_Collection createCollection()
 * @method static \Bitrix\Main\EO_Group wakeUpObject($row)
 * @method static \Bitrix\Main\EO_Group_Collection wakeUpCollection($rows)
 */
class GroupTable extends Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_group';
	}

	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'TIMESTAMP_X' => array(
				'data_type' => 'datetime'
			),
			'ACTIVE' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y')
			),
			'C_SORT' => array(
				'data_type' => 'integer'
			),
			'IS_SYSTEM' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y')
			),
			'ANONYMOUS' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y')
			),
			'NAME' => array(
				'data_type' => 'string'
			),
			'DESCRIPTION' => array(
				'data_type' => 'string'
			),
			'STRING_ID' => array(
				'data_type' => 'string'
			),
		);
	}
}
