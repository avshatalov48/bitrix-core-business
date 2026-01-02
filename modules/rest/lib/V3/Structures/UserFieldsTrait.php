<?php

namespace Bitrix\Rest\V3\Structures;

trait UserFieldsTrait
{
	protected array $userFields = [];

	public function getUserFields(): array
	{
		return $this->userFields;
	}
}
