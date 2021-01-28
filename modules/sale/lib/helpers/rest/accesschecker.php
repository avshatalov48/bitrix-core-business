<?php

namespace Bitrix\Sale\Helpers\Rest;

use Bitrix\Main;
use Bitrix\Rest\AccessException;

/**
 * Class Permissions
 * @package Bitrix\Sale\Helpers\Rest
 */
class AccessChecker
{
	/**
	 * @throws AccessException
	 * @throws Main\LoaderException
	 */
	public static function checkAccessPermission()
	{
		global $APPLICATION, $USER;
		if (Main\ModuleManager::isModuleInstalled('intranet') && Main\Loader::includeModule('crm'))
		{
			$CrmPerms = new \CCrmPerms($USER->GetID());
			if (!$CrmPerms->HavePerm('CONFIG', BX_CRM_PERM_CONFIG, 'WRITE'))
			{
				throw new AccessException();
			}
		}
		else
		{
			$saleModulePermissions = $APPLICATION::GetGroupRight("sale");
			if ($saleModulePermissions < "W")
			{
				throw new AccessException();
			}
		}
	}
}