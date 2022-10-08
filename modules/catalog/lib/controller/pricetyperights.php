<?php

namespace Bitrix\Catalog\Controller;

use Bitrix\Main\Engine\CurrentUser;
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
			!CurrentUser::get()->CanDoOperation(Controller::CATALOG_READ)
			&& !CurrentUser::get()->CanDoOperation(Controller::CATALOG_GROUP)
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

		if (!CurrentUser::get()->CanDoOperation(Controller::CATALOG_GROUP))
		{
			$result->addError(new Error('Access Denied'));
		}

		return $result;
	}
}
