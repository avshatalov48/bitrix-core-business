<?php
namespace Bitrix\Sender\Access\Install;

use Bitrix\Sender\Access\Role\RoleTable;
use Bitrix\Sender\Access\Role\RoleUtil;
use Bitrix\Sender\Access\Service\RolePermissionService;

class AccessInstaller
{
	/**
	 * Use for install agent and install data to DB
	 * @return string
	 */
	public static function installAgent()
	{
		self::fillSystemPermissions();
		return '';
	}

	/**
	 * fill data by presetted array
	 */
	private static function fillSystemPermissions() :void
	{
		$map = RoleUtil::preparedRoleMap();

		$query = [];
		try
		{
			$oldRolesList = RoleTable::getList(['select' => ['ID', 'XML_ID']])->fetchAll();
			$xmlIds       = array_flip(array_column($oldRolesList, 'XML_ID'));
			foreach ($map as $roleKey => $permissions)
			{
				$roleName = RoleUtil::getLocalizedName($roleKey);

				$roleId = isset($xmlIds[$roleKey])
					? $oldRolesList[$xmlIds[$roleKey]]['ID']
					: (new RolePermissionService())->saveRole($roleName);

				$query = array_merge($query, RoleUtil::buildInsertPermissionQuery($permissions, $roleId));
			}

			RoleUtil::insertPermissions($query);
		} catch (\Exception $e)
		{
		}
	}
}