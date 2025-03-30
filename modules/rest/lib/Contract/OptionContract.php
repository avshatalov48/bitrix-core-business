<?php

namespace Bitrix\Rest\Contract;

interface OptionContract
{
	public function get(string $key, mixed $default = null): mixed;

	public function set(string $key, mixed $value): void;
}