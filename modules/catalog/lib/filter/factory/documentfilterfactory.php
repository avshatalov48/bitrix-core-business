<?php

namespace Bitrix\Catalog\Filter\Factory;

use Bitrix\Catalog\Filter\DataProvider\DocumentDataProvider;
use Bitrix\Main\Filter\Filter;
use Bitrix\Main\Filter\Settings;

class DocumentFilterFactory
{
	public function createBySettings(string $mode, Settings $settings, array $extraDataProviders = null): Filter
	{
		return new Filter(
			$settings->getID(),
			new DocumentDataProvider($mode, $settings),
			$extraDataProviders
		);
	}
}
