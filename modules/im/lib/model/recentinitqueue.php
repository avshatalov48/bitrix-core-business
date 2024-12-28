<?php
namespace Bitrix\Im\Model;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\BooleanField;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;

/**
 * Class RecentInitQueueTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> USER_ID int mandatory
 * <li> STAGE string(100) mandatory
 * <li> SOURCE string(100) mandatory
 * <li> SOURCE_ID int optional
 * <li> POINTER string(255) optional default ''
 * <li> STATUS string(100) optional default ''
 * <li> IS_LOCKED bool ('N', 'Y') optional default 'N'
 * <li> DATE_CREATE datetime optional
 * <li> DATE_UPDATE datetime optional
 * </ul>
 *
 * @package Bitrix\Im
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_RecentInitQueue_Query query()
 * @method static EO_RecentInitQueue_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_RecentInitQueue_Result getById($id)
 * @method static EO_RecentInitQueue_Result getList(array $parameters = [])
 * @method static EO_RecentInitQueue_Entity getEntity()
 * @method static \Bitrix\Im\Model\EO_RecentInitQueue createObject($setDefaultValues = true)
 * @method static \Bitrix\Im\Model\EO_RecentInitQueue_Collection createCollection()
 * @method static \Bitrix\Im\Model\EO_RecentInitQueue wakeUpObject($row)
 * @method static \Bitrix\Im\Model\EO_RecentInitQueue_Collection wakeUpCollection($rows)
 */

class RecentInitQueueTable extends DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_im_recent_init_queue';
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
			'USER_ID' => new IntegerField(
				'USER_ID',
				[
					'required' => true,
				]
			),
			'STAGE' => new StringField(
				'STAGE',
				[
					'required' => true,
					'validation' => function()
					{
						return [
							new LengthValidator(null, 100),
						];
					},
				]
			),
			'SOURCE' => new StringField(
				'SOURCE',
				[
					'required' => true,
					'validation' => function()
					{
						return [
							new LengthValidator(null, 100),
						];
					},
				]
			),
			'SOURCE_ID' => new IntegerField(
				'SOURCE_ID',
				[
				]
			),
			'POINTER' => new StringField(
				'POINTER',
				[
					'validation' => function()
					{
						return[
							new LengthValidator(null, 255),
						];
					},
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
