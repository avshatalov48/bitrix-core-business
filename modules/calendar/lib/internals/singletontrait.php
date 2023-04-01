<?php

namespace Bitrix\Calendar\Internals;

use Bitrix\Calendar\Core\Base\BaseException;

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

	/**
	 * @throws BaseException
	 */
	public function __wakeup()
	{
		throw new BaseException("Trying to wake singleton up");
	}
	protected function __clone(){}
}