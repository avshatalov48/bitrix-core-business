<?php
namespace Bitrix\Clouds;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields;

/**
 * Class FileBucketTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> ACTIVE bool ('N', 'Y') optional default 'Y'
 * <li> SORT int optional default 500
 * <li> READ_ONLY bool ('N', 'Y') optional default 'N'
 * <li> SERVICE_ID string(50) optional
 * <li> BUCKET string(63) optional
 * <li> LOCATION string(50) optional
 * <li> CNAME string(100) optional
 * <li> FILE_COUNT int optional default 0
 * <li> FILE_SIZE double optional default 0
 * <li> LAST_FILE_ID int optional
 * <li> PREFIX string(100) optional
 * <li> SETTINGS text optional
 * <li> FILE_RULES text optional
 * <li> FAILOVER_ACTIVE bool ('N', 'Y') optional default 'N'
 * <li> FAILOVER_BUCKET_ID int optional
 * <li> FAILOVER_COPY bool ('N', 'Y') optional default 'N'
 * <li> FAILOVER_DELETE bool ('N', 'Y') optional default 'N'
 * <li> FAILOVER_DELETE_DELAY int optional
 * <li> FAILOVER_BUCKET_ID reference to {@link \Bitrix\Clouds\FileBucketTable}
 * </ul>
 *
 * @package Bitrix\Clouds
 **/

class FileBucketTable extends DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_clouds_file_bucket';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return [
			new Fields\IntegerField(
				'ID',
				[
					'primary' => true,
					'autocomplete' => true,
					'title' => Loc::getMessage('FILE_BUCKET_ENTITY_ID_FIELD'),
				]
			),
			new Fields\BooleanField(
				'ACTIVE',
				[
					'values' => ['N', 'Y'],
					'default' => 'Y',
					'title' => Loc::getMessage('FILE_BUCKET_ENTITY_ACTIVE_FIELD'),
				]
			),
			new Fields\IntegerField(
				'SORT',
				[
					'default' => 500,
					'title' => Loc::getMessage('FILE_BUCKET_ENTITY_SORT_FIELD'),
				]
			),
			new Fields\BooleanField(
				'READ_ONLY',
				[
					'values' => ['N', 'Y'],
					'default' => 'N',
					'title' => Loc::getMessage('FILE_BUCKET_ENTITY_READ_ONLY_FIELD'),
				]
			),
			new Fields\StringField(
				'SERVICE_ID',
				[
					'validation' => [__CLASS__, 'validateServiceId'],
					'title' => Loc::getMessage('FILE_BUCKET_ENTITY_SERVICE_ID_FIELD'),
				]
			),
			new Fields\StringField(
				'BUCKET',
				[
					'validation' => [__CLASS__, 'validateBucket'],
					'title' => Loc::getMessage('FILE_BUCKET_ENTITY_BUCKET_FIELD'),
				]
			),
			new Fields\StringField(
				'LOCATION',
				[
					'validation' => [__CLASS__, 'validateLocation'],
					'title' => Loc::getMessage('FILE_BUCKET_ENTITY_LOCATION_FIELD'),
				]
			),
			new Fields\StringField(
				'CNAME',
				[
					'validation' => [__CLASS__, 'validateCname'],
					'title' => Loc::getMessage('FILE_BUCKET_ENTITY_CNAME_FIELD'),
				]
			),
			new Fields\IntegerField(
				'FILE_COUNT',
				[
					'default' => 0,
					'title' => Loc::getMessage('FILE_BUCKET_ENTITY_FILE_COUNT_FIELD'),
				]
			),
			new Fields\FloatField(
				'FILE_SIZE',
				[
					'default' => 0,
					'title' => Loc::getMessage('FILE_BUCKET_ENTITY_FILE_SIZE_FIELD'),
				]
			),
			new Fields\IntegerField(
				'LAST_FILE_ID',
				[
					'title' => Loc::getMessage('FILE_BUCKET_ENTITY_LAST_FILE_ID_FIELD'),
				]
			),
			new Fields\StringField(
				'PREFIX',
				[
					'validation' => [__CLASS__, 'validatePrefix'],
					'title' => Loc::getMessage('FILE_BUCKET_ENTITY_PREFIX_FIELD'),
				]
			),
			new Fields\TextField(
				'SETTINGS',
				[
					'title' => Loc::getMessage('FILE_BUCKET_ENTITY_SETTINGS_FIELD'),
				]
			),
			new Fields\TextField(
				'FILE_RULES',
				[
					'title' => Loc::getMessage('FILE_BUCKET_ENTITY_FILE_RULES_FIELD'),
				]
			),
			new Fields\BooleanField(
				'FAILOVER_ACTIVE',
				[
					'values' => ['N', 'Y'],
					'default' => 'N',
					'title' => Loc::getMessage('FILE_BUCKET_ENTITY_FAILOVER_ACTIVE_FIELD'),
				]
			),
			new Fields\IntegerField(
				'FAILOVER_BUCKET_ID',
				[
					'title' => Loc::getMessage('FILE_BUCKET_ENTITY_FAILOVER_BUCKET_ID_FIELD'),
				]
			),
			new Fields\BooleanField(
				'FAILOVER_COPY',
				[
					'values' => ['N', 'Y'],
					'default' => 'N',
					'title' => Loc::getMessage('FILE_BUCKET_ENTITY_FAILOVER_COPY_FIELD'),
				]
			),
			new Fields\BooleanField(
				'FAILOVER_DELETE',
				[
					'values' => ['N', 'Y'],
					'default' => 'N',
					'title' => Loc::getMessage('FILE_BUCKET_ENTITY_FAILOVER_DELETE_FIELD'),
				]
			),
			new Fields\IntegerField(
				'FAILOVER_DELETE_DELAY',
				[
					'title' => Loc::getMessage('FILE_BUCKET_ENTITY_FAILOVER_DELETE_DELAY_FIELD'),
				]
			),
			new Fields\Relations\Reference(
				'FAILOVER_BUCKET',
				'\Bitrix\Clouds\FileBucket',
				['=this.FAILOVER_BUCKET_ID' => 'ref.ID'],
				['join_type' => 'LEFT']
			),
		];
	}

	/**
	 * Returns validators for SERVICE_ID field.
	 *
	 * @return array
	 */
	public static function validateServiceId(): array
	{
		return [
			new Fields\Validators\LengthValidator(null, 50),
		];
	}

	/**
	 * Returns validators for BUCKET field.
	 *
	 * @return array
	 */
	public static function validateBucket(): array
	{
		return [
			new Fields\Validators\LengthValidator(null, 63),
		];
	}

	/**
	 * Returns validators for LOCATION field.
	 *
	 * @return array
	 */
	public static function validateLocation(): array
	{
		return [
			new Fields\Validators\LengthValidator(null, 50),
		];
	}

	/**
	 * Returns validators for CNAME field.
	 *
	 * @return array
	 */
	public static function validateCname(): array
	{
		return [
			new Fields\Validators\LengthValidator(null, 100),
		];
	}

	/**
	 * Returns validators for PREFIX field.
	 *
	 * @return array
	 */
	public static function validatePrefix(): array
	{
		return [
			new Fields\Validators\LengthValidator(null, 100),
		];
	}
}
