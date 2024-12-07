<?php
namespace Bitrix\Clouds;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields;

/**
 * Class FileSaveTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> TIMESTAMP_X datetime mandatory
 * <li> BUCKET_ID int mandatory
 * <li> SUBDIR string(255) optional
 * <li> FILE_NAME string(255) mandatory
 * <li> EXTERNAL_ID string(50) optional
 * <li> FILE_SIZE int optional
 * <li> BUCKET_ID reference to {@link \Bitrix\Clouds\CloudsFileBucketTable}
 * </ul>
 *
 * @package Bitrix\Clouds
 **/

class FileSaveTable extends DataManager
{
	private static $files = [];

	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_clouds_file_save';
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
					'title' => Loc::getMessage('FILE_SAVE_ENTITY_ID_FIELD'),
				]
			),
			new Fields\DatetimeField(
				'TIMESTAMP_X',
				[
					'required' => true,
					'title' => Loc::getMessage('FILE_SAVE_ENTITY_TIMESTAMP_X_FIELD'),
				]
			),
			new Fields\IntegerField(
				'BUCKET_ID',
				[
					'required' => true,
					'title' => Loc::getMessage('FILE_SAVE_ENTITY_BUCKET_ID_FIELD'),
				]
			),
			new Fields\StringField(
				'SUBDIR',
				[
					'validation' => [__CLASS__, 'validateSubdir'],
					'title' => Loc::getMessage('FILE_SAVE_ENTITY_SUBDIR_FIELD'),
				]
			),
			new Fields\StringField(
				'FILE_NAME',
				[
					'required' => true,
					'validation' => [__CLASS__, 'validateFileName'],
					'title' => Loc::getMessage('FILE_SAVE_ENTITY_FILE_NAME_FIELD'),
				]
			),
			new Fields\StringField(
				'EXTERNAL_ID',
				[
					'validation' => [__CLASS__, 'validateExternalId'],
					'title' => Loc::getMessage('FILE_SAVE_ENTITY_EXTERNAL_ID_FIELD'),
				]
			),
			new Fields\IntegerField(
				'FILE_SIZE',
				[
					'title' => Loc::getMessage('FILE_SAVE_ENTITY_FILE_SIZE_FIELD'),
					'size' => 8,
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
	 * Returns validators for SUBDIR field.
	 *
	 * @return array
	 */
	public static function validateSubdir(): array
	{
		return [
			new Fields\Validators\LengthValidator(null, 255),
		];
	}

	/**
	 * Returns validators for FILE_NAME field.
	 *
	 * @return array
	 */
	public static function validateFileName(): array
	{
		return [
			new Fields\Validators\LengthValidator(null, 255),
		];
	}

	/**
	 * Returns validators for EXTERNAL_ID field.
	 *
	 * @return array
	 */
	public static function validateExternalId(): array
	{
		return [
			new Fields\Validators\LengthValidator(null, 50),
		];
	}

	/**
	 * Creates and file save task object, saves it to the database and returns as a result.
	 *
	 * @param int $bucketId Clouds storage bucket identifier.
	 * @param string $subDir File directory.
	 * @param string $fileName File name.
	 * @param string $externalId File external identifier.
	 *
	 * @return void
	 */
	public static function startFileOperation($bucketId, $subDir, $fileName, $externalId)
	{
		$key = $bucketId . '|' . $subDir . '|' . $fileName;
		$fileSave = \Bitrix\Clouds\FileSaveTable::createObject();
		$fileSave->setTimestampX(new \Bitrix\Main\Type\DateTime());
		$fileSave->setBucketId($bucketId);
		$fileSave->setSubdir($subDir);
		$fileSave->setFileName($fileName);
		$fileSave->setExternalId($externalId);
		$fileSave->setFileSize(-1);
		$saveResult = $fileSave->save();
		if ($saveResult->isSuccess())
		{
			self::$files[$key] = $fileSave;
		}
	}

	/**
	 * Assignes the file size to a file save task object.
	 *
	 * @param int $bucketId Clouds storage bucket identifier.
	 * @param string $subDir File directory.
	 * @param string $fileName File name.
	 * @param int $fileSize File size.
	 *
	 * @return void
	 */
	public static function setFileSize($bucketId, $subDir, $fileName, $fileSize)
	{
		$key = $bucketId . '|' . $subDir . '|' . $fileName;
		if (isset(self::$files[$key]))
		{
			$fileSave = self::$files[$key];
			$fileSave->setFileSize($fileSize);
			$fileSave->save();
		}
	}

	/**
	 * Deletes the file save task object from the database.
	 *
	 * @param int $bucketId Clouds storage bucket identifier.
	 * @param string $subDir File directory.
	 * @param string $fileName File name.
	 *
	 * @return void
	 */
	public static function endFileOperation($bucketId, $subDir, $fileName)
	{
		$key = $bucketId . '|' . $subDir . '|' . $fileName;
		if (isset(self::$files[$key]))
		{
			$fileSave = self::$files[$key];
			$fileSave->delete();
			unset(self::$files[$key]);
		}
	}
}
