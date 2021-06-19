<?php

namespace Bitrix\Catalog\Controller;

use Bitrix\Currency\CurrencyManager;
use Bitrix\Main\Engine;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Response;

class ProductForm extends Engine\Controller
{
	protected function getDefaultPreFilters()
	{
		return array_merge(
			parent::getDefaultPreFilters(),
			[
				new ActionFilter\HttpMethod([ActionFilter\HttpMethod::METHOD_POST]),
				new ActionFilter\Scope(ActionFilter\Scope::AJAX),
			]
		);
	}

	public function setConfigAction($configName, $value): void
	{
		if ($configName === 'showTaxBlock' || $configName === 'showDiscountBlock')
		{
			$value = ($value === 'N') ? 'N' : 'Y';
			\CUserOptions::SetOption("catalog.product-form", $configName, $value);
		}
	}
}
