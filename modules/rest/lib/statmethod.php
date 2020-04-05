<?php
namespace Bitrix\Rest;

use Bitrix\Main;

/**
 * Class StatMethodTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> NAME string(255) mandatory
 * </ul>
 *
 * @package Bitrix\Rest
 **/

class StatMethodTable extends Main\Entity\DataManager
{
	const METHOD_TYPE_METHOD = 'M';
	const METHOD_TYPE_EVENT = 'E';
	const METHOD_TYPE_PLACEMENT = 'P';

	protected static $methodCache = null;

	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_rest_stat_method';
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
			'NAME' => array(
				'data_type' => 'string',
				'required' => true,
				'validation' => array(__CLASS__, 'validateName'),
			),
			'METHOD_TYPE' => array(
				'data_type' => 'enum',
				'required' => false,
				'values' => array(self::METHOD_TYPE_METHOD, self::METHOD_TYPE_EVENT, self::METHOD_TYPE_PLACEMENT),
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
			new Main\Entity\Validator\Unique(),
		);
	}

	public static function getId($methodName)
	{
		static::checkList(array($methodName));

		return static::$methodCache[$methodName];
	}

	public static function checkList($methodList, $methodType = self::METHOD_TYPE_METHOD)
	{
		static::loadFromCache();

		$update = false;
		foreach($methodList as $method)
		{
			if(!array_key_exists($method, static::$methodCache))
			{
				static::addMethod($method, $methodType);
				$update = true;
			}
		}

		if($update)
		{
			static::loadFromCache(true);
		}

	}

	protected static function addMethod($methodName, $methodType)
	{
		$connection = Main\Application::getConnection();
		$helper = $connection->getSqlHelper();

		$sqlTableName = static::getTableName();
		$sqlMethodName = $helper->forSql($methodName);
		$sqlMethodType = in_array(
			$methodType, [
				static::METHOD_TYPE_METHOD,
				static::METHOD_TYPE_EVENT,
				static::METHOD_TYPE_PLACEMENT
			]
		) ? $methodType : self::METHOD_TYPE_METHOD;

		$query = "INSERT IGNORE INTO {$sqlTableName} (NAME, METHOD_TYPE) VALUES ('{$sqlMethodName}', '{$sqlMethodType}')";
		$connection->query($query);
	}

	protected static function loadFromCache($force = false)
	{
		if($force)
		{
			static::$methodCache = null;
		}

		if(static::$methodCache === null)
		{
			static::$methodCache = array();

			$cacheId = 'stat_method_cache';

			$managedCache = Main\Application::getInstance()->getManagedCache();
			if($force)
			{
				$managedCache->clean($cacheId);
			}

			if($managedCache->read(86400, $cacheId))
			{
				static::$methodCache = $managedCache->get($cacheId);
			}
			else
			{
				$dbRes = static::getList();
				while($method = $dbRes->fetch())
				{
					static::$methodCache[$method['NAME']] = $method['ID'];
				}

				$managedCache->set($cacheId, static::$methodCache);
			}
		}
	}
}