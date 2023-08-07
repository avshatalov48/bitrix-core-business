<?php
namespace Bitrix\B24connector;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class ButtonsTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> ADD_DATE datetime optional
 * <li> ADD_BY int mandatory
 * <li> NAME string(255) optional
 * <li> SCRIPT string optional
 * </ul>
 *
 * @package Bitrix\B24connector
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Button_Query query()
 * @method static EO_Button_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_Button_Result getById($id)
 * @method static EO_Button_Result getList(array $parameters = array())
 * @method static EO_Button_Entity getEntity()
 * @method static \Bitrix\B24connector\EO_Button createObject($setDefaultValues = true)
 * @method static \Bitrix\B24connector\EO_Button_Collection createCollection()
 * @method static \Bitrix\B24connector\EO_Button wakeUpObject($row)
 * @method static \Bitrix\B24connector\EO_Button_Collection wakeUpCollection($rows)
 */

class ButtonTable extends Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_b24connector_buttons';
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
				'title' => Loc::getMessage('B24C_BT_ENTITY_ID_FIELD'),
			),
			'APP_ID' => array(
					'data_type' => 'integer',
					'required' => true,
					'title' => Loc::getMessage('B24C_BT_ENTITY_APP_ID_FIELD'),
			),
			'ADD_DATE' => array(
				'data_type' => 'datetime',
				'title' => Loc::getMessage('B24C_BT_ENTITY_ADD_DATE_FIELD'),
			),
			'ADD_BY' => array(
				'data_type' => 'integer',
				'required' => true,
				'title' => Loc::getMessage('B24C_BT_ENTITY_ADD_BY_FIELD'),
			),
			'NAME' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateName'),
				'title' => Loc::getMessage('B24C_BT_ENTITY_NAME_FIELD'),
			),
			'SCRIPT' => array(
				'data_type' => 'text',
				'title' => Loc::getMessage('B24C_BT_ENTITY_SCRIPT_FIELD'),
			),
		);
	}
	/**
	 * Returns validators for NAME field.
	 *
	 * @return array
	 */
	public static function validateName()
	{
		return array(
			new Entity\Validator\Length(null, 255),
		);
	}
}