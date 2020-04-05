<?php

namespace Bitrix\Main\UserField\Internal;

use Bitrix\Main\EventManager;
use Bitrix\Main\ORM\Entity;

/**
 * @deprecated
 */
final class Registry
{
	const EVENT_NAME = 'onGetUserFieldTypeFactory';

	/** @var TypeFactory[] */
	protected $factories = [];
	protected $itemTypes = [];

	/** @var Registry */
	private static $instance;

	private function __construct()
	{
		$this->addFactoriesByEvent();
	}

	private function __clone()
	{
	}

	/**
	 * Returns Singleton of Driver
	 * @return Registry
	 */
	public static function getInstance(): Registry
	{
		if (!isset(self::$instance))
		{
			self::$instance = new Registry;
		}

		return self::$instance;
	}

	public function registerFactory(TypeFactory $factory)
	{
		$this->factories[$factory->getCode()] = $factory;
	}

	/**
	 * @param Entity $entity
	 * @param array $type
	 */
	public function registerTypeByEntity(Entity $entity, array $type): void
	{
		$this->itemTypes[$entity->getName()] = $type;
	}

	/**
	 * @param Entity $entity
	 * @return array|null
	 */
	public function getTypeByEntity(Entity $entity): ?array
	{
		$entityName = $entity->getName();
		if(isset($this->itemTypes[$entityName]))
		{
			return $this->itemTypes[$entityName];
		}

		return null;
	}

	public function getFactoryByCode(string $code): ?TypeFactory
	{
		if(isset($this->factories[$code]))
		{
			return $this->factories[$code];
		}

		return null;
	}

	public function getFactoryByTypeDataClass($typeDataClass): ?TypeFactory
	{
		foreach($this->factories as $factory)
		{
			if($factory->getTypeDataClass() == $typeDataClass)
			{
				return $factory;
			}
		}

		return null;
	}

	/**
	 * @param Entity $entity
	 * @return null|string
	 */
	public function getUserFieldEntityIdByItemEntity(Entity $entity): ?string
	{
		$type = $this->getTypeByEntity($entity);
		if($type && $type['code'])
		{
			$factory = $this->getFactoryByCode($type['code']);
			if($factory)
			{
				return $factory->getUserFieldEntityId($type['ID']);
			}
		}

		return null;
	}

	protected function addFactoriesByEvent(): void
	{
		foreach(EventManager::getInstance()->findEventHandlers('main', static::EVENT_NAME) as $handler)
		{
			$eventResult = ExecuteModuleEventEx($handler);
			if(is_array($eventResult))
			{
				foreach($eventResult as $factory)
				{
					if($factory instanceof TypeFactory)
					{
						$this->registerFactory($factory);
					}
				}
			}
		}
	}
}