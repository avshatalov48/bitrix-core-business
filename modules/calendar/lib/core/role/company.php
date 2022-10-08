<?php

namespace Bitrix\Calendar\Core\Role;

class Company extends BaseRole
{
	public const TYPE = 'company';

	public function getType(): string
	{
		return self::TYPE;
	}
}
