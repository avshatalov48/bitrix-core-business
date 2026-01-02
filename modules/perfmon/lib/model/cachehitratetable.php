<?php

namespace Bitrix\Perfmon\Model;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\FloatField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;

/**
 * Class CacheHitrateTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> HASH string(255) mandatory
 * <li> CACHE_SIZE int optional default 0
 * <li> MODULE_ID string(255) optional
 * <li> BASE_DIR string(255) optional
 * <li> INIT_DIR string(255) optional
 * <li> FILE_NAME string(255) optional
 * <li> READ_COUNT int optional default 0
 * <li> WRITE_COUNT int optional default 0
 * <li> CLEAN_COUNT int optional default 0
 * <li> RATE int optional default 0
 * </ul>
 *
 * @package Bitrix\Perfmon
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_CacheHitrate_Query query()
 * @method static EO_CacheHitrate_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_CacheHitrate_Result getById($id)
 * @method static EO_CacheHitrate_Result getList(array $parameters = [])
 * @method static EO_CacheHitrate_Entity getEntity()
 * @method static \Bitrix\Perfmon\Model\EO_CacheHitrate createObject($setDefaultValues = true)
 * @method static \Bitrix\Perfmon\Model\EO_CacheHitrate_Collection createCollection()
 * @method static \Bitrix\Perfmon\Model\EO_CacheHitrate wakeUpObject($row)
 * @method static \Bitrix\Perfmon\Model\EO_CacheHitrate_Collection wakeUpCollection($rows)
 */

class CacheHitrateTable extends DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_perf_cache_hitrate';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return [
			new IntegerField(
				'ID',
				[
					'primary' => true,
					'autocomplete' => true,
				]
			),
			new StringField(
				'HASH',
				[
					'required' => true,
					'validation' => function ()
					{
						return[
							new LengthValidator(null, 255),
						];
					},
				]
			),
			new IntegerField(
				'CACHE_SIZE',
				[
					'default' => 0,
				]
			),
			new StringField(
				'MODULE_ID',
				[
					'validation' => function ()
					{
						return[
							new LengthValidator(null, 255),
						];
					},
				]
			),
			new StringField(
				'BASE_DIR',
				[
					'validation' => function ()
					{
						return[
							new LengthValidator(null, 255),
						];
					},
				]
			),
			new StringField(
				'INIT_DIR',
				[
					'validation' => function ()
					{
						return[
							new LengthValidator(null, 255),
						];
					},
				]
			),
			new StringField(
				'FILE_NAME',
				[
					'validation' => function ()
					{
						return[
							new LengthValidator(null, 255),
						];
					},
				]
			),
			new IntegerField(
				'READ_COUNT',
				[
					'default' => 0,
				]
			),
			new IntegerField(
				'WRITE_COUNT',
				[
					'default' => 0,
				]
			),
			new IntegerField(
				'CLEAN_COUNT',
				[
					'default' => 0,
				]
			),
			new FloatField(
				'RATE',
				[
					'default' => 0,
					'size' => 1,
				]
			),
		];
	}
}
