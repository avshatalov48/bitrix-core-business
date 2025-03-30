<?php

namespace Bitrix\Rest\Service;

use Bitrix\Rest\Contract\OptionContract;
use Bitrix\Main\Config\Option;

class RestOption implements OptionContract
{
	private string $moduleName;

	public function __construct()
	{
		$this->moduleName = 'rest';
	}

	public function get(string $key, mixed $default = null): mixed
	{
		return Option::get($this->moduleName, $key, $default);
	}

	public function set(string $key, mixed $value): void
	{
		Option::set($this->moduleName, $key, $value);
	}
}
