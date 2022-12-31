<?php

namespace Bitrix\Calendar\Internals;

use Bitrix\Calendar\Core\Base\Map;

class FlagRegistry
{
	use SingletonTrait;

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