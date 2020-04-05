<?php
namespace Bitrix\MessageService\Internal\Entity;

use Bitrix\Main;

class RestAppLangTable extends Main\Entity\DataManager
{
	/**
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_messageservice_rest_app_lang';
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
			'APP_ID' => array(
				'data_type' => 'integer',
				'required' => true,
			),
			'LANGUAGE_ID' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateLanguageId'),
			),
			'NAME' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateVarchar500'),
			),
			'APP_NAME' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateVarchar500'),
			),
			'DESCRIPTION' => array(
				'data_type' => 'string',
				'validation' => array(__CLASS__, 'validateVarchar1000'),
			),
		);
	}

	public static function deleteByApp($appId)
	{
		$connection = Main\Application::getConnection();
		return $connection->query("DELETE FROM ".static::getTableName()." WHERE APP_ID=".(int)$appId);
	}

	/**
	 * @return array
	 */
	public static function validateVarchar500()
	{
		return array(
			new Main\Entity\Validator\Length(null, 500),
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
	 * @return array
	 */
	public static function validateVarchar1000()
	{
		return array(
			new Main\Entity\Validator\Length(null, 1000),
		);
	}
}