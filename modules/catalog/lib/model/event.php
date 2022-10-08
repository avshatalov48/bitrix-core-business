<?php
namespace Bitrix\Catalog\Model;

use Bitrix\Main,
	Bitrix\Main\Localization\Loc,
	Bitrix\Catalog;

Loc::loadMessages(__FILE__);

class Event extends Main\Event
{
	/** @var Entity */
	protected $entity = null;

	protected static $catalogHandlerExist = [];

	private static $keys = ['fields', 'external_fields', 'actions'];

	public function __construct(Entity $entity, string $type, array $parameters = [])
	{
		$this->entity = $entity;

		parent::__construct('catalog', self::makeEventName(get_class($this->entity), $type), $parameters);
	}

	/**
	 * Checks the result of the event for errors, fills the Result object.
	 * Returns true on errors, false on no errors.
	 *
	 * @param Main\Result $result
	 * @return bool
	 */
	public function getErrors(Main\Result $result): bool
	{
		$hasErrors = false;

		/** @var $eventResult Main\Entity\EventResult */
		foreach($this->getResults() as $eventResult)
		{
			if ($eventResult->getType() === Main\Entity\EventResult::ERROR)
			{
				$hasErrors = true;
				$result->addErrors($eventResult->getErrors());
			}
		}
		return $hasErrors;
	}

	/**
	 * Merge data from handlers.
	 *
	 * @param array $data
	 * @return void
	 */
	public function mergeData(array &$data): void
	{
		/** @var $eventResult Catalog\Model\EventResult */
		foreach($this->getResults() as $eventResult)
		{
			$removed = $eventResult->getUnset();
			foreach (self::$keys as $index)
			{
				if (empty($removed[$index]))
					continue;
				foreach ($removed[$index] as $key)
					unset($data[$index][$key]);
				unset($key);
			}
			unset($removed);
			$modified = $eventResult->getModified();
			foreach (self::$keys as $index)
			{
				if (empty($modified[$index]))
					continue;
				$data[$index] = array_merge($data[$index], $modified[$index]);
			}
			unset($modified);
		}
	}

	/**
	 * Search handlers for event.
	 *
	 * @param Entity $entity
	 * @param string $eventName
	 * @return bool
	 */
	public static function existEventHandlers(Entity $entity, string $eventName): bool
	{
		return static::existEventHandlersById(self::makeEventName(get_class($entity), $eventName));
	}

	/**
	 * @param string $class
	 * @param string $eventName
	 * @return string
	 */
	public static function makeEventName(string $class, string $eventName): string
	{
		return $class.'::'.$eventName;
	}

	/**
	 * Search handlers for event by id.
	 *
	 * @param string $id
	 * @return bool
	 */
	public static function existEventHandlersById(string $id): bool
	{
		if (!isset(self::$catalogHandlerExist[$id]))
		{
			$eventManager = Main\EventManager::getInstance();
			$eventsList = $eventManager->findEventHandlers(
				'catalog', $id
			);
			self::$catalogHandlerExist[$id] = !empty($eventsList);
			unset($eventsList, $eventManager);
		}
		return self::$catalogHandlerExist[$id];
	}
}