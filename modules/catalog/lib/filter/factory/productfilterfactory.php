<?php

namespace Bitrix\Catalog\Filter\Factory;

use Bitrix\Catalog\Filter\DataProvider\ProductDataProvider;
use Bitrix\Catalog\Filter\DataProvider\Settings\ProductSettings;
use Bitrix\Main\Filter\Filter;

class ProductFilterFactory
{
	public function createBySettings(ProductSettings $settings): Filter
	{
		return new Filter(
			$settings->getID(),
			new ProductDataProvider($settings)
		);
	}
}
