<?php
namespace Bitrix\Clouds;

use Bitrix\Main,
	Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

/**
 * Class FileResizeTable
 * 
 * Fields:
 * <ul>
 * <li> ID int mandatory </li>
 * <li> TIMESTAMP_X datetime mandatory default 'CURRENT_TIMESTAMP' </li>
 * <li> ERROR_CODE string(1) mandatory </li>
 * <li> FILE_ID int optional </li>
 * <li> PARAMS string optional </li>
 * <li> FROM_PATH string(500) optional </li>
 * <li> TO_PATH string(500) optional </li>
 * <li> FILE reference to {@link \Bitrix\Main\FileTable} </li>
 * </ul>
 *
 * @package Bitrix\Clouds
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_FileResize_Query query()
 * @method static EO_FileResize_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_FileResize_Result getById($id)
 * @method static EO_FileResize_Result getList(array $parameters = array())
 * @method static EO_FileResize_Entity getEntity()
 * @method static \Bitrix\Clouds\EO_FileResize createObject($setDefaultValues = true)
 * @method static \Bitrix\Clouds\EO_FileResize_Collection createCollection()
 * @method static \Bitrix\Clouds\EO_FileResize wakeUpObject($row)
 * @method static \Bitrix\Clouds\EO_FileResize_Collection wakeUpCollection($rows)
 */

class FileResizeTable extends Main\Entity\DataManager
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
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
				'title' => Loc::getMessage('FILE_RESIZE_ENTITY_ID_FIELD'),
			),
			'TIMESTAMP_X' => array(
				'data_type' => 'datetime',
				'required' => true,
				'title' => Loc::getMessage('FILE_RESIZE_ENTITY_TIMESTAMP_X_FIELD'),
			),
			'ERROR_CODE' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateErrorCode'),
				'title' => Loc::getMessage('FILE_RESIZE_ENTITY_ERROR_CODE_FIELD'),
			),
			'FILE_ID' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('FILE_RESIZE_ENTITY_FILE_ID_FIELD'),
			),
			'PARAMS' => array(
				'data_type' => 'text',
				'title' => Loc::getMessage('FILE_RESIZE_ENTITY_PARAMS_FIELD'),
			),
			'FROM_PATH' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateFromPath'),
				'title' => Loc::getMessage('FILE_RESIZE_ENTITY_FROM_PATH_FIELD'),
			),
			'TO_PATH' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateToPath'),
				'title' => Loc::getMessage('FILE_RESIZE_ENTITY_TO_PATH_FIELD'),
			),
			'FILE' => array(
				'data_type' => 'Bitrix\Main\File',
				'reference' => array('=this.FILE_ID' => 'ref.ID'),
			),
		);
	}
	/**
	 * Returns validators for ERROR_CODE field.
	 *
	 * @return array
	 */
	public static function validateErrorCode()
	{
		return array(
			new Main\Entity\Validator\Length(0, 1),
		);
	}
	/**
	 * Returns validators for FROM_PATH field.
	 *
	 * @return array
	 */
	public static function validateFromPath()
	{
		return array(
			new Main\Entity\Validator\Length(0, 500),
		);
	}
	/**
	 * Returns validators for TO_PATH field.
	 *
	 * @return array
	 */
	public static function validateToPath()
	{
		return array(
			new Main\Entity\Validator\Length(0, 500),
		);
	}
}