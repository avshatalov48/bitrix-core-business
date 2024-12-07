<?php
namespace Bitrix\Clouds;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields;

/**
 * Class FileUploadTable
 *
 * Fields:
 * <ul>
 * <li> ID string(32) mandatory
 * <li> TIMESTAMP_X datetime mandatory
 * <li> FILE_PATH string(500) mandatory
 * <li> FILE_SIZE int optional
 * <li> TMP_FILE string(500) optional
 * <li> BUCKET_ID int mandatory
 * <li> PART_SIZE int mandatory
 * <li> PART_NO int mandatory
 * <li> PART_FAIL_COUNTER int mandatory
 * <li> NEXT_STEP text optional
 * <li> BUCKET_ID reference to {@link \Bitrix\Clouds\CloudsFileBucketTable}
 * </ul>
 *
 * @package Bitrix\Clouds
 **/

class FileUploadTable extends DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_clouds_file_upload';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return [
			new Fields\StringField(
				'ID',
				[
					'primary' => true,
					'validation' => [__CLASS__, 'validateId'],
					'title' => Loc::getMessage('FILE_UPLOAD_ENTITY_ID_FIELD'),
				]
			),
			new Fields\DatetimeField(
				'TIMESTAMP_X',
				[
					'required' => true,
					'title' => Loc::getMessage('FILE_UPLOAD_ENTITY_TIMESTAMP_X_FIELD'),
				]
			),
			new Fields\StringField(
				'FILE_PATH',
				[
					'required' => true,
					'validation' => [__CLASS__, 'validateFilePath'],
					'title' => Loc::getMessage('FILE_UPLOAD_ENTITY_FILE_PATH_FIELD'),
				]
			),
			new Fields\IntegerField(
				'FILE_SIZE',
				[
					'title' => Loc::getMessage('FILE_UPLOAD_ENTITY_FILE_SIZE_FIELD'),
					'size' => 8,
				]
			),
			new Fields\StringField(
				'TMP_FILE',
				[
					'validation' => [__CLASS__, 'validateTmpFile'],
					'title' => Loc::getMessage('FILE_UPLOAD_ENTITY_TMP_FILE_FIELD'),
				]
			),
			new Fields\IntegerField(
				'BUCKET_ID',
				[
					'required' => true,
					'title' => Loc::getMessage('FILE_UPLOAD_ENTITY_BUCKET_ID_FIELD'),
				]
			),
			new Fields\IntegerField(
				'PART_SIZE',
				[
					'required' => true,
					'title' => Loc::getMessage('FILE_UPLOAD_ENTITY_PART_SIZE_FIELD'),
				]
			),
			new Fields\IntegerField(
				'PART_NO',
				[
					'required' => true,
					'title' => Loc::getMessage('FILE_UPLOAD_ENTITY_PART_NO_FIELD'),
				]
			),
			new Fields\IntegerField(
				'PART_FAIL_COUNTER',
				[
					'required' => true,
					'title' => Loc::getMessage('FILE_UPLOAD_ENTITY_PART_FAIL_COUNTER_FIELD'),
				]
			),
			new Fields\TextField(
				'NEXT_STEP',
				[
					'title' => Loc::getMessage('FILE_UPLOAD_ENTITY_NEXT_STEP_FIELD'),
				]
			),
			new Fields\Relations\Reference(
				'BUCKET',
				'\Bitrix\Clouds\CloudsFileBucket',
				['=this.BUCKET_ID' => 'ref.ID'],
				['join_type' => 'LEFT']
			),
		];
	}

	/**
	 * Returns validators for ID field.
	 *
	 * @return array
	 */
	public static function validateId(): array
	{
		return [
			new Fields\Validators\LengthValidator(null, 32),
		];
	}

	/**
	 * Returns validators for FILE_PATH field.
	 *
	 * @return array
	 */
	public static function validateFilePath(): array
	{
		return [
			new Fields\Validators\LengthValidator(null, 500),
		];
	}

	/**
	 * Returns validators for TMP_FILE field.
	 *
	 * @return array
	 */
	public static function validateTmpFile(): array
	{
		return [
			new Fields\Validators\LengthValidator(null, 500),
		];
	}
}
