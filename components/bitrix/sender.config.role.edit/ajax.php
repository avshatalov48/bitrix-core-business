<?php
define('STOP_STATISTICS', true);
define('BX_SECURITY_SHOW_MESSAGE', true);

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

use Bitrix\Main\Localization\Loc;
use Bitrix\Sender\Access\AccessController;
use Bitrix\Sender\Access\ActionDictionary;
use Bitrix\Sender\Access\Service\RolePermissionService;
use Bitrix\Sender\Security;
use Bitrix\Sender\Security\User;

if (!Bitrix\Main\Loader::includeModule('sender'))
{
	return;
}

class ConfigRoleEditSenderAjaxController extends \Bitrix\Main\Engine\Controller
{
	public function savePermissionsAction(array $userGroups, array $parameters)
	{

		if (!Security\Role\Manager::canUse())
		{
			return;
		}

		if(!AccessController::can(
			User::current()->getId(),
			ActionDictionary::ACTION_SETTINGS_EDIT
		))
		{
			return;
		}

		if (!is_array($userGroups) || empty($userGroups) || !check_bitrix_sessid())
		{
			return;
		}

		try
		{
			$permissionService = new RolePermissionService();

			$dealCategoryId = $parameters['dealCategoryId'] ?? 0;
			$permissionService
				->saveRolePermissions($userGroups, $dealCategoryId);

			(new \Bitrix\Sender\Access\Service\RoleRelationService())
				->saveRoleRelation($userGroups);

			return [
				'USER_GROUPS' => $permissionService->getUserGroups($dealCategoryId),
				'ACCESS_RIGHTS' => $permissionService->getAccessRights()
			];
		}
		catch (\Exception $e)
		{
			$this->errorCollection[] = new \Bitrix\Main\Error(Loc::getMessage('SENDER_CONFIG_PERMISSIONS_DB_ERROR'));
		}
	}

	public function deleteRoleAction(int $roleId)
	{
		if(!AccessController::can(
			User::current()->getId(),
			ActionDictionary::ACTION_SETTINGS_EDIT
		))
		{
			return;
		}

		if (!is_int($roleId) || !check_bitrix_sessid())
		{
			return;
		}

		try
		{
			(new RolePermissionService())->deleteRole($roleId);
		}
		catch (\Bitrix\Main\DB\SqlQueryException $e)
		{
			$this->errorCollection[] = new \Bitrix\Main\Error(
				Loc::getMessage('SENDER_CONFIG_ROLE_DELETE_DB_ERROR')
			);
		}
	}

	/**
	 *
	 * @param array $parameters
	 *
	 * @return array
	 */
	public function loadAction(array $parameters)
	{
		$dealCategoryId = $parameters['dealCategoryId'] ?? 0;
		$permissionService = new RolePermissionService();

		return [
			'USER_GROUPS' => $permissionService->getUserGroups($dealCategoryId),
			'ACCESS_RIGHTS' => $permissionService->getAccessRights()
		];
	}
}