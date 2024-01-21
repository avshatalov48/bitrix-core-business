<?php

namespace Bitrix\Socialnetwork\Space\Toolbar\Switcher;

trait CacheTrait
{
	private static ?SwitcherCollection $switchers = null;

	public static function get(int $userId, ?int $spaceId, string $code): SwitcherInterface
	{
		static::init();

		/** @var AbstractSwitcher $switcher */
		$switcher = new static($userId, $spaceId, $code);
		if (static::$switchers->has($switcher))
		{
			return $switcher;
		}

		static::$switchers->add($switcher);
		return $switcher;
	}

	private static function init(): void
	{
		if (is_null(static::$switchers))
		{
			static::$switchers = new SwitcherCollection();
		}
	}

	public function invalidate(): void
	{
		$this->isInitialized = false;
	}
}