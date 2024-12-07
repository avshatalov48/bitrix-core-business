<?php
namespace Bitrix\Clouds;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields;

/**
 * Class DeleteQueueTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> TIMESTAMP_X datetime mandatory
 * <li> BUCKET_ID int mandatory
 * <li> FILE_PATH string(500) mandatory
 * <li> BUCKET_ID reference to {@link \Bitrix\Clouds\FileBucketTable}
 * </ul>
 *
 * @package Bitrix\Clouds
 **/

class DeleteQueueTable extends DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_clouds_delete_queue';
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
					'title' => Loc::getMessage('DELETE_QUEUE_ENTITY_ID_FIELD'),
				]
			),
			new Fields\DatetimeField(
				'TIMESTAMP_X',
				[
					'required' => true,
					'title' => Loc::getMessage('DELETE_QUEUE_ENTITY_TIMESTAMP_X_FIELD'),
				]
			),
			new Fields\IntegerField(
				'BUCKET_ID',
				[
					'required' => true,
					'title' => Loc::getMessage('DELETE_QUEUE_ENTITY_BUCKET_ID_FIELD'),
				]
			),
			new Fields\StringField(
				'FILE_PATH',
				[
					'required' => true,
					'validation' => [__CLASS__, 'validateFilePath'],
					'title' => Loc::getMessage('DELETE_QUEUE_ENTITY_FILE_PATH_FIELD'),
				]
			),
			new Fields\Relations\Reference(
				'BUCKET',
				'\Bitrix\Clouds\FileBucket',
				['=this.BUCKET_ID' => 'ref.ID'],
				['join_type' => 'LEFT']
			),
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
}
