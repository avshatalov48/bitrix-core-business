<?php

namespace Bitrix\Im\V2\Integration\HumanResources\Department;

abstract class BaseDepartment implements IDepartment
{
	protected static ?int $topId = null;
	protected static bool $wasSearchedTopId = false;
	protected array $structureDepartments = [];

	public function getTopCode(): ?string
	{
		$departmentId = $this->getTopId();

		if (!isset($departmentId))
		{
			return null;
		}

		return 'DR' . $departmentId;
	}
}
