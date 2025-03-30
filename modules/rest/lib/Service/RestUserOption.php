<?php

namespace Bitrix\Rest\Service;

use Bitrix\Rest\Contract\OptionContract;

class RestUserOption implements OptionContract
{
	private string $moduleName;

	public function __construct()
	{
		$this->moduleName = 'rest';
	}

	public function get(string $key, mixed $default = null): mixed
	{
		return \CUserOptions::GetOption($this->moduleName, $key, $default);
	}

	public function set(string $key, mixed $value): void
	{
		\CUserOptions::SetOption($this->moduleName, $key, $value);
	}
}