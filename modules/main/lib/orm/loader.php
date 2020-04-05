<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2018 Bitrix
 */

namespace Bitrix\Main\ORM;

use Bitrix\Main\ORM\Data\DataManager;

/**
 * Loads (generates) entity object or collection classes
 *
 * @package    bitrix
 * @subpackage main
 */
class Loader
{
	/** @var DataManager[] Entity registers its object class on init */
	protected static $predefinedObjectClass;

	/** @var DataManager[] Entity registers its collection class on init */
	protected static $predefinedCollectionClass;

	public static function autoLoad($class)
	{
		// break recursion
		if (substr($class, -5) == 'Table')
		{
			return;
		}

		if (strpos($class, '\\') === false)
		{
			// define global namespace explicitly
			$class = '\\'.$class;
		}

		$namespace = substr($class, 0, strrpos($class, '\\')+1);
		$className = substr($class, strrpos($class, '\\') + 1);

		if (substr($className, 0, 3) == 'EO_')
		{
			$needFor = 'object';

			if ($className == 'EO_NNM_Object')
			{
				// entity without name, defined by namespace
				$entityName = '';
			}
			elseif (substr($className, -11) == '_Collection')
			{
				$needFor = 'collection';
				$entityName = substr($className, 3, -11);
			}
			else
			{
				$entityName = substr($className, 3);
			}

			$entityName .= 'Table';
			$entityClass = $namespace.$entityName;

			if (class_exists($entityClass) && is_subclass_of($entityClass, DataManager::class))
			{
				($needFor == 'object')
					? Entity::compileObjectClass($entityClass)
					: Entity::compileCollectionClass($entityClass);
			}
		}
	}

	public static function registerObjectClass($objectClass, $entityClass)
	{
		static::$predefinedObjectClass[strtolower(Entity::normalizeName($objectClass))] = $entityClass;
	}

	public static function registerCollectionClass($collectionClass, $entityClass)
	{
		static::$predefinedCollectionClass[strtolower(Entity::normalizeName($collectionClass))] = $entityClass;
	}
}
