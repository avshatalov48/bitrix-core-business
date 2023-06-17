<?php

namespace Bitrix\Main\Session\Legacy;

use Bitrix\Main\Application;
use Bitrix\Main\InvalidOperationException;

final class LazySessionStart implements \ArrayAccess
{
	private static $instance;

	public static function register(): void
	{
		if (self::$instance)
		{
			throw new InvalidOperationException("LazySessionStart was already registered.");
		}

		// It's very important to make a reference to the LazySessionStart object,
		// because when somebody uses $_SESSION['d'] += $value;
		// it converts to: offsetGet & offsetSet. But PHP destroys
		// the object because it'll be the last reference and offsetSet crashes.
		$_SESSION = self::$instance = new self();
	}

	protected function start(): void
	{
		if ($this->isSessionAlreadyClosed() && !Application::getInstance()->getSession()->isAccessible())
		{
			$this->writeToLogError(
				new \RuntimeException(
					"Skipped cold session start because headers have already been sent. Be aware and fix usage of session, details in trace."
				)
			);

			$GLOBALS['_SESSION'] = [];

			return;
		}

		Application::getInstance()->getSession()->start();
	}

	public function offsetExists($offset): bool
	{
		$this->start();

		return isset($_SESSION[$offset]);
	}

	#[\ReturnTypeWillChange]
	public function &offsetGet($offset)
	{
		$this->start();

		return $_SESSION[$offset];
	}

	public function offsetSet($offset, $value): void
	{
		$this->start();

		$_SESSION[$offset] = $value;
	}

	public function offsetUnset($offset): void
	{
		$this->start();

		unset($_SESSION[$offset]);
	}

	private function isKernelWentSessionStart(): bool
	{
		return defined('BX_STARTED');
	}

	private function isSessionAlreadyClosed(): bool
	{
		return
			$this->isKernelWentSessionStart()
			&& !Application::getInstance()->getKernelSession()->isStarted()
		;
	}

	private function writeToLogError(\RuntimeException $exception): void
	{
		$exceptionHandler = Application::getInstance()->getExceptionHandler();
		$exceptionHandler->writeToLog($exception);
	}
}
