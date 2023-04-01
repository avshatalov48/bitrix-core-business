<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Error;
use Bitrix\Catalog\Access\AccessController;
use Bitrix\Catalog\Access\Service\RolePermissionService;
use Bitrix\Catalog\Config\State;

if (!Bitrix\Main\Loader::includeModule('catalog'))
{
	return;
}

class CatalogConfigPermissionsAjaxController extends \Bitrix\Main\Engine\Controller
{
	public function savePermissionsAction(array $userGroups, array $parameters = []): ?array
	{
		if (
			!$userGroups
			|| !AccessController::can(CurrentUser::get()->getId(), ActionDictionary::ACTION_CATALOG_RIGHTS_EDIT)
		)
		{
			return null;
		}

		try
		{
			$rolePermissionService = new RolePermissionService();
			if (!State::isUsedInventoryManagement())
			{
				$userGroups = $rolePermissionService->appendInventoryManagmentPermissions($userGroups);
			}

			$userGroups = $rolePermissionService->saveRolePermissions($userGroups);

			return $this->loadData();
		}
		catch (\Exception $e)
		{
			$this->errorCollection[] = new Error(Loc::getMessage('CATALOG_CONFIG_PERMISSIONS_DB_ERROR'));
		}

		return null;
	}

	public function deleteRoleAction(int $roleId): void
	{
		if (!AccessController::can(CurrentUser::get()->getId(), ActionDictionary::ACTION_CATALOG_RIGHTS_EDIT))
		{
			return;
		}

		try
		{
			(new RolePermissionService())->deleteRole($roleId);
		}
		catch (\Bitrix\Main\DB\SqlQueryException $e)
		{
			$this->errorCollection[] = new Error(Loc::getMessage('CATALOG_CONFIG_ROLE_DELETE_DB_ERROR'));
		}
	}

	/**
	 *
	 * @return null | array
	 */
	public function loadAction(): ?array
	{
		if (!AccessController::can(CurrentUser::get()->getId(), ActionDictionary::ACTION_CATALOG_RIGHTS_EDIT))
		{
			return null;
		}

		return $this->loadData();
	}

	/**
	 * @return array
	 */
	private function loadData(): array
	{
		$configPermissions = new \Bitrix\Catalog\Access\Component\PermissionConfig();

		return [
			'USER_GROUPS' => $configPermissions->getUserGroups(),
			'ACCESS_RIGHTS' => $configPermissions->getAccessRights()
		];
	}
}
