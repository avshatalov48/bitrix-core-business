<?php

namespace Bitrix\Calendar\Internals;

trait SingletonTrait
{
	/**
	 * @var static|null
	 */
	protected static $instance = null;

	/**
	 * @return static
	 */
	public static function getInstance()
	{
		if (static::$instance === null)
		{
			static::$instance = new static();
		}

		return static::$instance;
	}


	protected function __construct(){}

	protected function __wakeup(){}
	protected function __clone(){}
}