<?php
namespace Bitrix\Clouds;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields;

/**
 * Class CopyQueueTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> TIMESTAMP_X datetime mandatory
 * <li> OP string(1) mandatory
 * <li> SOURCE_BUCKET_ID int mandatory
 * <li> SOURCE_FILE_PATH string(500) mandatory
 * <li> TARGET_BUCKET_ID int mandatory
 * <li> TARGET_FILE_PATH string(500) mandatory
 * <li> FILE_SIZE int optional default -1
 * <li> FILE_POS int optional default 0
 * <li> FAIL_COUNTER int optional default 0
 * <li> STATUS bool ('N', 'Y') optional default 'Y'
 * <li> ERROR_MESSAGE string(500) optional
 * <li> SOURCE_BUCKET_ID reference to {@link \Bitrix\Clouds\FileBucketTable}
 * <li> TARGET_BUCKET_ID reference to {@link \Bitrix\Clouds\FileBucketTable}
 * </ul>
 *
 * @package Bitrix\Clouds
 **/

class CopyQueueTable extends DataManager
{
	const OP_COPY = 'C';
	const OP_RENAME = 'R';
	const OP_SYNC = 'S';

	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_clouds_copy_queue';
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
					'title' => Loc::getMessage('COPY_QUEUE_ENTITY_ID_FIELD'),
				]
			),
			new Fields\DatetimeField(
				'TIMESTAMP_X',
				[
					'required' => true,
					'title' => Loc::getMessage('COPY_QUEUE_ENTITY_TIMESTAMP_X_FIELD'),
				]
			),
			new Fields\StringField(
				'OP',
				[
					'required' => true,
					'validation' => [__CLASS__, 'validateOp'],
					'title' => Loc::getMessage('COPY_QUEUE_ENTITY_OP_FIELD'),
				]
			),
			new Fields\IntegerField(
				'SOURCE_BUCKET_ID',
				[
					'required' => true,
					'title' => Loc::getMessage('COPY_QUEUE_ENTITY_SOURCE_BUCKET_ID_FIELD'),
				]
			),
			new Fields\StringField(
				'SOURCE_FILE_PATH',
				[
					'required' => true,
					'validation' => [__CLASS__, 'validateSourceFilePath'],
					'title' => Loc::getMessage('COPY_QUEUE_ENTITY_SOURCE_FILE_PATH_FIELD'),
				]
			),
			new Fields\IntegerField(
				'TARGET_BUCKET_ID',
				[
					'required' => true,
					'title' => Loc::getMessage('COPY_QUEUE_ENTITY_TARGET_BUCKET_ID_FIELD'),
				]
			),
			new Fields\StringField(
				'TARGET_FILE_PATH',
				[
					'required' => true,
					'validation' => [__CLASS__, 'validateTargetFilePath'],
					'title' => Loc::getMessage('COPY_QUEUE_ENTITY_TARGET_FILE_PATH_FIELD'),
				]
			),
			new Fields\IntegerField(
				'FILE_SIZE',
				[
					'default' => -1,
					'title' => Loc::getMessage('COPY_QUEUE_ENTITY_FILE_SIZE_FIELD'),
				]
			),
			new Fields\IntegerField(
				'FILE_POS',
				[
					'default' => 0,
					'title' => Loc::getMessage('COPY_QUEUE_ENTITY_FILE_POS_FIELD'),
				]
			),
			new Fields\IntegerField(
				'FAIL_COUNTER',
				[
					'default' => 0,
					'title' => Loc::getMessage('COPY_QUEUE_ENTITY_FAIL_COUNTER_FIELD'),
				]
			),
			new Fields\BooleanField(
				'STATUS',
				[
					'values' => ['N', 'Y'],
					'default' => 'Y',
					'title' => Loc::getMessage('COPY_QUEUE_ENTITY_STATUS_FIELD'),
				]
			),
			new Fields\StringField(
				'ERROR_MESSAGE',
				[
					'validation' => [__CLASS__, 'validateErrorMessage'],
					'title' => Loc::getMessage('COPY_QUEUE_ENTITY_ERROR_MESSAGE_FIELD'),
				]
			),
			new Fields\Relations\Reference(
				'SOURCE_BUCKET',
				'\Bitrix\Clouds\FileBucket',
				['=this.SOURCE_BUCKET_ID' => 'ref.ID'],
				['join_type' => 'LEFT']
			),
			new Fields\Relations\Reference(
				'TARGET_BUCKET',
				'\Bitrix\Clouds\FileBucket',
				['=this.TARGET_BUCKET_ID' => 'ref.ID'],
				['join_type' => 'LEFT']
			),
		];
	}

	/**
	 * Returns validators for OP field.
	 *
	 * @return array
	 */
	public static function validateOp(): array
	{
		return [
			new Fields\Validators\LengthValidator(null, 1),
		];
	}

	/**
	 * Returns validators for SOURCE_FILE_PATH field.
	 *
	 * @return array
	 */
	public static function validateSourceFilePath(): array
	{
		return [
			new Fields\Validators\LengthValidator(null, 500),
		];
	}

	/**
	 * Returns validators for TARGET_FILE_PATH field.
	 *
	 * @return array
	 */
	public static function validateTargetFilePath(): array
	{
		return [
			new Fields\Validators\LengthValidator(null, 500),
		];
	}

	/**
	 * Returns validators for ERROR_MESSAGE field.
	 *
	 * @return array
	 */
	public static function validateErrorMessage(): array
	{
		return [
			new Fields\Validators\LengthValidator(null, 500),
		];
	}
}