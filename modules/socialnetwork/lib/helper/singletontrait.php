<?php

namespace Bitrix\Socialnetwork\Helper;

use RuntimeException;

trait SingletonTrait
{
	use InstanceTrait;

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