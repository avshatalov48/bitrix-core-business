<?php

namespace Bitrix\Bizproc\Task\Data;

class ActivityData
{
	private array $data;

	public function __construct(array $data)
	{
		$this->data = $data;
	}

	public function get(string $name): mixed
	{
		return $this->data[$name] ?? null;
	}
}
