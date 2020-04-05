<?php
namespace Bitrix\Im\Model;

use Bitrix\Main,
	Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

/**
 * Class AppTable
 * 
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> MODULE_ID string(50) mandatory
 * <li> BOT_ID int optional
 * <li> APP_ID string(128) optional
 * <li> CODE string(255) mandatory
 * <li> ICON_FILE_ID string(255) mandatory
 * <li> CONTEXT string(128) optional
 * <li> IFRAME string(255) optional
 * <li> IFRAME_WIDTH int optional
 * <li> IFRAME_HEIGHT int optional
 * <li> IFRAME_POPUP bool optional default 'N'
 * <li> JS string(255) optional
 * <li> HIDDEN bool optional default 'N'
 * <li> EXTRANET_SUPPORT bool optional default 'N'
 * <li> LIVECHAT_SUPPORT bool optional default 'N'
 * <li> CLASS string(255) optional
 * <li> METHOD_LANG_GET string(255) optional
 * </ul>
 *
 * @package Bitrix\Im
 **/

class AppTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_im_app';
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
				'autocomplete' => true
			),
			'MODULE_ID' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateModuleId'),
			),
			'BOT_ID' => array(
				'data_type' => 'integer',
			),
			'APP_ID' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateAppId'),
			),
			'HASH' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateHash'),
			),
			'REGISTERED' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateHash'),
				'default_value' => 'Y',
			),
			'CODE' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateIconCode'),
			),
			'ICON_FILE_ID' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateIconFileId'),
			),
			'CONTEXT' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateContext'),
			),
			'IFRAME' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateIframe'),
			),
			'IFRAME_WIDTH' => array(
				'data_type' => 'integer',
				'default_value' => '350',
			),
			'IFRAME_HEIGHT' => array(
				'data_type' => 'integer',
				'default_value' => '250',
			),
			'IFRAME_POPUP' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'default_value' => 'N',
			),
			'JS' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateJs'),
			),
			'EXTRANET_SUPPORT' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'default_value' => 'N',
			),
			'LIVECHAT_SUPPORT' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'default_value' => 'N',
			),
			'HIDDEN' => array(
				'data_type' => 'boolean',
				'values' => array('N', 'Y'),
				'default_value' => 'N',
			),
			'CLASS' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateClass'),
			),
			'METHOD_LANG_GET' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateMethodLangGet'),
			),
		);
	}
	/**
	 * Returns validators for MODULE_ID field.
	 *
	 * @return array
	 */
	public static function validateModuleId()
	{
		return array(
			new Main\Entity\Validator\Length(null, 50),
		);
	}
	/**
	 * Returns validators for APP_ID field.
	 *
	 * @return array
	 */
	public static function validateAppId()
	{
		return array(
			new Main\Entity\Validator\Length(null, 128),
		);
	}
	/**
	 * Returns validators for HASH field.
	 *
	 * @return array
	 */
	public static function validateHash()
	{
		return array(
			new Main\Entity\Validator\Length(null, 32),
		);
	}
	/**
	 * Returns validators for CODE field.
	 *
	 * @return array
	 */
	public static function validateIconCode()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}
	/**
	 * Returns validators for ICON_FILE_ID field.
	 *
	 * @return array
	 */
	public static function validateIconFileId()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}
	/**
	 * Returns validators for CONTEXT field.
	 *
	 * @return array
	 */
	public static function validateContext()
	{
		return array(
			new Main\Entity\Validator\Length(null, 128),
		);
	}
	/**
	 * Returns validators for IFRAME field.
	 *
	 * @return array
	 */
	public static function validateIframe()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}
	/**
	 * Returns validators for JS field.
	 *
	 * @return array
	 */
	public static function validateJs()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}
	/**
	 * Returns validators for CLASS field.
	 *
	 * @return array
	 */
	public static function validateClass()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}
	/**
	 * Returns validators for METHOD_LANG_GET field.
	 *
	 * @return array
	 */
	public static function validateMethodLangGet()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}
}