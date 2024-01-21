<?php

namespace Bitrix\Lists\Api\Data\IBlockService;

use Bitrix\Lists\Api\Data\Filter;

class IBlockListFilter extends Filter
{
	public const ALLOWABLE_FIELDS = [
		'ID',
		'ACTIVE',
		'TYPE',
		'SITE_ID',
		'SOCNET_GROUP_ID',
		'CHECK_PERMISSIONS',
	];

	public function setField(string $fieldId, $value, string $operator = ''): static
	{
		if ($fieldId === 'CHECK_PERMISSIONS')
		{
			return $this->setCheckPermission(is_bool($value) ? $value : ($value === 'Y'));
		}

		return parent::setField($fieldId, $value, $operator);
	}

	public function setActive(bool $value): static
	{
		return $this->setField('ACTIVE', $value ? 'Y' : 'N');
	}

	public function setIBLockTypeId(string $iBlockTypeId): static
	{
		return $this->setField('TYPE', $iBlockTypeId);
	}

	public function setSite($siteId): static
	{
		return $this->setField('SITE_ID', $siteId);
	}

	public function setSocNetGroupId(int $groupId): static
	{
		if ($groupId > 0)
		{
			return $this->setField('SOCNET_GROUP_ID', $groupId, '=');
		}

		return $this;
	}

	public function setCheckPermission(bool $value): static
	{
		$this->filter['CHECK_PERMISSIONS'] = $value ? 'Y': 'N';
		$this->keyMatching['CHECK_PERMISSIONS'] = 'CHECK_PERMISSIONS';

		return $this;
	}
}
