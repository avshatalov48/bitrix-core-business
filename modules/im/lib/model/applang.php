<?php
namespace Bitrix\Im\Model;

use Bitrix\Main,
	Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

/**
 * Class AppLangTable
 * 
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> APP_ID int mandatory
 * <li> LANGUAGE_ID string(2) mandatory
 * <li> TITLE string(255) optional
 * <li> DESCRIPTION string(255) optional
 * <li> COPYRIGHT string(255) optional
 * </ul>
 *
 * @package Bitrix\Im
 **/

class AppLangTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_im_app_lang';
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
			),
			'APP_ID' => array(
				'data_type' => 'integer',
				'required' => true,
			),
			'LANGUAGE_ID' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateLanguageId'),
			),
			'TITLE' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateTitle'),
			),
			'DESCRIPTION' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateDescription'),
			),
			'COPYRIGHT' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateCopyright'),
			),
		);
	}
	/**
	 * Returns validators for LANGUAGE_ID field.
	 *
	 * @return array
	 */
	public static function validateLanguageId()
	{
		return array(
			new Main\Entity\Validator\Length(null, 2),
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
			new Main\Entity\Validator\Length(null, 255),
		);
	}
	/**
	 * Returns validators for DESCRIPTION field.
	 *
	 * @return array
	 */
	public static function validateDescription()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}
	/**
	 * Returns validators for COPYRIGHT field.
	 *
	 * @return array
	 */
	public static function validateCopyright()
	{
		return array(
			new Main\Entity\Validator\Length(null, 255),
		);
	}
}
