<?php
namespace Bitrix\Clouds;

use Bitrix\Main,
	Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

/**
 * Class DeleteQueueTable
 * 
 * Fields:
 * <ul>
 * <li> ID int mandatory </li>
 * <li> TIMESTAMP_X datetime mandatory default 'CURRENT_TIMESTAMP' </li>
 * <li> BUCKET_ID int mandatory </li>
 * <li> FILE_PATH string(500) mandatory </li>
 * <li> BUCKET reference to {@link \Bitrix\Clouds\FileBucketTable} </li>
 * </ul>
 *
 * @package Bitrix\Clouds
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_DeleteQueue_Query query()
 * @method static EO_DeleteQueue_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_DeleteQueue_Result getById($id)
 * @method static EO_DeleteQueue_Result getList(array $parameters = array())
 * @method static EO_DeleteQueue_Entity getEntity()
 * @method static \Bitrix\Clouds\EO_DeleteQueue createObject($setDefaultValues = true)
 * @method static \Bitrix\Clouds\EO_DeleteQueue_Collection createCollection()
 * @method static \Bitrix\Clouds\EO_DeleteQueue wakeUpObject($row)
 * @method static \Bitrix\Clouds\EO_DeleteQueue_Collection wakeUpCollection($rows)
 */

class DeleteQueueTable extends Main\Entity\DataManager
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
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
				'title' => Loc::getMessage('DELETE_QUEUE_ENTITY_ID_FIELD'),
			),
			'TIMESTAMP_X' => array(
				'data_type' => 'datetime',
				'required' => true,
				'title' => Loc::getMessage('DELETE_QUEUE_ENTITY_TIMESTAMP_X_FIELD'),
			),
			'BUCKET_ID' => array(
				'data_type' => 'integer',
				'required' => true,
				'title' => Loc::getMessage('DELETE_QUEUE_ENTITY_BUCKET_ID_FIELD'),
			),
			'FILE_PATH' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateFilePath'),
				'title' => Loc::getMessage('DELETE_QUEUE_ENTITY_FILE_PATH_FIELD'),
			),
			'BUCKET' => array(
				'data_type' => 'Bitrix\Clouds\FileBucket',
				'reference' => array('=this.BUCKET_ID' => 'ref.ID'),
			),
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
}