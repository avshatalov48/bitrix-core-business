<?php
namespace Bitrix\Clouds;

use Bitrix\Main,
	Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

/**
 * Class FileSaveTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> TIMESTAMP_X datetime mandatory default 'CURRENT_TIMESTAMP'
 * <li> BUCKET_ID int mandatory
 * <li> SUBDIR string(255) optional
 * <li> FILE_NAME string(255) mandatory
 * <li> EXTERNAL_ID string(50) optional
 * <li> FILE_SIZE int optional
 * </ul>
 *
 * @package Bitrix\Clouds
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_FileSave_Query query()
 * @method static EO_FileSave_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_FileSave_Result getById($id)
 * @method static EO_FileSave_Result getList(array $parameters = array())
 * @method static EO_FileSave_Entity getEntity()
 * @method static \Bitrix\Clouds\EO_FileSave createObject($setDefaultValues = true)
 * @method static \Bitrix\Clouds\EO_FileSave_Collection createCollection()
 * @method static \Bitrix\Clouds\EO_FileSave wakeUpObject($row)
 * @method static \Bitrix\Clouds\EO_FileSave_Collection wakeUpCollection($rows)
 */

class FileSaveTable extends Main\ORM\Data\DataManager
{
	private static $files = array();

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
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
				'title' => Loc::getMessage('FILE_SAVE_ENTITY_ID_FIELD'),
			),
			'TIMESTAMP_X' => array(
				'data_type' => 'datetime',
				'required' => true,
				'title' => Loc::getMessage('FILE_SAVE_ENTITY_TIMESTAMP_X_FIELD'),
			),
			'BUCKET_ID' => array(
				'data_type' => 'integer',
				'required' => true,
				'title' => Loc::getMessage('FILE_SAVE_ENTITY_BUCKET_ID_FIELD'),
			),
			'SUBDIR' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateSubdir'),
				'title' => Loc::getMessage('FILE_SAVE_ENTITY_SUBDIR_FIELD'),
			),
			'FILE_NAME' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateFileName'),
				'title' => Loc::getMessage('FILE_SAVE_ENTITY_FILE_NAME_FIELD'),
			),
			'EXTERNAL_ID' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateExternalId'),
				'title' => Loc::getMessage('FILE_SAVE_ENTITY_EXTERNAL_ID_FIELD'),
			),
			'FILE_SIZE' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('FILE_SAVE_ENTITY_FILE_SIZE_FIELD'),
			),
		);
	}
	/**
	 * Returns validators for SUBDIR field.
	 *
	 * @return array
	 */
	public static function validateSubdir()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}
	/**
	 * Returns validators for FILE_NAME field.
	 *
	 * @return array
	 */
	public static function validateFileName()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}
	/**
	 * Returns validators for EXTERNAL_ID field.
	 *
	 * @return array
	 */
	public static function validateExternalId()
	{
		return array(
			new Main\Entity\Validator\Length(null, 50),
		);
	}

	/**
	 * Creates and file save task object, saves it to the database and returns as a result.
	 *
	 * @param integer $bucketId Clouds storage bucket identifier.
	 * @param string $subDir File directory.
	 * @param string $fileName File name.
	 * @param string $externalId File external identifier.
	 *
	 * @return void
	 */
	public static function startFileOperation($bucketId, $subDir, $fileName, $externalId)
	{
		$key = $bucketId."|".$subDir."|".$fileName;
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
	 * @param integer $bucketId Clouds storage bucket identifier.
	 * @param string $subDir File directory.
	 * @param string $fileName File name.
	 * @param integer $fileSize File size.
	 *
	 * @return void
	 */
	public static function setFileSize($bucketId, $subDir, $fileName, $fileSize)
	{
		$key = $bucketId."|".$subDir."|".$fileName;
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
	 * @param integer $bucketId Clouds storage bucket identifier.
	 * @param string $subDir File directory.
	 * @param string $fileName File name.
	 *
	 * @return void
	 */
	public static function endFileOperation($bucketId, $subDir, $fileName)
	{
		$key = $bucketId."|".$subDir."|".$fileName;
		if (isset(self::$files[$key]))
		{
			$fileSave = self::$files[$key];
			$fileSave->delete();
			unset(self::$files[$key]);
		}
	}
}