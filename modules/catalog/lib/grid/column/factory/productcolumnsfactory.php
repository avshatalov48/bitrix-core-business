<?php

namespace Bitrix\Catalog\Grid\Column\Factory;

use Bitrix\Catalog\Config\State;
use Bitrix\Catalog\Grid\Column\BarcodeProvider;
use Bitrix\Catalog\Grid\Column\MeasureRatioProvider;
use Bitrix\Catalog\Grid\Column\PriceProvider;
use Bitrix\Catalog\Grid\Column\ProductColumns;
use Bitrix\Catalog\Grid\Column\ProductProvider;
use Bitrix\Catalog\Grid\Settings\ProductSettings;
use Bitrix\Iblock\Grid\Column\BusinessProcessProvider;
use Bitrix\Iblock\Grid\Column\ElementPropertyProvider;
use Bitrix\Iblock\Grid\Column\ElementProvider;
use Bitrix\Iblock\Grid\Column\WorkflowProvider;
use Bitrix\Main\Loader;

Loader::requireModule('iblock');

class ProductColumnsFactory
{
	public function create(ProductSettings $settings): ProductColumns
	{
		$providers = [
			// iblock
			new ElementProvider($settings),
			new ElementPropertyProvider($settings),
			new WorkflowProvider($settings),
			new BusinessProcessProvider($settings),

			// catalog
			new ProductProvider($settings),
			new PriceProvider($settings),
			new MeasureRatioProvider($settings),
		];

		if (!State::isUsedInventoryManagement())
		{
			$providers[] = new BarcodeProvider($settings);
		}

		return new ProductColumns(...$providers);
	}
}
