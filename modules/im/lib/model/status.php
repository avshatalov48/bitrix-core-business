<?php
namespace Bitrix\Im\Model;

use Bitrix\Main\Entity;
use Bitrix\Main\ORM\Data\Internal\MergeTrait;

/**
 * Class StatusTable
 *
 * Fields:
 * <ul>
 * <li> USER_ID int mandatory
 * <li> STATUS string(50) optional default 'online'
 * <li> STATUS_TEXT string(255) optional
 * <li> IDLE datetime optional default 0
 * <li> DESKTOP_LAST_DATE datetime optional default 0
 * <li> MOBILE_LAST_DATE datetime optional default 0
 * <li> EVENT_ID int optional default 0
 * <li> EVENT_UNTIL_DATE datetime optional default 0
 * </ul>
 *
 * @package Bitrix\Im
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Status_Query query()
 * @method static EO_Status_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_Status_Result getById($id)
 * @method static EO_Status_Result getList(array $parameters = array())
 * @method static EO_Status_Entity getEntity()
 * @method static \Bitrix\Im\Model\EO_Status createObject($setDefaultValues = true)
 * @method static \Bitrix\Im\Model\EO_Status_Collection createCollection()
 * @method static \Bitrix\Im\Model\EO_Status wakeUpObject($row)
 * @method static \Bitrix\Im\Model\EO_Status_Collection wakeUpCollection($rows)
 */

class StatusTable extends Entity\DataManager
{
	use MergeTrait;

	/**
	 * Returns path to the file which contains definition of the class.
	 *
	 * @return string
	 */
	public static function getFilePath()
	{
		return __FILE__;
	}

	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_im_status';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'USER_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				//'title' => Loc::getMessage('STATUS_ENTITY_USER_ID_FIELD'),
			),
			'COLOR' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateColor'),
				//'title' => Loc::getMessage('STATUS_ENTITY_COLOR_FIELD'),
			),
			'STATUS' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateStatus'),
				//'title' => Loc::getMessage('STATUS_ENTITY_STATUS_FIELD'),
				'default_value' => 'online',
			),
			'STATUS_TEXT' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateStatusText'),
			),
			'IDLE' => array(
				'data_type' => 'datetime',
				//'title' => Loc::getMessage('STATUS_ENTITY_IDLE_FIELD'),
			),
			'DESKTOP_LAST_DATE' => array(
				'data_type' => 'datetime',
				//'title' => Loc::getMessage('STATUS_ENTITY_DESKTOP_LAST_DATE_FIELD'),
			),
			'MOBILE_LAST_DATE' => array(
				'data_type' => 'datetime',
				//'title' => Loc::getMessage('STATUS_ENTITY_MOBILE_LAST_DATE_FIELD'),
			),
			'EVENT_ID' => array(
				'data_type' => 'integer',
				//'title' => Loc::getMessage('STATUS_ENTITY_EVENT_ID_FIELD'),
			),
			'EVENT_UNTIL_DATE' => array(
				'data_type' => 'datetime',
				//'title' => Loc::getMessage('STATUS_ENTITY_EVENT_UNTIL_DATE_FIELD'),
			),
			'USER' => array(
				'data_type' => 'Bitrix\Main\User',
				'reference' => array('=this.USER_ID' => 'ref.ID'),
			),
		);
	}
	/**
	 * Returns validators for STATUS field.
	 *
	 * @return array
	 */
	public static function validateStatus()
	{
		return array(
			new Entity\Validator\Length(null, 50),
		);
	}
	/**
	 * Returns validators for STATUS_TEXT field.
	 *
	 * @return array
	 */
	public static function validateStatusText()
	{
		return array(
			new Entity\Validator\Length(null, 255),
		);
	}

	public static function validateColor()
	{
		return array(
			new Entity\Validator\Length(null, 255),
		);
	}
}