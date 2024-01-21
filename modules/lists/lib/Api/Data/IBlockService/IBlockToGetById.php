<?php

namespace Bitrix\Lists\Api\Data\IBlockService;

class IBlockToGetById
{
	private int $iBlockId = 0;
	private bool $needCheckPermissions = true;

	public function __construct(int $iBlockId)
	{
		if ($iBlockId > 0)
		{
			$this->iBlockId = $iBlockId;
		}
	}

	public function getIBlockId(): int
	{
		return $this->iBlockId;
	}

	public function enableCheckPermissions(): static
	{
		$this->needCheckPermissions = true;

		return $this;
	}

	public function disableCheckPermissions(): static
	{
		$this->needCheckPermissions = false;

		return $this;
	}

	public function needCheckPermissions(): bool
	{
		return $this->needCheckPermissions;
	}
}
