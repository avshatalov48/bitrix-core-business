<?php

namespace Bitrix\UI\AccessRights\V2\Dto\AccessRightsBuilder;

use Bitrix\UI\AccessRights\V2\Contract\AccessRightsBuilder\Provider\Models\RightModel;

final class UserGroupModelDto
{
	public function __construct(
		private readonly string|int $id,
		private readonly string $name,
		private array $accessCodes = [],
		/** @var RightModel[]  */
		private array $accessRightModels = [],
	)
	{
	}

	public function id(): string
	{
		return $this->id;
	}

	public function name(): string
	{
		return $this->name;
	}

	public function accessCodes(): array
	{
		return $this->accessCodes;
	}

	public function setAccessCodes(array $accessCodes): self
	{
		$this->accessCodes = $accessCodes;

		return $this;
	}

	public function addAccessCode(string $accessCode): self
	{
		$this->accessCodes[] = $accessCode;

		return $this;
	}

	/**
	 * @return RightModel[]
	 */
	public function accessRightModels(): array
	{
		return $this->accessRightModels;
	}

	public function setAccessRightModels(array $accessRightModels): self
	{
		$this->accessRightModels = $accessRightModels;

		return $this;
	}

	public function addAccessRightModel(RightModel $model): self
	{
		$this->accessRightModels[] = $model;

		return $this;
	}
}
