<?php
namespace Bitrix\Clouds;

use Bitrix\Main,
	Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

/**
 * Class FileBucketTable
 * 
 * Fields:
 * <ul>
 * <li> ID int mandatory </li>
 * <li> ACTIVE bool optional default 'Y' </li>
 * <li> SORT int optional default 500 </li>
 * <li> READ_ONLY bool optional default 'N' </li>
 * <li> SERVICE_ID string(50) optional </li>
 * <li> BUCKET string(63) optional </li>
 * <li> LOCATION string(50) optional </li>
 * <li> CNAME string(100) optional </li>
 * <li> FILE_COUNT int optional </li>
 * <li> FILE_SIZE double optional </li>
 * <li> LAST_FILE_ID int optional </li>
 * <li> PREFIX string(100) optional </li>
 * <li> SETTINGS string optional </li>
 * <li> FILE_RULES string optional </li>
 * <li> FAILOVER_ACTIVE bool optional default 'N' </li>
 * <li> FAILOVER_BUCKET_ID int optional </li>
 * <li> FAILOVER_COPY bool optional default 'N' </li>
 * <li> FAILOVER_DELETE bool optional default 'N' </li>
 * <li> FAILOVER_DELETE_DELAY int optional </li>
 * <li> FAILOVER_BUCKET reference to {@link \Bitrix\Clouds\FileBucketTable} </li>
 * </ul>
 *
 * @package Bitrix\Clouds
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_FileBucket_Query query()
 * @method static EO_FileBucket_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_FileBucket_Result getById($id)
 * @method static EO_FileBucket_Result getList(array $parameters = array())
 * @method static EO_FileBucket_Entity getEntity()
 * @method static \Bitrix\Clouds\EO_FileBucket createObject($setDefaultValues = true)
 * @method static \Bitrix\Clouds\EO_FileBucket_Collection createCollection()
 * @method static \Bitrix\Clouds\EO_FileBucket wakeUpObject($row)
 * @method static \Bitrix\Clouds\EO_FileBucket_Collection wakeUpCollection($rows)
 */

class FileBucketTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_clouds_file_bucket';
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
				'title' => Loc::getMessage('FILE_BUCKET_ENTITY_ID_FIELD'),
			),
			'ACTIVE' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('FILE_BUCKET_ENTITY_ACTIVE_FIELD'),
			),
			'SORT' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('FILE_BUCKET_ENTITY_SORT_FIELD'),
			),
			'READ_ONLY' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('FILE_BUCKET_ENTITY_READ_ONLY_FIELD'),
			),
			'SERVICE_ID' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateServiceId'),
				'title' => Loc::getMessage('FILE_BUCKET_ENTITY_SERVICE_ID_FIELD'),
			),
			'BUCKET' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateBucket'),
				'title' => Loc::getMessage('FILE_BUCKET_ENTITY_BUCKET_FIELD'),
			),
			'LOCATION' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateLocation'),
				'title' => Loc::getMessage('FILE_BUCKET_ENTITY_LOCATION_FIELD'),
			),
			'CNAME' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateCname'),
				'title' => Loc::getMessage('FILE_BUCKET_ENTITY_CNAME_FIELD'),
			),
			'FILE_COUNT' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('FILE_BUCKET_ENTITY_FILE_COUNT_FIELD'),
			),
			'FILE_SIZE' => array(
				'data_type' => 'float',
				'title' => Loc::getMessage('FILE_BUCKET_ENTITY_FILE_SIZE_FIELD'),
			),
			'LAST_FILE_ID' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('FILE_BUCKET_ENTITY_LAST_FILE_ID_FIELD'),
			),
			'PREFIX' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validatePrefix'),
				'title' => Loc::getMessage('FILE_BUCKET_ENTITY_PREFIX_FIELD'),
			),
			'SETTINGS' => array(
				'data_type' => 'text',
				'title' => Loc::getMessage('FILE_BUCKET_ENTITY_SETTINGS_FIELD'),
			),
			'FILE_RULES' => array(
				'data_type' => 'text',
				'title' => Loc::getMessage('FILE_BUCKET_ENTITY_FILE_RULES_FIELD'),
			),
			'FAILOVER_ACTIVE' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('FILE_BUCKET_ENTITY_FAILOVER_ACTIVE_FIELD'),
			),
			'FAILOVER_BUCKET_ID' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('FILE_BUCKET_ENTITY_FAILOVER_BUCKET_ID_FIELD'),
			),
			'FAILOVER_COPY' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('FILE_BUCKET_ENTITY_FAILOVER_COPY_FIELD'),
			),
			'FAILOVER_DELETE' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'title' => Loc::getMessage('FILE_BUCKET_ENTITY_FAILOVER_DELETE_FIELD'),
			),
			'FAILOVER_DELETE_DELAY' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('FILE_BUCKET_ENTITY_FAILOVER_DELETE_DELAY_FIELD'),
			),
			'FAILOVER_BUCKET' => array(
				'data_type' => 'Bitrix\Clouds\FileBucket',
				'reference' => array('=this.FAILOVER_BUCKET_ID' => 'ref.ID'),
			),
		);
	}
	/**
	 * Returns validators for SERVICE_ID field.
	 *
	 * @return array
	 */
	public static function validateServiceId()
	{
		return array(
			new Main\Entity\Validator\Length(0, 50),
		);
	}
	/**
	 * Returns validators for BUCKET field.
	 *
	 * @return array
	 */
	public static function validateBucket()
	{
		return array(
			new Main\Entity\Validator\Length(0, 63),
		);
	}
	/**
	 * Returns validators for LOCATION field.
	 *
	 * @return array
	 */
	public static function validateLocation()
	{
		return array(
			new Main\Entity\Validator\Length(0, 50),
		);
	}
	/**
	 * Returns validators for CNAME field.
	 *
	 * @return array
	 */
	public static function validateCname()
	{
		return array(
			new Main\Entity\Validator\Length(0, 100),
		);
	}
	/**
	 * Returns validators for PREFIX field.
	 *
	 * @return array
	 */
	public static function validatePrefix()
	{
		return array(
			new Main\Entity\Validator\Length(0, 100),
		);
	}
}