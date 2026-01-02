<?php

declare(strict_types=1);

namespace Bitrix\Im\V2\Common;

use RuntimeException;

trait SingletonTrait
{
	protected static ?self $instance = null;

	public static function getInstance(): static
	{
		if (static::$instance === null)
		{
			static::$instance = new static();
		}

		return static::$instance;
	}

	private function __construct()
	{
	}

	public function __clone()
	{
		throw new RuntimeException('Cannot clone a singleton');
	}

	public function __wakeup()
	{
		throw new RuntimeException('Cannot wake up a singleton');
	}
}
