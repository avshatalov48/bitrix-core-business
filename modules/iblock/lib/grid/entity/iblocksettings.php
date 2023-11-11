<?php

namespace Bitrix\Iblock\Grid\Entity;

use Bitrix\Iblock\IblockTable;
use Bitrix\Main;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\SystemException;

class IblockSettings extends Main\Grid\Settings
{
	protected array $iblockFields;

	private int $iblockId;
	private string $iblockTypeId;
	private string $listMode;
	private bool $isAllowedIblockSections = false;
	private bool $isShowedXmlId = false;

	public function __construct(array $params)
	{
		parent::__construct($params);

		$this->iblockId = $params['IBLOCK_ID'] ?? 0;
		if ($this->iblockId === 0)
		{
			throw new ArgumentException('Collection does not contain value for iblock id.', 'params');
		}

		$this->init();
	}

	protected function init(): void
	{
		$this->iblockFields = IblockTable::getRow([
			'select' => [
				'*',
				'TYPE_SECTIONS' => 'TYPE.SECTIONS',
			],
			'filter' => [
				'=ID' => $this->iblockId,
				'=ACTIVE' => 'Y',
			],
		]);
		if (empty($this->iblockFields))
		{
			throw new SystemException('Not found active iblock');
		}

		$this->iblockTypeId = $this->iblockFields['IBLOCK_TYPE_ID'];
		$this->isAllowedIblockSections = $this->iblockFields['TYPE_SECTIONS'] === 'Y';

		if (
			$this->iblockFields['LIST_MODE'] === IblockTable::LIST_MODE_SEPARATE
			|| $this->iblockFields['LIST_MODE'] === IblockTable::LIST_MODE_COMBINED
		)
		{
			$this->listMode = $this->iblockFields['LIST_MODE'];
		}
		else
		{
			$this->listMode =
				Option::get('iblock', 'combined_list_mode') === 'Y'
					? IblockTable::LIST_MODE_COMBINED
					: IblockTable::LIST_MODE_SEPARATE
			;
		}
	}

	public function getIblockId(): int
	{
		return $this->iblockId;
	}

	public function getIblockTypeId(): string
	{
		return $this->iblockTypeId;
	}

	public function getListMode(): string
	{
		return $this->listMode;
	}

	public function setListMode(string $value): self
	{
		$this->listMode = $value;

		return $this;
	}

	public function isAllowedIblockSections(): bool
	{
		return $this->isAllowedIblockSections;
	}

	public function setAllowedIblockSections(bool $value): self
	{
		$this->isAllowedIblockSections = $value;

		return $this;
	}

	public function isShowedXmlId(): bool
	{
		return $this->isShowedXmlId;
	}

	public function setShowedXmlId(bool $value): self
	{
		$this->isShowedXmlId = $value;

		return $this;
	}
}
