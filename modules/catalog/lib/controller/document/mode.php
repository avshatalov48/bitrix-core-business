<?php

namespace Bitrix\Catalog\Controller\Document;

use Bitrix\Main\Engine;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Config\Option;

class Mode extends Engine\Controller
{
	public function statusAction(): string
	{
		return Option::get('catalog', 'default_use_store_control', 'N') === 'Y' ? 'Y' : 'N';
	}

	protected function getDefaultPreFilters()
	{
		return array_merge(
			parent::getDefaultPreFilters(),
			[
				new ActionFilter\Scope(ActionFilter\Scope::REST),
			]
		);
	}
}
