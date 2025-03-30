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
	 * @param int[] $ids
	 * @return Entity[]
	 */
	public function getListByIds(array $ids): array;

	/**
	 * @return Entity[]
	 */
	public function getListByXml(string $xmlId): array;

	/**
	 * @return int[]
	 */
	public function getColleagues(): array;

	public function getEmployeeIdsWithLimit(array $ids, int $limit = 50): array;
}
