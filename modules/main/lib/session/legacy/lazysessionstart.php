<?php

namespace Bitrix\Main\Session\Legacy;

use Bitrix\Main\Application;
use Bitrix\Main\InvalidOperationException;

final class LazySessionStart implements \ArrayAccess
{
	private static $instance;

	public static function register()
	{
		if (static::$instance)
		{
			throw new InvalidOperationException("LazySessionStart was already registered.");
		}

		// It's very important to make link to object LazySessionStart,
		// because when somebody uses $_SESSION['d'] += $value;
		// it converts to: offsetGet & offsetSet. But php destroys
		// object because it'll be last reference and offsetSet crashes.
		$_SESSION = static::$instance = new static();
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