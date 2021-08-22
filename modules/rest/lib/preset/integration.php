<?php

namespace Bitrix\Rest\Preset;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Entity\ReferenceField;

Loc::loadMessages(__FILE__);

/**
 * Class IntegrationTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> ELEMENT_CODE string(256) mandatory
 * <li> TITLE string(256) mandatory
 * <li> METHOD string(64) optional
 * <li> PASSWORD_ID int optional
 * <li> SCOPE string optional
 * <li> QUERY string optional
 * <li> OUTGOING_EVENTS string optional
 * <li> OUTGOING_NEEDED string(1) optional
 * <li> WIDGET_NEEDED string(1) optional
 * <li> WIDGET_HANDLER_URL string(2048) optional
 * <li> WIDGET_LIST string optional
 * <li> APPLICATION_TOKEN string(50) optional
 * <li> APPLICATION_NEEDED string(1) optional
 * <li> BOT_ID int optional
 * <li> BOT_HANDLER_URL string optional
 * </ul>
 *
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Integration_Query query()
 * @method static EO_Integration_Result getByPrimary($primary, array $parameters = array())
 * @method static EO_Integration_Result getById($id)
 * @method static EO_Integration_Result getList(array $parameters = array())
 * @method static EO_Integration_Entity getEntity()
 * @method static \Bitrix\Rest\Preset\EO_Integration createObject($setDefaultValues = true)
 * @method static \Bitrix\Rest\Preset\EO_Integration_Collection createCollection()
 * @method static \Bitrix\Rest\Preset\EO_Integration wakeUpObject($row)
 * @method static \Bitrix\Rest\Preset\EO_Integration_Collection wakeUpCollection($rows)
 */

class IntegrationTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_rest_integration';
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
				'title' => Loc::getMessage('INTEGRATION_ENTITY_ID_FIELD'),
			),
			'USER_ID' => array(
				'data_type' => 'integer',
				'validation' => array(__CLASS__, 'validateUser'),
				'title' => Loc::getMessage('INTEGRATION_ENTITY_USER_ID_FIELD'),
			),
			'ELEMENT_CODE' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateElementCode'),
				'title' => Loc::getMessage('INTEGRATION_ENTITY_ELEMENT_CODE_FIELD'),
			),
			'TITLE' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateTitle'),
				'title' => Loc::getMessage('INTEGRATION_ENTITY_TITLE_FIELD'),
			),
			'PASSWORD_ID' => array(
				'data_type' => 'integer',
				'validation' => array(__CLASS__, 'validatePassword'),
				'title' => Loc::getMessage('INTEGRATION_ENTITY_PASSWORD_ID_FIELD'),
			),
			'APP_ID' => array(
				'data_type' => 'integer',
				'validation' => array(__CLASS__, 'validateApp'),
				'title' => Loc::getMessage('INTEGRATION_ENTITY_APP_ID_FIELD'),
			),
			'SCOPE' => array(
				'data_type' => 'text',
				'save_data_modification' => function()
				{
					return array(
						function($value)
						{
							return is_array($value) ? implode(',', $value) : '';
						}
					);
				},
				'fetch_data_modification' => function()
				{
					return array(
						function($value)
						{
							return explode(',', $value);
						}
					);
				},
				'title' => Loc::getMessage('INTEGRATION_ENTITY_SCOPE_FIELD'),
			),
			'QUERY' => array(
				'data_type' => 'text',
				'serialized' => true,
				'title' => Loc::getMessage('INTEGRATION_ENTITY_QUERY_FIELD'),
			),
			'OUTGOING_EVENTS' => array(
				'data_type' => 'text',
				'save_data_modification' => function()
				{
					return array(
						function($value)
						{
							return is_array($value) ? implode(',', $value) : '';
						}
					);
				},
				'fetch_data_modification' => function()
				{
					return array(
						function($value)
						{
							return explode(',', $value);
						}
					);
				},
				'title' => Loc::getMessage('INTEGRATION_ENTITY_OUTGOING_EVENTS_FIELD'),
			),
			'OUTGOING_NEEDED' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateOutgoingQueryNeeded'),
				'title' => Loc::getMessage('INTEGRATION_ENTITY_OUTGOING_NEEDED_FIELD'),
			),
			'OUTGOING_HANDLER_URL' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateOutgoingHandlerUrl'),
				'title' => Loc::getMessage('INTEGRATION_ENTITY_OUTGOING_HANDLER_URL_FIELD'),
			),
			'WIDGET_NEEDED' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateWidgetNeeded'),
				'title' => Loc::getMessage('INTEGRATION_ENTITY_WIDGET_NEEDED_FIELD'),
			),
			'WIDGET_HANDLER_URL' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateWidgetHandlerUrl'),
				'title' => Loc::getMessage('INTEGRATION_ENTITY_WIDGET_HANDLER_URL_FIELD'),
			),
			'WIDGET_LIST' => array(
				'data_type' => 'text',
				'save_data_modification' => function()
				{
					return array(
						function($value)
						{
							return is_array($value) ? implode(',', $value) : '';
						}
					);
				},
				'fetch_data_modification' => function()
				{
					return array(
						function($value)
						{
							return explode(',', $value);
						}
					);
				},
				'title' => Loc::getMessage('INTEGRATION_ENTITY_WIDGET_LIST_FIELD'),
			),
			'APPLICATION_TOKEN' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateApplicationToken'),
				'title' => Loc::getMessage('INTEGRATION_ENTITY_APPLICATION_TOKEN_FIELD'),
			),
			'APPLICATION_NEEDED' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateApplicationNeeded'),
				'title' => Loc::getMessage('INTEGRATION_ENTITY_APPLICATION_NEEDED_FIELD'),
			),
			'APPLICATION_ONLY_API' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateApplicationOnlyApi'),
				'title' => Loc::getMessage('INTEGRATION_ENTITY_APPLICATION_ONLY_API_FIELD'),
			),
			'BOT_ID' => array(
				'data_type' => 'integer',
				'title' => Loc::getMessage('INTEGRATION_ENTITY_BOT_ID_FIELD'),
			),
			'BOT_HANDLER_URL' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateBotHandlerUrl'),
				'title' => Loc::getMessage('INTEGRATION_ENTITY_BOT_HANDLER_URL_FIELD'),
			),
			'USER' => new ReferenceField(
				'USER',
				'\Bitrix\Main\UserTable',
				array('=this.USER_ID' => 'ref.ID')
			),
		);
	}

	/**
	 * Returns validators for USER_ID field.
	 *
	 * @return array
	 */
	public static function validateUser()
	{
		return array(
			new Main\Entity\Validator\Length(null, 11),
		);
	}

	/**
	 * Returns validators for ELEMENT_CODE field.
	 *
	 * @return array
	 */
	public static function validateElementCode()
	{
		return array(
			new Main\Entity\Validator\Length(null, 256),
		);
	}

	/**
	 * Returns validators for TITLE field.
	 *
	 * @return array
	 */
	public static function validateTitle()
	{
		return array(
			new Main\Entity\Validator\Length(null, 256),
		);
	}

	/**
	 * Returns validators for PASSWORD_ID field.
	 *
	 * @return array
	 */
	public static function validatePassword()
	{
		return array(
			new Main\Entity\Validator\Length(null, 11),
		);
	}

	/**
	 * Returns validators for APP_ID field.
	 *
	 * @return array
	 */
	public static function validateApp()
	{
		return array(
			new Main\Entity\Validator\Length(null, 11),
		);
	}

	/**
	 * Returns validators for OUTGOING_NEEDED field.
	 *
	 * @return array
	 */
	public static function validateOutgoingQueryNeeded()
	{
		return array(
			new Main\Entity\Validator\Length(null, 1),
		);
	}
	/**
 * Returns validators for OUTGOING_HANDLER_URL field.
 *
 * @return array
 */
	public static function validateOutgoingHandlerUrl()
	{
		return array(
			new Main\Entity\Validator\Length(null, 2048),
		);
	}

	/**
	 * Returns validators for WIDGET_NEEDED field.
	 *
	 * @return array
	 */
	public static function validateWidgetNeeded()
	{
		return array(
			new Main\Entity\Validator\Length(null, 1),
		);
	}

	/**
	 * Returns validators for WIDGET_HANDLER_URL field.
	 *
	 * @return array
	 */
	public static function validateWidgetHandlerUrl()
	{
		return array(
			new Main\Entity\Validator\Length(null, 2048),
		);
	}

	/**
	 * Returns validators for APPLICATION_TOKEN field.
	 *
	 * @return array
	 */
	public static function validateApplicationToken()
	{
		return array(
			new Main\Entity\Validator\Length(null, 50),
		);
	}

	/**
	 * Returns validators for APPLICATION_NEEDED field.
	 *
	 * @return array
	 */
	public static function validateApplicationNeeded()
	{
		return array(
			new Main\Entity\Validator\Length(null, 1),
		);
	}
	/**
	 * Returns validators for APPLICATION_ONLY_API field.
	 *
	 * @return array
	 */
	public static function validateApplicationOnlyApi()
	{
		return array(
			new Main\Entity\Validator\Length(null, 1),
		);
	}

	/**
	 * Returns validators for BOT_HANDLER_URL field.
	 *
	 * @return array
	 */
	public static function validateBotHandlerUrl()
	{
		return array(
			new Main\Entity\Validator\Length(null, 2048),
		);
	}
}