<?php

namespace Bitrix\Socialnetwork\Component\WorkgroupList;

class SelectFieldsManager
{
	private array $selectFields = [];

	public function __construct()
	{
		$selectFields = [];
	}

	public function add($fieldName): void
	{
		$this->selectFields[] = $fieldName;
	}

	public function has($fieldName): bool
	{
		return (in_array($fieldName, $this->selectFields, true));
	}

	public function get(): array
	{
		return $this->selectFields;
	}
}
