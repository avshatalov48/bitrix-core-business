<?php

namespace Bitrix\Im\V2\Integration\HumanResources\Department;

interface IDepartment
{
	public function getTopId(): ?int;
	public function getTopCode(): ?string;

	/**
	 * @return Entity[]
	 */
	public function getList(): array;

	/**
	 * @return Entity[]
	 */
	public function getListByXml(string $xmlId): array;
}
