<?php
namespace Bitrix\Clouds;

use Bitrix\Main,
	Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

/**
 * Class FileUploadTable
 * 
 * Fields:
 * <ul>
 * <li> ID string(32) mandatory </li>
 * <li> TIMESTAMP_X datetime mandatory default 'CURRENT_TIMESTAMP' </li>
 * <li> FILE_PATH string(500) mandatory </li>
 * <li> FILE_SIZE int optional </li>
 * <li> TMP_FILE string(500) optional </li>
 * <li> BUCKET_ID int mandatory </li>
 * <li> PART_SIZE int mandatory </li>
 * <li> PART_NO int mandatory </li>
 * <li> PART_FAIL_COUNTER int mandatory </li>
 * <li> NEXT_STEP string optional </li>
 * <li> BUCKET reference to {@link \Bitrix\Clouds\FileBucketTable} </li>
 * </ul>
 *
 * @package Bitrix\Clouds
 **/

class FileUploadTable extends Main\Entity\DataManager
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
		return array(
			'ID' => array(
				'data_type' => 'string',
				'primary' => true,
				'validation' => array(__CLASS__, 'validateId'),
				'title' => Loc::getMessage('FILE_UPLOAD_ENTITY_ID_FIELD'),
			),
			'TIMESTAMP_X' => array(
				'data_type' => 'datetime',
				'required' => true,
				'title' => Loc::getMessage('FILE_UPLOAD_ENTITY_TIMESTAMP_X_FIELD'),
			),
			'FILE_PATH' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateFilePath'),
				'title' => Loc::getMessage('FILE_UPLOAD_ENTITY_FILE_PATH_FIELD'),
			),
			'FILE_SIZE' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('FILE_UPLOAD_ENTITY_FILE_SIZE_FIELD'),
			),
			'TMP_FILE' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateTmpFile'),
				'title' => Loc::getMessage('FILE_UPLOAD_ENTITY_TMP_FILE_FIELD'),
			),
			'BUCKET_ID' => array(
				'data_type' => 'integer',
				'required' => true,
				'title' => Loc::getMessage('FILE_UPLOAD_ENTITY_BUCKET_ID_FIELD'),
			),
			'PART_SIZE' => array(
				'data_type' => 'integer',
				'required' => true,
				'title' => Loc::getMessage('FILE_UPLOAD_ENTITY_PART_SIZE_FIELD'),
			),
			'PART_NO' => array(
				'data_type' => 'integer',
				'required' => true,
				'title' => Loc::getMessage('FILE_UPLOAD_ENTITY_PART_NO_FIELD'),
			),
			'PART_FAIL_COUNTER' => array(
				'data_type' => 'integer',
				'required' => true,
				'title' => Loc::getMessage('FILE_UPLOAD_ENTITY_PART_FAIL_COUNTER_FIELD'),
			),
			'NEXT_STEP' => array(
				'data_type' => 'text',
				'title' => Loc::getMessage('FILE_UPLOAD_ENTITY_NEXT_STEP_FIELD'),
			),
			'BUCKET' => array(
				'data_type' => 'Bitrix\Clouds\FileBucket',
				'reference' => array('=this.BUCKET_ID' => 'ref.ID'),
			),
		);
	}
	/**
	 * Returns validators for ID field.
	 *
	 * @return array
	 */
	public static function validateId()
	{
		return array(
			new Main\Entity\Validator\Length(0, 32),
		);
	}
	/**
	 * Returns validators for FILE_PATH field.
	 *
	 * @return array
	 */
	public static function validateFilePath()
	{
		return array(
			new Main\Entity\Validator\Length(0, 500),
		);
	}
	/**
	 * Returns validators for TMP_FILE field.
	 *
	 * @return array
	 */
	public static function validateTmpFile()
	{
		return array(
			new Main\Entity\Validator\Length(0, 500),
		);
	}
}