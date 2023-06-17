<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2022 Bitrix
 */

namespace Bitrix\Main;

use Bitrix\Main\IO;
use Bitrix\Main\Type\Collection;

class EventManager
{
	/**
	 * @var EventManager
	 */
	protected static $instance;

	protected $handlers = [];
	protected $isHandlersLoaded = false;

	protected const CACHE_ID = 'b_module_to_module';

	protected function __construct()
	{
	}

	/**
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
	 * @static
	 * @param EventManager $instance
	 */
	public static function setInstance($instance)
	{
		$c = __CLASS__;
		if ($instance instanceof $c)
		{
			self::$instance = $instance;
		}
	}

	protected function addEventHandlerInternal($fromModuleId, $eventType, $callback, $includeFile, $sort, $version)
	{
		$arEvent = [
			'FROM_MODULE_ID' => $fromModuleId,
			'MESSAGE_ID' => $eventType,
			'CALLBACK' => $callback,
			'SORT' => $sort,
			'FULL_PATH' => $includeFile,
			'VERSION' => $version,
			'TO_NAME' => $this->formatEventName(['CALLBACK' => $callback]),
		];

		$fromModuleId = strtoupper($fromModuleId);
		$eventType = strtoupper($eventType);

		if (!isset($this->handlers[$fromModuleId]) || !is_array($this->handlers[$fromModuleId]))
		{
			$this->handlers[$fromModuleId] = [];
		}

		$arEvents = &$this->handlers[$fromModuleId];

		if (empty($arEvents[$eventType]) || !is_array($arEvents[$eventType]))
		{
			$arEvents[$eventType] = [$arEvent];
			$iEventHandlerKey = 0;
		}
		else
		{
			$newEvents = [];
			$iEventHandlerKey = max(array_keys($arEvents[$eventType])) + 1;

			foreach ($arEvents[$eventType] as $key => $value)
			{
				if ($value['SORT'] > $arEvent['SORT'])
				{
					$newEvents[$iEventHandlerKey] = $arEvent;
				}

				$newEvents[$key] = $value;
			}
			$newEvents[$iEventHandlerKey] = $arEvent;
			$arEvents[$eventType] = $newEvents;
		}

		return $iEventHandlerKey;
	}

	public function addEventHandler($fromModuleId, $eventType, $callback, $includeFile = false, $sort = 100)
	{
		return $this->addEventHandlerInternal($fromModuleId, $eventType, $callback, $includeFile, $sort, 2);
	}

	/**
	 * @param $fromModuleId
	 * @param $eventType
	 * @param $callback
	 * @param bool $includeFile
	 * @param int $sort
	 * @return int
	 */
	public function addEventHandlerCompatible($fromModuleId, $eventType, $callback, $includeFile = false, $sort = 100)
	{
		return $this->addEventHandlerInternal($fromModuleId, $eventType, $callback, $includeFile, $sort, 1);
	}

	public function removeEventHandler($fromModuleId, $eventType, $iEventHandlerKey)
	{
		$fromModuleId = strtoupper($fromModuleId);
		$eventType = strtoupper($eventType);

		if (is_array($this->handlers[$fromModuleId][$eventType]))
		{
			if (isset($this->handlers[$fromModuleId][$eventType][$iEventHandlerKey]))
			{
				unset($this->handlers[$fromModuleId][$eventType][$iEventHandlerKey]);
				return true;
			}
		}

		return false;
	}

	public function unRegisterEventHandler($fromModuleId, $eventType, $toModuleId, $toClass = '', $toMethod = '', $toPath = '', $toMethodArg = [])
	{
		$toMethodArg = (!is_array($toMethodArg) || empty($toMethodArg) ? '' : serialize($toMethodArg));

		$con = Application::getConnection();
		$sqlHelper = $con->getSqlHelper();

		$strSql =
			"DELETE FROM b_module_to_module ".
			"WHERE FROM_MODULE_ID='".$sqlHelper->forSql($fromModuleId)."'".
			"	AND MESSAGE_ID='".$sqlHelper->forSql($eventType)."' ".
			"	AND TO_MODULE_ID='".$sqlHelper->forSql($toModuleId)."' ".
			(($toClass != '') ? " AND TO_CLASS='".$sqlHelper->forSql($toClass)."' " : " AND (TO_CLASS='' OR TO_CLASS IS NULL) ").
			(($toMethod != '') ? " AND TO_METHOD='".$sqlHelper->forSql($toMethod)."'": " AND (TO_METHOD='' OR TO_METHOD IS NULL) ").
			(($toPath != '' && $toPath !== 1/*controller disconnect correction*/) ? " AND TO_PATH='".$sqlHelper->forSql($toPath)."'" : " AND (TO_PATH='' OR TO_PATH IS NULL) ").
			(($toMethodArg != '') ? " AND TO_METHOD_ARG='".$sqlHelper->forSql($toMethodArg)."'" : " AND (TO_METHOD_ARG='' OR TO_METHOD_ARG IS NULL) ");

		$con->queryExecute($strSql);

		$this->clearLoadedHandlers();
	}

	public function registerEventHandler($fromModuleId, $eventType, $toModuleId, $toClass = '', $toMethod = '', $sort = 100, $toPath = '', $toMethodArg = [])
	{
		$this->registerEventHandlerInternal($fromModuleId, $eventType, $toModuleId, $toClass, $toMethod, $sort, $toPath, $toMethodArg, 2);
	}

	public function registerEventHandlerCompatible($fromModuleId, $eventType, $toModuleId, $toClass = '', $toMethod = '', $sort = 100, $toPath = '', $toMethodArg = [])
	{
		$this->registerEventHandlerInternal($fromModuleId, $eventType, $toModuleId, $toClass, $toMethod, $sort, $toPath, $toMethodArg, 1);
	}

	protected function registerEventHandlerInternal($fromModuleId, $eventType, $toModuleId, $toClass, $toMethod, $sort, $toPath, $toMethodArg, $version)
	{
		$toMethodArg = (!is_array($toMethodArg) || empty($toMethodArg) ? '' : serialize($toMethodArg));
		$sort = intval($sort);
		$version = intval($version);

		$uniqueID = md5(mb_strtolower($fromModuleId.'.'.$eventType.'.'.$toModuleId.'.'.$toPath.'.'.$toClass.'.'.$toMethod.'.'.$toMethodArg.'.'.$version));

		$con = Application::getConnection();
		$sqlHelper = $con->getSqlHelper();

		$fromModuleId = $sqlHelper->forSql($fromModuleId);
		$eventType = $sqlHelper->forSql($eventType);
		$toModuleId = $sqlHelper->forSql($toModuleId);
		$toClass = $sqlHelper->forSql($toClass);
		$toMethod = $sqlHelper->forSql($toMethod);
		$toPath = $sqlHelper->forSql($toPath);
		$toMethodArg = $sqlHelper->forSql($toMethodArg);

		$con->queryExecute(
			"INSERT IGNORE INTO b_module_to_module (SORT, FROM_MODULE_ID, MESSAGE_ID, TO_MODULE_ID, ".
			"	TO_CLASS, TO_METHOD, TO_PATH, TO_METHOD_ARG, VERSION, UNIQUE_ID) ".
			"VALUES (".$sort.", '".$fromModuleId."', '".$eventType."', '".$toModuleId."', ".
			"   '".$toClass."', '".$toMethod."', '".$toPath."', '".$toMethodArg."', ".$version.", '".$uniqueID."')"
		);

		$this->clearLoadedHandlers();
	}

	protected function formatEventName($arEvent)
	{
		$strName = '';
		if (isset($arEvent['CALLBACK']))
		{
			if (is_array($arEvent['CALLBACK']))
			{
				$strName .= (is_object($arEvent['CALLBACK'][0]) ? get_class($arEvent['CALLBACK'][0]) : $arEvent['CALLBACK'][0]).'::'.$arEvent['CALLBACK'][1];
			}
			elseif (is_callable($arEvent['CALLBACK']))
			{
				$strName .= 'callable';
			}
			else
			{
				$strName .= $arEvent['CALLBACK'];
			}
		}
		else
		{
			$strName .= $arEvent['TO_CLASS'].'::'.$arEvent['TO_METHOD'];
		}
		if (isset($arEvent['TO_MODULE_ID']) && !empty($arEvent['TO_MODULE_ID']))
		{
			$strName .= ' ('.$arEvent['TO_MODULE_ID'].')';
		}
		return $strName;
	}

	protected function loadEventHandlers()
	{
		$cache = Application::getInstance()->getManagedCache();

		if ($cache->read(3600, self::CACHE_ID, self::CACHE_ID))
		{
			$rawEvents = $cache->get(self::CACHE_ID);

			if (!is_array($rawEvents))
			{
				$rawEvents = [];
			}
		}
		else
		{
			$con = Application::getConnection();

			$rs = $con->query("
				SELECT FROM_MODULE_ID, MESSAGE_ID, SORT, TO_MODULE_ID, TO_PATH,
					TO_CLASS, TO_METHOD, TO_METHOD_ARG, VERSION
				FROM b_module_to_module m2m
					INNER JOIN b_module m ON (m2m.TO_MODULE_ID = m.ID)
				ORDER BY SORT
			");

			$rawEvents = $rs->fetchAll();

			$cache->set(self::CACHE_ID, $rawEvents);
		}

		$handlers = $this->handlers;
		$hasHandlers = !empty($this->handlers);

		foreach ($rawEvents as $ar)
		{
			$ar['TO_NAME'] = $this->formatEventName([
				'TO_MODULE_ID' => $ar['TO_MODULE_ID'],
				'TO_CLASS' => $ar['TO_CLASS'],
				'TO_METHOD' => $ar['TO_METHOD'],
			]);
			$ar['FROM_MODULE_ID'] = strtoupper($ar['FROM_MODULE_ID']);
			$ar['MESSAGE_ID'] = strtoupper($ar['MESSAGE_ID']);
			if ($ar['TO_METHOD_ARG'] != '')
			{
				$ar['TO_METHOD_ARG'] = unserialize($ar['TO_METHOD_ARG'], ['allowed_classes' => false]);
			}
			else
			{
				$ar['TO_METHOD_ARG'] = [];
			}

			$this->handlers[$ar['FROM_MODULE_ID']][$ar['MESSAGE_ID']][] = [
				'SORT' => $ar['SORT'],
				'TO_MODULE_ID' => $ar['TO_MODULE_ID'],
				'TO_PATH' => $ar['TO_PATH'],
				'TO_CLASS' => $ar['TO_CLASS'],
				'TO_METHOD' => $ar['TO_METHOD'],
				'TO_METHOD_ARG' => $ar['TO_METHOD_ARG'],
				'VERSION' => $ar['VERSION'],
				'TO_NAME' => $ar['TO_NAME'],
				'FROM_DB' => true,
			];
		}

		if ($hasHandlers)
		{
			// need to re-sort because of AddEventHandler() calls (before loadEventHandlers)
			foreach (array_keys($handlers) as $moduleId)
			{
				foreach (array_keys($handlers[$moduleId]) as $event)
				{
					Collection::sortByColumn(
						$this->handlers[$moduleId][$event],
						['SORT' => SORT_ASC],
						'',
						null,
						true
					);
				}
			}
		}

		$this->isHandlersLoaded = true;
	}

	public function clearLoadedHandlers()
	{
		$managedCache = Application::getInstance()->getManagedCache();
		$managedCache->clean(self::CACHE_ID, self::CACHE_ID);

		foreach ($this->handlers as $module=>$types)
		{
			foreach ($types as $type=>$events)
			{
				foreach ($events as $i => $event)
				{
					if (isset($event['FROM_DB']) && $event['FROM_DB'])
					{
						unset($this->handlers[$module][$type][$i]);
					}
				}
			}
		}
		$this->isHandlersLoaded = false;
	}

	public function findEventHandlers($eventModuleId, $eventType, array $filter = null)
	{
		if (!$this->isHandlersLoaded)
		{
			$this->loadEventHandlers();
		}

		$eventModuleId = strtoupper($eventModuleId);
		$eventType = strtoupper($eventType);

		if (!isset($this->handlers[$eventModuleId]) || !isset($this->handlers[$eventModuleId][$eventType]))
		{
			return [];
		}

		$handlers = $this->handlers[$eventModuleId][$eventType];
		if (!is_array($handlers))
		{
			return [];
		}

		if (is_array($filter) && !empty($filter))
		{
			$handlersTmp = $handlers;
			$handlers = [];
			foreach ($handlersTmp as $handler)
			{
				if (isset($handler['TO_MODULE_ID']) && in_array($handler['TO_MODULE_ID'], $filter))
				{
					$handlers[] = $handler;
				}
			}
		}

		return $handlers;
	}

	public function send(Event $event)
	{
		$handlers = $this->findEventHandlers($event->getModuleId(), $event->getEventType(), $event->getFilter());
		foreach ($handlers as $handler)
		{
			$this->sendToEventHandler($handler, $event);
		}
	}

	protected function sendToEventHandler(array $handler, Event $event)
	{
		try
		{
			$result = true;
			$includeResult = true;

			$event->addDebugInfo($handler);

			if (isset($handler['TO_MODULE_ID']) && !empty($handler['TO_MODULE_ID']) && ($handler['TO_MODULE_ID'] != 'main'))
			{
				$result = Loader::includeModule($handler['TO_MODULE_ID']);
			}
			elseif (isset($handler['TO_PATH']) && !empty($handler['TO_PATH']))
			{
				$path = ltrim($handler['TO_PATH'], '/');
				if (($path = Loader::getLocal($path)) !== false)
				{
					$includeResult = include_once($path);
				}
			}
			elseif (isset($handler['FULL_PATH']) && !empty($handler['FULL_PATH']) && IO\File::isFileExists($handler['FULL_PATH']))
			{
				$includeResult = include_once($handler['FULL_PATH']);
			}

			$event->addDebugInfo($result);

			if ($result)
			{
				if (isset($handler['TO_METHOD_ARG']) && is_array($handler['TO_METHOD_ARG']) && !empty($handler['TO_METHOD_ARG']))
				{
					$args = $handler['TO_METHOD_ARG'];
				}
				else
				{
					$args = [];
				}

				if ($handler['VERSION'] > 1)
				{
					$args[] = $event;
				}
				else
				{
					$args = array_merge($args, array_values($event->getParameters()));
				}

				$callback = null;
				if (isset($handler['CALLBACK']))
				{
					$callback = $handler['CALLBACK'];
				}
				elseif (!empty($handler['TO_CLASS']) && !empty($handler['TO_METHOD']) && class_exists($handler['TO_CLASS']))
				{
					$callback = [$handler['TO_CLASS'], $handler['TO_METHOD']];
				}

				if ($callback != null)
				{
					$result = call_user_func_array($callback, $args);
				}
				else
				{
					$result = $includeResult;
				}

				if (($result != null) && !($result instanceof EventResult))
				{
					$result = new EventResult(EventResult::UNDEFINED, $result, $handler['TO_MODULE_ID'] ?? null);
				}

				$event->addDebugInfo($result);

				if ($result != null)
				{
					$event->addResult($result);
				}
			}
		}
		catch (\Exception $ex)
		{
			if ($event->isDebugOn())
			{
				$event->addException($ex);
			}
			else
			{
				throw $ex;
			}
		}
	}
}
