<?php

namespace Bitrix\Report\VisualConstructor\Internal\Manager;

use Bitrix\Main\Event;
use Bitrix\Report\VisualConstructor\Config\Common;
use Bitrix\Report\VisualConstructor\Internal\Error\IErrorable;

/**
 * Base Class Singleton for report managers
 * @package Bitrix\Report\VisualConstructor\Internal\Manager
 */
abstract class Base implements IErrorable
{
	private $event;
	private static $instances = array();
	protected $errors;
	protected static $result;

	/**
	 * @return string
	 */
	protected abstract function getEventTypeKey();

	/**
	 * @return mixed
	 */
	abstract public function call();

	public function getErrors()
	{
		return $this->errors;
	}

	/**
	 * Returns the *Singleton* instance of this class.
	 *
	 * @return static Singleton The *Singleton* instance.
	 */
	public static function getInstance()
	{
		$class = get_called_class();
		if (!isset(self::$instances[$class]))
		{
			self::$instances[$class] = new $class();
		}
		return self::$instances[$class];
	}

	/**
	 * Protected constructor to prevent creating a new instance of the
	 * *Singleton* via the `new` operator from outside of this class.
	 */
	protected function __construct()
	{
	}

	/**
	 * @return Event
	 */
	protected function getEvent()
	{
		return $this->event;
	}

	/**
	 * Private clone method to prevent cloning of the instance of the
	 * *Singleton* instance.
	 *
	 * @return void
	 */
	private function __clone()
	{
	}

	/**
	 * @return $this
	 */
	private function collect()
	{
		$moduleId = Common::MODULE_NAME;
		$event = new Event($moduleId, $this->getEventTypeKey());
		$event->send();
		$this->event = $event;
		return $this;
	}

	/**
	 * @return string
	 */
	protected function getModuleId()
	{
		return Common::MODULE_NAME;
	}


	/**
	 * @return array
	 */
	protected function getResult()
	{
		//TODO: maybe should add here some physical caching ?!!!!!!!!
		$class = $this->getManagerClassName();
		if (!isset($this->result[$class]))
		{
			$this->collect();
			$results = $this->getEvent()->getResults();
			static::$result[$class] = array();
			foreach ($results as $result)
			{
				$params = $result->getParameters();
				foreach ($params as $param)
				{
					static::$result[$class][] = $param;
				}
			}
		}
		return static::$result[$class];
	}

	/**
	 * @return string
	 */
	private function getManagerClassName()
	{
		return get_called_class();
	}
}