<?php

namespace Bitrix\Lists\Api\Data\IBlockService;

use Bitrix\Lists\Api\Data\Filter;

class IBlockElementFilter extends Filter
{
	public const ALLOWABLE_FIELDS = [
		'ID',
		// ACTIVE - Y
		// 'NAME',
		'CHECK_PERMISSIONS',
		// PERMISSIONS_BY
		'MIN_PERMISSION',
		'CREATED_BY',
		'IBLOCK_ID',
		// SITE_ID,
		'IBLOCK_TYPE',
		'SECTION_ID',
		// SHOW_HISTORY
		'SHOW_NEW'
	];

	public function setField(string $fieldId, $value, string $operator = ''): static
	{
		if ($fieldId === 'CHECK_PERMISSIONS')
		{
			return $this->setCheckPermission(is_bool($value) ? $value : ($value === 'Y'));
		}

		return parent::setField($fieldId, $value, $operator);
	}

	public function setIBlockType(string $iBlockType): static
	{
		if (!empty($iBlockType))
		{
			$this->setField('IBLOCK_TYPE', $iBlockType, '=');
		}

		return $this;
	}

	public function setCreatedBy(int $userId): static
	{
		if ($userId >= 0)
		{
			$this->setField('CREATED_BY', (string)$userId, '=');
		}

		return $this;
	}

	public function setCheckPermission(bool $value): static
	{
		$this->filter['CHECK_PERMISSIONS'] = $value ? 'Y': 'N';
		$this->keyMatching['CHECK_PERMISSIONS'] = 'CHECK_PERMISSIONS';

		return $this;
	}

	public function setId(int $id): static
	{
		if ($id >= 0)
		{
			$this->setField('ID', $id, '=');
		}

		return $this;
	}

	public function setIBlockId(int $iBlockId): static
	{
		if ($iBlockId > 0)
		{
			$this->setField('IBLOCK_ID', $iBlockId, '=');
		}

		return $this;
	}

	public function setShowNew(bool $flag): static
	{
		$this->setField('SHOW_NEW', $flag ? 'Y' : 'N');

		return $this;
	}

	public function setMinPermission(string $permission): static
	{
		if (in_array($permission, ['E', 'S', 'T', 'R', 'U', 'W', 'X'], true))
		{
			$this->setField('MIN_PERMISSION', $permission);
		}

		return $this;
	}
}
