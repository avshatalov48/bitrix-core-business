<?php

namespace Bitrix\Sale\Helpers\Rest;

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
		global $APPLICATION, $USER;

		if (Main\ModuleManager::isModuleInstalled('intranet') && Main\Loader::includeModule('crm'))
		{
			$crmPerms = new \CCrmPerms($USER->GetID());
			if (!$crmPerms->HavePerm('CONFIG', BX_CRM_PERM_CONFIG, 'WRITE'))
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
