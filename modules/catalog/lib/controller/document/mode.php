<?php

namespace Bitrix\Catalog\Controller\Document;

use Bitrix\Catalog;
use Bitrix\Catalog\Access\AccessController;
use Bitrix\Catalog\Controller\Controller;
use Bitrix\Main\Engine;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Catalog\Config\State;
use Bitrix\Main\Error;

class Mode extends Engine\Controller
{
	public function statusAction(): ?string
	{
		if (!$this->checkPermissions())
		{
			return null;
		}

		return Catalog\Config\State::isUsedInventoryManagement() ? 'Y' : 'N';
	}

	public function statusDetailsAction(): ?array
	{
		if (!$this->checkPermissions())
		{
			return null;
		}

		return [
			'enabled' => State::isUsedInventoryManagement(),
			'mode' => Catalog\Store\EnableWizard\Manager::getCurrentMode(),
		];
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

	private function checkPermissions(): bool
	{
		if (
			!(
				AccessController::getCurrent()->check(Controller::CATALOG_STORE)
				|| AccessController::getCurrent()->check(Controller::CATALOG_READ)
			)
		)
		{
			$this->addError(new Error('Access denied'));

			return false;
		}

		return true;
	}
}
