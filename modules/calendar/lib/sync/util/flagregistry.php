<?php

namespace Bitrix\Calendar\Sync\Util;

use Bitrix\Calendar\Core\Base\SingletonTrait;

class FlagRegistry
{
	use SingletonTrait;
	/**
	 * @var array
	 */
	private array $flags;

	public function setFlag(string $name)
	{
		$this->flags[$name] = true;
	}

	public function resetFlag(string $name)
	{
		if (array_key_exists($name, $this->flags))
		{
			unset($this->flags[$name]);
		}
	}

	public function isFlag(string $name): bool
	{
		return $this->flags[$name] ?? false;
	}
}