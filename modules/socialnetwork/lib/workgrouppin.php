<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2022 Bitrix
 */
namespace Bitrix\Socialnetwork;

use Bitrix\Main\Entity;

/**
 * Class WorkgroupPinTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_WorkgroupPin_Query query()
 * @method static EO_WorkgroupPin_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_WorkgroupPin_Result getById($id)
 * @method static EO_WorkgroupPin_Result getList(array $parameters = [])
 * @method static EO_WorkgroupPin_Entity getEntity()
 * @method static \Bitrix\Socialnetwork\EO_WorkgroupPin createObject($setDefaultValues = true)
 * @method static \Bitrix\Socialnetwork\EO_WorkgroupPin_Collection createCollection()
 * @method static \Bitrix\Socialnetwork\EO_WorkgroupPin wakeUpObject($row)
 * @method static \Bitrix\Socialnetwork\EO_WorkgroupPin_Collection wakeUpCollection($rows)
 */
class WorkgroupPinTable extends Entity\DataManager
{
	public static function getTableName()
	{
		return 'b_sonet_group_pin';
	}

	public static function getMap()
	{
		return [
			'ID' => [
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			],
			'USER_ID' => [
				'data_type' => 'integer',
			],
			'USER' => [
				'data_type' => '\Bitrix\Main\User',
				'reference' => [ '=this.USER_ID' => 'ref.ID' ],
			],
			'GROUP_ID' => [
				'data_type' => 'integer',
			],
			'GROUP' => [
				'data_type' => '\Bitrix\Socialnetwork\Workgroup',
				'reference' => [ '=this.GROUP_ID' => 'ref.ID' ],
			],
			'CONTEXT' => [
				'data_type' => 'string',
			],
		];
	}

	public static function getSelectExpression(): string
	{
		$tableName = static::getTableName();

		return "
			IF(
				EXISTS(
					SELECT 'x'
					FROM {$tableName}
					WHERE
						GROUP_ID = %s
						AND USER_ID = %s
						AND CONTEXT = %s
				),
				'Y',
				'N'
			)
		";
	}
}
