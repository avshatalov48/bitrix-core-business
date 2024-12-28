<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Helper;

trait InstanceTrait
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
}