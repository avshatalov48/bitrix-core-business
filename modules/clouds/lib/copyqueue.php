<?php
namespace Bitrix\Clouds;

use Bitrix\Main,
	Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

/**
 * Class CopyQueueTable
 * 
 * Fields:
 * <ul>
 * <li> ID int mandatory </li>
 * <li> TIMESTAMP_X datetime mandatory default 'CURRENT_TIMESTAMP' </li>
 * <li> OP enum optional default 'C' </li>
 * <li> SOURCE_BUCKET_ID int mandatory </li>
 * <li> SOURCE_FILE_PATH string(500) mandatory </li>
 * <li> TARGET_BUCKET_ID int mandatory </li>
 * <li> TARGET_FILE_PATH string(500) mandatory </li>
 * <li> FILE_SIZE int mandatory default -1 </li>
 * <li> FILE_POS int mandatory </li>
 * <li> STATUS enum optional default 'Y' </li>
 * <li> ERROR_MESSAGE string(500) optional </li>
 * <li> SOURCE_BUCKET reference to {@link \Bitrix\Clouds\FileBucketTable} </li>
 * <li> TARGET_BUCKET reference to {@link \Bitrix\Clouds\FileBucketTable} </li>
 * </ul>
 *
 * @package Bitrix\Clouds
 **/

class CopyQueueTable extends Main\Entity\DataManager
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
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
				'title' => Loc::getMessage('COPY_QUEUE_ENTITY_ID_FIELD'),
			),
			'TIMESTAMP_X' => array(
				'data_type' => 'datetime',
				'required' => true,
				'title' => Loc::getMessage('COPY_QUEUE_ENTITY_TIMESTAMP_X_FIELD'),
			),
			'OP' => array(
				'data_type' => 'enum',
				'values' => array('C', 'R', 'S'),
				'title' => Loc::getMessage('COPY_QUEUE_ENTITY_OP_FIELD'),
			),
			'SOURCE_BUCKET_ID' => array(
				'data_type' => 'integer',
				'required' => true,
				'title' => Loc::getMessage('COPY_QUEUE_ENTITY_SOURCE_BUCKET_ID_FIELD'),
			),
			'SOURCE_FILE_PATH' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateSourceFilePath'),
				'title' => Loc::getMessage('COPY_QUEUE_ENTITY_SOURCE_FILE_PATH_FIELD'),
			),
			'TARGET_BUCKET_ID' => array(
				'data_type' => 'integer',
				'required' => true,
				'title' => Loc::getMessage('COPY_QUEUE_ENTITY_TARGET_BUCKET_ID_FIELD'),
			),
			'TARGET_FILE_PATH' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateTargetFilePath'),
				'title' => Loc::getMessage('COPY_QUEUE_ENTITY_TARGET_FILE_PATH_FIELD'),
			),
			'FILE_SIZE' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('COPY_QUEUE_ENTITY_FILE_SIZE_FIELD'),
			),
			'FILE_POS' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('COPY_QUEUE_ENTITY_FILE_POS_FIELD'),
			),
			'FAIL_COUNTER' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('COPY_QUEUE_ENTITY_FAIL_COUNTER_FIELD'),
			),
			'STATUS' => array(
				'data_type' => 'enum',
				'values' => array('Y', 'F'),
				'title' => Loc::getMessage('COPY_QUEUE_ENTITY_STATUS_FIELD'),
			),
			'ERROR_MESSAGE' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateErrorMessage'),
				'title' => Loc::getMessage('COPY_QUEUE_ENTITY_ERROR_MESSAGE_FIELD'),
			),
			'SOURCE_BUCKET' => array(
				'data_type' => 'Bitrix\\Clouds\\FileBucket',
				'reference' => array('=this.SOURCE_BUCKET_ID' => 'ref.ID'),
			),
			'TARGET_BUCKET' => array(
				'data_type' => 'Bitrix\\Clouds\\FileBucket',
				'reference' => array('=this.TARGET_BUCKET_ID' => 'ref.ID'),
			),
		);
	}
	/**
	 * Returns validators for SOURCE_FILE_PATH field.
	 *
	 * @return array
	 */
	public static function validateSourceFilePath()
	{
		return array(
			new Main\Entity\Validator\Length(0, 500),
		);
	}
	/**
	 * Returns validators for TARGET_FILE_PATH field.
	 *
	 * @return array
	 */
	public static function validateTargetFilePath()
	{
		return array(
			new Main\Entity\Validator\Length(0, 500),
		);
	}
	/**
	 * Returns validators for CONTENT_TYPE field.
	 *
	 * @return array
	 */
	public static function validateContentType()
	{
		return array(
			new Main\Entity\Validator\Length(0, 500),
		);
	}
	/**
	 * Returns validators for ERROR_MESSAGE field.
	 *
	 * @return array
	 */
	public static function validateErrorMessage()
	{
		return array(
			new Main\Entity\Validator\Length(0, 500),
		);
	}
}