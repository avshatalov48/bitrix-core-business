<?php

namespace Bitrix\Catalog\Controller\Document;

use Bitrix\Catalog;
use Bitrix\Catalog\Controller\Controller;
use Bitrix\Main\Engine;
use Bitrix\Main\Engine\ActionFilter;

class Mode extends Engine\Controller
{
	/**
	 * @return string|null
	 */
	public function statusAction(): ?string
	{
		$currentUser = Engine\CurrentUser::get();

		if (
			$currentUser->canDoOperation(Controller::CATALOG_STORE)
			|| $currentUser->canDoOperation(Controller::CATALOG_READ)
		)
		{
			return Catalog\Config\State::isUsedInventoryManagement() ? 'Y' : 'N';
		}

		$this->addError(new \Bitrix\Main\Error('Access denied'));

		return null;
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
