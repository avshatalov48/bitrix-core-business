<?php
namespace Bitrix\Rest;

use Bitrix\Main;

/**
 * Class AppLangTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> APP_ID int mandatory
 * <li> LANGUAGE_ID string(2) mandatory
 * <li> MENU_NAME string(500) mandatory
 * </ul>
 *
 * @package Bitrix\Rest
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
		return 'b_rest_app_lang';
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
			'MENU_NAME' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateMenuName'),
			),
			'APP' => array(
				'data_type' => 'Bitrix\Rest\AppTable',
				'reference' => array('=this.APP_ID' => 'ref.ID'),
			),
		);
	}

	public static function deleteByApp($appId)
	{
		$connection = Main\Application::getConnection();
		return $connection->query("DELETE FROM ".static::getTableName()." WHERE APP_ID='".intval($appId)."'");
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
	 * Returns validators for MENU_NAME field.
	 *
	 * @return array
	 */
	public static function validateMenuName()
	{
		return array(
			new Main\Entity\Validator\Length(null, 500),
		);
	}
}