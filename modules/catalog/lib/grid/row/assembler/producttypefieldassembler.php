<?php

namespace Bitrix\Catalog\Grid\Row\Assembler;

use Bitrix\Catalog\ProductTable;
use Bitrix\Main\Grid\Row\Assembler\Field\ListFieldAssembler;
use Bitrix\Main\Localization\Loc;

final class ProductTypeFieldAssembler extends ListFieldAssembler
{
	/**
	 * @inheritDoc
	 */
	protected function getNames(): array
	{
		return ProductTable::getProductTypes(true);
	}

	/**
	 * @inheritDoc
	 */
	protected function prepareRow(array $row): array
	{
		$row = parent::prepareRow($row);

		$bundle = $row['data']['BUNDLE'] ?? null;
		if ($bundle === 'Y')
		{
			$row['columns']['TYPE'] = Loc::getMessage('CATALOG_GRID_ROW_ASSEMBLER_PRODUCT_TYPE_BUNDLE_NAME', [
				'#TYPE#' => $row['columns']['TYPE'],
			]);

			return $row;
		}

		return $row;
	}
}
