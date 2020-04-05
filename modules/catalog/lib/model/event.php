<?php
namespace Bitrix\Catalog\Model;

use Bitrix\Main,
	Bitrix\Main\Localization\Loc,
	Bitrix\Catalog;

Loc::loadMessages(__FILE__);

class Event extends Main\Event
{
	protected $entity = null;
	protected $entityEventType;

	protected static $catalogHandlerExist = array();

	private static $keys = array('fields', 'external_fields', 'actions');

	public function __construct(Entity $entity, $type, array $parameters = array())
	{
		$this->entity = $entity;

		parent::__construct('catalog', get_class($this->entity).'::'.$type, $parameters);
	}

	/**
	 * Checks the result of the event for errors, fills the Result object.
	 * Returns true on errors, false on no errors.
	 *
	 * @param Main\Result $result
	 * @return bool
	 */
	public function getErrors(Main\Result $result)
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

	public function mergeData(array &$data)
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

	public static function existEventHandlers(Entity $entity, $type)
	{
		$id = get_class($entity).'::'.$type;
		if (!isset(self::$catalogHandlerExist[$id]))
		{
			$eventManager = Main\EventManager::getInstance();

			$eventsList = $eventManager->findEventHandlers(
				'catalog', get_class($entity).'::'.$type
			);

			self::$catalogHandlerExist[$id] = !empty($eventsList);

			unset($eventsList, $eventManager);
		}

		return self::$catalogHandlerExist[$id];
	}
}