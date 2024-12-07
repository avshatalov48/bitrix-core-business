<?php

namespace Bitrix\Bizproc\Api\Data\UserService;

class UsersToGet
{
	/** @var int[] */
	private array $userIds;

	public function __construct(array $userIds)
	{
		$this->userIds = $userIds;
		\Bitrix\Main\Type\Collection::normalizeArrayValuesByInt($this->userIds);
	}

	/**
	 * @return int[]
	 */
	public function getUserIds(): array
	{
		return $this->userIds;
	}
}