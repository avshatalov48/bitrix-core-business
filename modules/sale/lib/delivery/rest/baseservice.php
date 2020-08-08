<?php

namespace Bitrix\Sale\Delivery\Rest;

use Bitrix\Main,
	Bitrix\Rest\AccessException;

if (!Main\Loader::includeModule('rest'))
{
	return;
}

class BaseService extends \IRestService
{
	/**
	 * @throws AccessException
	 * @throws Main\LoaderException
	 */
	protected static function checkDeliveryPermission(): void
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
			$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
			if ($saleModulePermissions < "W")
			{
				throw new AccessException();
			}
		}
	}

	/**
	 * @param array $data
	 * @return array
	 */
	protected static function prepareParams(array $data): array
	{
		return array_change_key_case($data, CASE_UPPER);
	}
}