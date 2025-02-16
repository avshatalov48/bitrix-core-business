<?php

namespace Bitrix\Catalog\Controller;

use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Catalog\MeasureRatioTable;
use Bitrix\Main\Error;
use Bitrix\Main\Result;

final class Ratio extends Controller
{
	use ListAction; // default listAction realization
	use GetAction; // default getAction realization
	use CheckExists; // default implementation of existence check

	//region Actions
	public function getFieldsAction(): array
	{
		return [$this->getServiceItemName() => $this->getViewFields()];
	}

	/**
	 * public function listAction
	 * @see ListAction::listAction
	 */

	/**
	 * public function getAction
	 * @see GetAction::getAction
	 */
	//endregion

	protected function getEntityTable()
	{
		return new MeasureRatioTable();
	}

	protected function checkModifyPermissionEntity()
	{
		$r = new Result();

		if (!$this->accessController->check(ActionDictionary::ACTION_PRICE_GROUP_EDIT))
		{
			$r->addError($this->getErrorModifyAccessDenied());
		}

		return $r;
	}

	protected function checkReadPermissionEntity()
	{
		$r = new Result();

		if (
			!(
				$this->accessController->check(ActionDictionary::ACTION_CATALOG_READ)
				|| $this->accessController->check(ActionDictionary::ACTION_PRICE_GROUP_EDIT)
			)
		)
		{
			$r->addError($this->getErrorReadAccessDenied());
		}
		return $r;
	}
}
