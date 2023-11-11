<?php

namespace Bitrix\Catalog\Filter\DataProvider\Currency;

use Bitrix\Currency\CurrencyManager;
use Bitrix\Main\Loader;

trait CurrencyListItems
{
	private function getCurrencyListItems(): array
	{
		if (Loader::includeModule('currency'))
		{
			return CurrencyManager::getNameList();
		}

		return [];
	}
}
