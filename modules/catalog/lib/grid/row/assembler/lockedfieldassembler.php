<?php

namespace Bitrix\Catalog\Grid\Row\Assembler;

use Bitrix\Catalog\Grid\Settings\ProductSettings;
use Bitrix\Iblock\Grid\RowType;
use Bitrix\Main\Grid\Row\FieldAssembler;
use Bitrix\Main\Loader;
use CCatalogAdminTools;

Loader::requireModule('iblock');

/**
 * @method ProductSettings getSettings()
 */
final class LockedFieldAssembler extends FieldAssembler
{
	/**
	 * @var array[]
	 */
	private array $lockedColumns;

	/**
	 * @param ProductSettings $settings
	 */
	public function __construct(ProductSettings $settings)
	{
		$this->initLockedColumns($settings);

		parent::__construct(
			$this->getProcessedColumnIds(),
			$settings
		);
	}

	/**
	 * @param ProductSettings $settings
	 *
	 * @return void
	 */
	private function initLockedColumns(ProductSettings $settings): void
	{
		$this->lockedColumns = [];

		$lockedFieldNames = CCatalogAdminTools::getLockedGridFields([
			'USE_NEW_CARD' => $settings->isNewCardEnabled(),
		]);

		$removePrefix = 'CATALOG_';
		foreach ($lockedFieldNames as $productType => $map)
		{
			foreach ($map as $columnId => $value)
			{
				$columnId = str_replace($removePrefix, '', $columnId);
				$this->lockedColumns[$productType][$columnId] = $value === false;
			}
		}
	}

	/**
	 * All columns that can be used.
	 *
	 * @return string[]
	 */
	private function getProcessedColumnIds(): array
	{
		if (empty($this->lockedColumns))
		{
			return [];
		}

		return array_keys(reset($this->lockedColumns));
	}

	/**
	 * @param array $row
	 * @param string $columnId
	 *
	 * @return bool
	 */
	private function isLockedField(array $row, string $columnId): bool
	{
		$rowType = $row['data']['ROW_TYPE'] ?? RowType::ELEMENT;
		$productType = (int)($row['data']['TYPE'] ?? 0);

		return
			$rowType === RowType::ELEMENT
			&& isset($this->lockedColumns[$productType][$columnId])
			&& $this->lockedColumns[$productType][$columnId] === true
		;
	}

	#region override

	/**
	 * @inheritDoc
	 */
	protected function prepareRow(array $row): array
	{
		if (empty($this->getColumnIds()))
		{
			return $row;
		}

		foreach ($this->getColumnIds() as $columnId)
		{
			$isLocked = $this->isLockedField($row, $columnId);
			if ($isLocked)
			{
				$row['editableColumns'][$columnId] = false;
			}
		}

		return $row;
	}

	#endregion override
}
