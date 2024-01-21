<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2022 Bitrix
 */
namespace Bitrix\Socialnetwork;

use Bitrix\Main\Entity;
use Bitrix\Socialnetwork\Internals\Pin\Pin;
use Bitrix\Socialnetwork\Internals\Pin\PinCollection;

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
 * @method static \Bitrix\Socialnetwork\Internals\Pin\Pin createObject($setDefaultValues = true)
 * @method static \Bitrix\Socialnetwork\Internals\Pin\PinCollection createCollection()
 * @method static \Bitrix\Socialnetwork\Internals\Pin\Pin wakeUpObject($row)
 * @method static \Bitrix\Socialnetwork\Internals\Pin\PinCollection wakeUpCollection($rows)
 */
class WorkgroupPinTable extends Entity\DataManager
{
	public static function getTableName(): string
	{
		return 'b_sonet_group_pin';
	}

	public static function getMap(): array
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
			CASE WHEN
				EXISTS(
					SELECT 'x'
					FROM {$tableName}
					WHERE
						GROUP_ID = %s
						AND USER_ID = %s
						AND CONTEXT = %s
				)
				THEN 'Y'
				ELSE 'N'
			END
		";
	}

	public static function getObjectClass(): string
	{
		return Pin::class;
	}

	public static function getCollectionClass(): string
	{
		return PinCollection::class;
	}
}