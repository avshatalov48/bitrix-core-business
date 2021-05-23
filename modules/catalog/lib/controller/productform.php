<?php

namespace Bitrix\Catalog\Controller;

use Bitrix\Main\Engine;
use Bitrix\Main\Engine\ActionFilter;

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

	public function setConfigAction($configName, $value): ?array
	{
		if ($configName === 'showTaxBlock' || $configName === 'showDiscountBlock')
		{
			$value = ($value === 'N') ? 'N' : 'Y';
			\CUserOptions::SetOption("catalog.product-form", $configName, $value);
		}
	}
}
