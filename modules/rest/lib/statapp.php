<?php
namespace Bitrix\Rest;

use Bitrix\Main,
	Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

/**
 * Class StatAppTable
 * 
 * Fields:
 * <ul>
 * <li> APP_ID int mandatory
 * <li> APP_CODE string(128) mandatory
 * <li> APP reference to {@link \Bitrix\Rest\AppTable}
 * </ul>
 *
 * @package Bitrix\Rest
 **/

class StatAppTable extends Main\Entity\DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_rest_stat_app';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return array(
			'APP_ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'title' => Loc::getMessage('STAT_APP_ENTITY_APP_ID_FIELD'),
			),
			'APP_CODE' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateAppCode'),
				'title' => Loc::getMessage('STAT_APP_ENTITY_APP_CODE_FIELD'),
			),
			'APP' => array(
				'data_type' => 'Bitrix\Rest\App',
				'reference' => array('=this.APP_ID' => 'ref.ID'),
			),
		);
	}
	/**
	 * Returns validators for APP_CODE field.
	 *
	 * @return array
	 */
	public static function validateAppCode()
	{
		return array(
			new Main\Entity\Validator\Length(null, 128),
		);
	}
	/**
	 * Adds a relation between application id and it's code.
	 *
	 * @return void
	 */
	public static function register($appInfo)
	{
		$connection = Main\Application::getConnection();
		$helper = $connection->getSqlHelper();
		$queries = $helper->prepareMerge(
			static::getTableName(),
			array('APP_ID'),
			array('APP_ID' => $appInfo['ID'], 'APP_CODE' => $appInfo['CODE']),
			array('APP_CODE' => $appInfo['CODE'])
		);
		foreach($queries as $query)
		{
			$connection->queryExecute($query);
		}
	}
}