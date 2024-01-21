<?php

namespace Bitrix\Lists\Api\Data\IBlockService;

class IBlockElementToUpdate
{
	private int $modifiedBy = 0;
	private int $elementId = 0;
	private int $iBlockId = 0;
	private int $sectionId = 0;
	private array $values;

	private bool $isCheckPermissionsEnabled = true;

	public function __construct(
		int $modifiedBy,
		int $elementId,
		int $iBlockId,
		int $sectionId,
		array $values,
	)
	{
		if ($elementId > 0)
		{
			$this->elementId = $elementId;
		}
		if ($iBlockId > 0)
		{
			$this->iBlockId = $iBlockId;
		}
		if ($sectionId > 0)
		{
			$this->sectionId = $sectionId;
		}
		if ($modifiedBy > 0)
		{
			$this->modifiedBy = $modifiedBy;
		}

		$this->values = $values;
	}

	public function enableCheckPermissions(): static
	{
		$this->isCheckPermissionsEnabled = true;

		return $this;
	}

	public function disableCheckPermissions(): static
	{
		$this->isCheckPermissionsEnabled = false;

		return $this;
	}

	public function isCheckPermissionsEnabled(): bool
	{
		return $this->isCheckPermissionsEnabled;
	}

	public function getElementId(): int
	{
		return $this->elementId;
	}

	public function getSectionId(): int
	{
		return $this->sectionId;
	}

	public function getIBlockId(): int
	{
		return $this->iBlockId;
	}

	public function getFieldValueById(string $fieldsId)
	{
		return $this->values[$fieldsId] ?? null;
	}

	public function getModifiedBy(): int
	{
		return $this->modifiedBy;
	}
}
