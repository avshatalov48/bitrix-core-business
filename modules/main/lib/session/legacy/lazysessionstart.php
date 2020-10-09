<?php

namespace Bitrix\Main\Session\Legacy;

use Bitrix\Main\Application;
use Bitrix\Main\InvalidOperationException;

final class LazySessionStart implements \ArrayAccess
{
	private static $isRegistered = false;

	public static function register()
	{
		if (static::$isRegistered)
		{
			throw new InvalidOperationException("LazySessionStart was already registered.");
		}

		$_SESSION = new static();
		static::$isRegistered = true;
	}

	protected function start()
	{
		Application::getInstance()->getSession()->start();
	}

	public function offsetExists($offset)
	{
		$this->start();

		return isset($_SESSION[$offset]);
	}

	public function &offsetGet($offset)
	{
		$this->start();

		return $_SESSION[$offset];
	}

	public function offsetSet($offset, $value)
	{
		$this->start();

		$_SESSION[$offset] = $value;
	}

	public function offsetUnset($offset)
	{
		$this->start();

		unset($_SESSION[$offset]);
	}
}