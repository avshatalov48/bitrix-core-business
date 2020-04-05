<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2018 Bitrix
 */

namespace Bitrix\Main\ORM;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Objectify\EntityObject;

/**
 * EventManager wrapper for ORM entities
 *
 * @package    bitrix
 * @subpackage main
 */
class EventManager
{
	/**
	 * @var EventManager
	 */
	protected static $instance;

	/**
	 * Singleton constructor
	 */
	protected function __construct()
	{
	}

	/**
	 * Singleton getter
	 *
	 * @static
	 * @return EventManager
	 */
	public static function getInstance()
	{
		if (!isset(self::$instance))
		{
			$c = __CLASS__;
			self::$instance = new $c;
		}

		return self::$instance;
	}

	/**
	 * @param string|Entity|DataManager|EntityObject $entity    ORM Entity, or ORM Table class, or ORM Object class
	 * @param string                                 $eventType Constants DataManager::EVENT_ON_BEFORE_ADD etc.
	 * @param callable                               $callback  Callback
	 * @param bool                                   $includeFile
	 * @param int                                    $sort
	 *
	 * @return int|mixed
	 * @throws ArgumentException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function addEventHandler($entity, $eventType, $callback, $includeFile = false, $sort = 100)
	{
		$entity = static::obtainEntity($entity);
		$eventType = static::obtainEventType($entity, $eventType);

		// subscribe
		return \Bitrix\Main\EventManager::getInstance()
			->addEventHandler($entity->getModule(), $eventType, $callback, $includeFile, $sort);
	}

	/**
	 * @param string|Entity|DataManager|EntityObject $entity
	 * @param string                                 $eventType
	 * @param                                        $iEventHandlerKey
	 *
	 * @return bool
	 * @throws ArgumentException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function removeEventHandler($entity, $eventType, $iEventHandlerKey)
	{
		$entity = static::obtainEntity($entity);
		$eventType = static::obtainEventType($entity, $eventType);

		// unsubscribe
		return \Bitrix\Main\EventManager::getInstance()
			->removeEventHandler($entity->getModule(), $eventType, $iEventHandlerKey);
	}

	/**
	 * @param string|Entity|DataManager|EntityObject $entity
	 * @param string                                 $eventType
	 * @param string                                 $toModuleId
	 * @param string                                 $toClass
	 * @param string                                 $toMethod
	 * @param int                                    $sort
	 * @param string                                 $toPath
	 * @param array                                  $toMethodArg
	 *
	 * @throws ArgumentException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function registerEventHandler($entity, $eventType, $toModuleId, $toClass = "", $toMethod = "", $sort = 100, $toPath = "", $toMethodArg = [])
	{
		$entity = static::obtainEntity($entity);
		$eventType = static::obtainEventType($entity, $eventType);

		// subscribe
		\Bitrix\Main\EventManager::getInstance()
			->registerEventHandler($entity->getModule(), $eventType, $toModuleId, $toClass, $toMethod, $sort, $toPath, $toMethodArg);
	}

	/**
	 * @param string|Entity|DataManager|EntityObject $entity
	 * @param string                                 $eventType
	 * @param string                                 $toModuleId
	 * @param string                                 $toClass
	 * @param string                                 $toMethod
	 * @param string                                 $toPath
	 * @param array                                  $toMethodArg
	 *
	 * @throws ArgumentException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function unRegisterEventHandler($entity, $eventType, $toModuleId, $toClass = "", $toMethod = "", $toPath = "", $toMethodArg = [])
	{
		$entity = static::obtainEntity($entity);
		$eventType = static::obtainEventType($entity, $eventType);

		// unsubscribe
		\Bitrix\Main\EventManager::getInstance()
			->unRegisterEventHandler($entity->getModule(), $eventType, $toModuleId, $toClass, $toMethod, $toPath, $toMethodArg);
	}

	/**
	 * @param string|Entity|DataManager|EntityObject $entity
	 *
	 * @return Entity
	 * @throws ArgumentException
	 * @throws \Bitrix\Main\SystemException
	 */
	protected static function obtainEntity($entity)
	{
		// define entity
		if ($entity instanceof Entity)
		{
			// ok
		}
		elseif (is_subclass_of($entity, DataManager::class))
		{
			$entity = $entity::getEntity();
		}
		elseif (is_subclass_of($entity, EntityObject::class))
		{
			$dataClass = $entity::$dataClass;
			$entity = $dataClass::getEntity();
		}
		else
		{
			throw new ArgumentException('Unknown entity value');
		}

		return $entity;
	}

	/**
	 * @param Entity $entity
	 * @param string $eventType
	 *
	 * @return string
	 * @throws ArgumentException
	 */
	protected static function obtainEventType($entity, $eventType)
	{
		if (!in_array($eventType, [
			DataManager::EVENT_ON_BEFORE_ADD,
			DataManager::EVENT_ON_ADD,
			DataManager::EVENT_ON_AFTER_ADD,
			DataManager::EVENT_ON_BEFORE_UPDATE,
			DataManager::EVENT_ON_UPDATE,
			DataManager::EVENT_ON_AFTER_UPDATE,
			DataManager::EVENT_ON_BEFORE_DELETE,
			DataManager::EVENT_ON_DELETE,
			DataManager::EVENT_ON_AFTER_DELETE,
		], true))
		{
			throw new ArgumentException("Unknown event type `{$eventType}`");
		}

		return $entity->getNamespace() . $entity->getName() . '::' . $eventType;
	}
}
