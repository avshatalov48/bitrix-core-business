<?php

namespace Bitrix\Sale\Helpers\Rest;

use Bitrix\Crm\Service\Container;
use Bitrix\Main;
use Bitrix\Rest\AccessException;

/**
 * Class Permissions
 * @package Bitrix\Sale\Helpers\Rest
 * @internal
 */
class AccessChecker
{
	/**
	 * @throws AccessException
	 */
	public static function checkAccessPermission()
	{
		global $APPLICATION;

		if (Main\ModuleManager::isModuleInstalled('intranet') && Main\Loader::includeModule('crm'))
		{
			if (!Container::getInstance()->getUserPermissions()->isCrmAdmin())
			{
				throw new AccessException();
			}
		}
		else
		{
			$saleModulePermissions = $APPLICATION::GetGroupRight('sale');
			if ($saleModulePermissions < 'W')
			{
				throw new AccessException();
			}
		}
	}
}
