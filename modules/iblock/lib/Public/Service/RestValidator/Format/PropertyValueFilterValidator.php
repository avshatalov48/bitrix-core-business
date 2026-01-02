<?php

namespace Bitrix\Iblock\Public\Service\RestValidator\Format;

use Bitrix\Iblock\Public\Service\RestValidator\Trait;
use Bitrix\Iblock\PropertyTable;

class PropertyValueFilterValidator extends BaseFilterFieldValidator
{
	use Trait\IblockFilterOperationTrait;

	private const FIELD_NAME_PREFIX = 'PROPERTY_';

	protected int $iblockId;

	public function setIblockId(int $iblockId): self
	{
		if ($iblockId < 0)
		{
			return $this;
		}
		if ($this->getIblockId() !== $iblockId)
		{
			$this->iblockId = $iblockId;
			$this->setFields([]);
			$this->setFieldAliases([]);
			$this->init();
		}

		return $this;
	}

	public function getIblockId(): ?int
	{
		return $this->iblockId ?? null;
	}

	private function getPropertyIndex(int $propertyId): string
	{
		return self::FIELD_NAME_PREFIX . $propertyId;
	}

	protected function init(): void
	{
		$iblockId = $this->getIblockId();
		if ($iblockId)
		{
			$fields = [];

			$iterator = PropertyTable::getList([
				'select' => [
					'ID',
				],
				'filter' => [
					'=IBLOCK_ID' => $this->getIblockId(),
					'=ACTIVE' => 'Y',
					'!=PROPERTY_TYPE' => PropertyTable::TYPE_FILE,
				],
				'order' => [
					'ID' => 'ASC',
				],
				'cache' => [
					'ttl' => 86400,
				],
			]);
			while ($row = $iterator->fetch())
			{
				$propertyIndex = $this->getPropertyIndex((int)$row['ID']);
				$fields[] = $propertyIndex;
			}
			unset(
				$row,
				$iterator,
			);

			$this->setFields($fields);
		}
	}
}
