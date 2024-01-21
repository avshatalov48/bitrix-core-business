<?php

namespace Bitrix\Catalog\Filter\Factory;

use Bitrix\Catalog\Filter\DataProvider\ProductDataProvider;
use Bitrix\Catalog\Filter\DataProvider\Settings\ProductSettings;
use Bitrix\Catalog\Filter\ProductFilter;

class ProductFilterFactory
{
	public function createBySettings(ProductSettings $settings): ProductFilter
	{
		return new ProductFilter(
			$settings->getID(),
			new ProductDataProvider($settings)
		);
	}
}
