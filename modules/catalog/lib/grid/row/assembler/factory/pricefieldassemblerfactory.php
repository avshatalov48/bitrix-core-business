<?php

namespace Bitrix\Catalog\Grid\Row\Assembler\Factory;

use Bitrix\Catalog\Grid\Column\PriceProvider;
use Bitrix\Catalog\Grid\Row\Assembler\PriceFieldAssembler;
use Bitrix\Catalog\GroupTable;

class PriceFieldAssemblerFactory
{
	public function createForCatalogPrices(): PriceFieldAssembler
	{
		$columnIds = [];

		foreach (GroupTable::getTypeList() as $type)
		{
			$columnIds[] = PriceProvider::getPriceTypeColumnId(
				(int)$type['ID']
			);
		}

		return new PriceFieldAssembler($columnIds);
	}
}
