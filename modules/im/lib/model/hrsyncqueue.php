<?php
namespace Bitrix\Im\Model;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\BooleanField;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;

/**
 * Class HrSyncQueueTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> ENTITY_TYPE string(25) mandatory
 * <li> ENTITY_ID int mandatory
 * <li> DIRECTION string(100) mandatory
 * <li> NODE_ID int mandatory
 * <li> WITH_CHILD_NODES bool ('N', 'Y') optional default 'N'
 * <li> POINTER int optional default 0
 * <li> STATUS string(100) optional
 * <li> IS_LOCKED bool ('N', 'Y') optional default 'N'
 * <li> DATE_CREATE datetime optional
 * <li> DATE_UPDATE datetime optional
 * </ul>
 *
 * @package Bitrix\Im
 **/

class HrSyncQueueTable extends DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_im_hr_sync_queue';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return [
			'ID' => new IntegerField(
				'ID',
				[
					'primary' => true,
					'autocomplete' => true,
				]
			),
			'ENTITY_TYPE' => new StringField(
				'ENTITY_TYPE',
				[
					'required' => true,
					'validation' => function()
					{
						return[
							new LengthValidator(null, 25),
						];
					},
				]
			),
			'ENTITY_ID' => new IntegerField(
				'ENTITY_ID',
				[
					'required' => true,
				]
			),
			'DIRECTION' => new StringField(
				'DIRECTION',
				[
					'required' => true,
					'validation' => function()
					{
						return[
							new LengthValidator(null, 100),
						];
					},
				]
			),
			'NODE_ID' => new IntegerField(
				'NODE_ID',
				[
					'required' => true,
				]
			),
			'WITH_CHILD_NODES' => new BooleanField(
				'WITH_CHILD_NODES',
				[
					'values' => ['N', 'Y'],
					'default' => 'N',
				]
			),
			'POINTER' => new IntegerField(
				'POINTER',
				[
					'default' => 0,
				]
			),
			'STATUS' => new StringField(
				'STATUS',
				[
					'validation' => function()
					{
						return[
							new LengthValidator(null, 100),
						];
					},
				]
			),
			'IS_LOCKED' => new BooleanField(
				'IS_LOCKED',
				[
					'values' => ['N', 'Y'],
					'default' => 'N',
				]
			),
			'DATE_CREATE' => new DatetimeField(
				'DATE_CREATE',
				[
				]
			),
			'DATE_UPDATE' => new DatetimeField(
				'DATE_UPDATE',
				[
				]
			),
		];
	}
}