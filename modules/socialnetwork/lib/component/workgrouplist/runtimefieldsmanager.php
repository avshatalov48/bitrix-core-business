<?php

namespace Bitrix\Socialnetwork\Component\WorkgroupList;

class RuntimeFieldsManager
{
	private array $runtimeFields = [];

	public function __construct()
	{
		$runtimeFields = [];
	}

	public function add($fieldName): void
	{
		$this->runtimeFields[] = $fieldName;
	}

	public function has($fieldName): bool
	{
		return (in_array($fieldName, $this->runtimeFields, true));
	}

	public function get(): array
	{
		return $this->runtimeFields;
	}
}
