<?php
namespace Bitrix\Clouds;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields;

/**
 * Class FileResizeTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> TIMESTAMP_X datetime mandatory
 * <li> ERROR_CODE int optional default 0
 * <li> FILE_ID int optional
 * <li> PARAMS text optional
 * <li> FROM_PATH string(500) optional
 * <li> TO_PATH string(500) optional
 * <li> FILE_ID reference to {@link \Bitrix\Main\FileTable}
 * </ul>
 *
 * @package Bitrix\Clouds
 **/

class FileResizeTable extends DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_clouds_file_resize';
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
					'title' => Loc::getMessage('FILE_RESIZE_ENTITY_ID_FIELD'),
				]
			),
			new Fields\DatetimeField(
				'TIMESTAMP_X',
				[
					'required' => true,
					'title' => Loc::getMessage('FILE_RESIZE_ENTITY_TIMESTAMP_X_FIELD'),
				]
			),
			new Fields\IntegerField(
				'ERROR_CODE',
				[
					'default' => 0,
					'title' => Loc::getMessage('FILE_RESIZE_ENTITY_ERROR_CODE_FIELD'),
				]
			),
			new Fields\IntegerField(
				'FILE_ID',
				[
					'title' => Loc::getMessage('FILE_RESIZE_ENTITY_FILE_ID_FIELD'),
				]
			),
			new Fields\TextField(
				'PARAMS',
				[
					'title' => Loc::getMessage('FILE_RESIZE_ENTITY_PARAMS_FIELD'),
				]
			),
			new Fields\StringField(
				'FROM_PATH',
				[
					'validation' => [__CLASS__, 'validateFromPath'],
					'title' => Loc::getMessage('FILE_RESIZE_ENTITY_FROM_PATH_FIELD'),
				]
			),
			new Fields\StringField(
				'TO_PATH',
				[
					'validation' => [__CLASS__, 'validateToPath'],
					'title' => Loc::getMessage('FILE_RESIZE_ENTITY_TO_PATH_FIELD'),
				]
			),
			new Fields\Relations\Reference(
				'FILE',
				'\Bitrix\Main\File',
				['=this.FILE_ID' => 'ref.ID'],
				['join_type' => 'LEFT']
			),
		];
	}

	/**
	 * Returns validators for FROM_PATH field.
	 *
	 * @return array
	 */
	public static function validateFromPath(): array
	{
		return [
			new Fields\Validators\LengthValidator(null, 500),
		];
	}

	/**
	 * Returns validators for TO_PATH field.
	 *
	 * @return array
	 */
	public static function validateToPath(): array
	{
		return [
			new Fields\Validators\LengthValidator(null, 500),
		];
	}
}
