<?php

namespace Bitrix\Catalog\Controller;

use Bitrix\Catalog\Access\AccessController;
use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Main\Error;
use Bitrix\Main\Result;

trait PriceTypeRights
{
	/**
	 * @return Result
	 */
	protected function checkReadPermissionEntity(): Result
	{
		$r = new Result();

		if (
			!AccessController::getCurrent()->check(ActionDictionary::ACTION_CATALOG_READ)
			&& !AccessController::getCurrent()->check(ActionDictionary::ACTION_PRICE_GROUP_EDIT)
		)
		{
			$r->addError(new Error('Access Denied'));
		}

		return $r;
	}

	/**
	 * @return Result
	 */
	protected function checkModifyPermissionEntity(): Result
	{
		$result = new Result();

		if (!AccessController::getCurrent()->check(ActionDictionary::ACTION_PRICE_GROUP_EDIT))
		{
			$result->addError(new Error('Access Denied'));
		}

		return $result;
	}
}
