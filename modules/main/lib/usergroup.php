<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2025 Bitrix
 */

namespace Bitrix\Main;

use Bitrix\Main\ORM\Data\Internal\DeleteByFilterTrait;
use Bitrix\Main\ORM\Data\AddStrategy\Trait\AddInsertIgnoreTrait;

/**
 * Class UserGroupTable
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_UserGroup_Query query()
 * @method static EO_UserGroup_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_UserGroup_Result getById($id)
 * @method static EO_UserGroup_Result getList(array $parameters = [])
 * @method static EO_UserGroup_Entity getEntity()
 * @method static \Bitrix\Main\EO_UserGroup createObject($setDefaultValues = true)
 * @method static \Bitrix\Main\EO_UserGroup_Collection createCollection()
 * @method static \Bitrix\Main\EO_UserGroup wakeUpObject($row)
 * @method static \Bitrix\Main\EO_UserGroup_Collection wakeUpCollection($rows)
 */
class UserGroupTable extends Entity\DataManager
{
	use DeleteByFilterTrait;
	use AddInsertIgnoreTrait;

	public static function getTableName()
	{
		return 'b_user_group';
	}

	public static function getMap()
	{
		return [
			'USER_ID' => [
				'data_type' => 'integer',
				'primary' => true,
			],
			'GROUP_ID' => [
				'data_type' => 'integer',
				'primary' => true,
			],
			'DATE_ACTIVE_FROM' => [
				'data_type' => 'datetime',
			],
			'DATE_ACTIVE_TO' => [
				'data_type' => 'datetime',
			],
			'USER' => [
				'data_type' => 'User',
				'reference' => ['=this.USER_ID' => 'ref.ID'],
			],
			'GROUP' => [
				'data_type' => 'Group',
				'reference' => ['=this.GROUP_ID' => 'ref.ID'],
			],
		];
	}
}
