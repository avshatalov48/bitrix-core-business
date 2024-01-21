<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2023 Bitrix
 */

namespace Bitrix\Main;

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
class GroupTable extends ORM\Data\DataManager
{
	public static function getTableName()
	{
		return 'b_group';
	}

	public static function getMap()
	{
		return [
			'ID' => [
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			],
			'TIMESTAMP_X' => [
				'data_type' => 'datetime',
			],
			'ACTIVE' => [
				'data_type' => 'boolean',
				'values' => ['N', 'Y'],
			],
			'C_SORT' => [
				'data_type' => 'integer',
			],
			'IS_SYSTEM' => [
				'data_type' => 'boolean',
				'values' => ['N', 'Y'],
			],
			'ANONYMOUS' => [
				'data_type' => 'boolean',
				'values' => ['N', 'Y'],
			],
			'NAME' => [
				'data_type' => 'string',
			],
			'DESCRIPTION' => [
				'data_type' => 'string',
			],
			'SECURITY_POLICY' => [
				'data_type' => 'text',
			],
			'STRING_ID' => [
				'data_type' => 'string',
			],
		];
	}
}
