<?php
namespace Bitrix\Sender\Access\Service;

use Bitrix\Main;
use Bitrix\Main\DB\SqlQueryException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sender\Access\AccessController;
use Bitrix\Sender\Access\ActionDictionary;
use Bitrix\Sender\Access\Permission\PermissionDictionary;
use Bitrix\Sender\Access\Role\RoleUtil;

Loc::loadMessages(__FILE__);

class RoleDealCategoryService implements RoleDealCategoryServiceInterface
{
	public const ALL_CATEGORIES = -1;
	/**
	 * @var RolePermissionServiceInterface
	 */
	private $rolePermissionService;
	/**
	 * @inheritDoc
	 */

	public function __construct()
	{
		$this->rolePermissionService = new RolePermissionService();
	}

	/**
	 * get able deal categories by user id
	 * @param int $userId
	 *
	 * @return array
	 * @throws SqlQueryException
	 */
	public function getAbleDealCategories(int $userId): array
	{
		$categories = [];
		if(is_null($userId))
		{
			return $categories;
		}

		$roles = implode(',', $this->rolePermissionService->getRoleListByUser($userId));
		$roles = $roles ?:'\'\'';

		$ownCategory = PermissionDictionary::SEGMENT_CLIENT_OWN_CATEGORY;
		$valueYes = PermissionDictionary::VALUE_YES;
		$query = "
			SELECT DISTINCT `bsr`.`DEAL_CATEGORY_ID` AS `CATEGORY_ID` FROM `b_sender_permission` AS `bsp`
			JOIN `b_sender_role` AS `bsr` ON `bsr`.`ID` = `bsp`.`ROLE_ID`
			WHERE 
			`bsp`.`PERMISSION_ID` = {$ownCategory} 
			AND `bsp`.`VALUE` = {$valueYes}
			AND `bsp`.`ROLE_ID` IN ({$roles})
		";

		return Main\Application::getConnection()->query($query)->fetchAll();
	}

	/**
	 * @param int $userId
	 * @param array $categories
	 *
	 * @return array
	 * @throws SqlQueryException
	 */
	public function getFilteredDealCategories(int $userId, array $categories): array
	{
		$ableDealCategories = $this->getAbleDealCategories($userId);
		$dealCategories = [];

		$this->accessController = new AccessController($userId);
		$allowAll = $this->accessController->isAdmin();

		$dealCategories[''] =  $categories['']??Loc::getMessage('SENDER_DEAL_CATEGORY_WITHOUT_DEAL_PREP');
		foreach ($ableDealCategories as $ableDealCategory)
		{
			if((int)$ableDealCategory['CATEGORY_ID'] === self::ALL_CATEGORIES)
			{
				$allowAll = true;
				break;
			}
		}

		if(!$allowAll)
		{
			foreach ($ableDealCategories as $ableDealCategory)
			{
				$dealCategories[$ableDealCategory['CATEGORY_ID']] = $categories[$ableDealCategory['CATEGORY_ID']];
			}
			return $dealCategories;
		}

		return $dealCategories + $categories;
	}


	/**
	 * @param int $dealCategoryId
	 *
	 * @return array
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 * @throws SqlQueryException
	 *
	 */
	public function fillDefaultDealCategoryPermission(int $dealCategoryId): array
	{
		$query = [];
		$map = RoleUtil::preparedRoleMap();

		$managerRoleId = null;
		$adminRoleId = null;
		foreach ($map as $roleKey => $permissions)
		{
			$roleName = RoleUtil::getLocalizedName($roleKey);

			$roleId = $this->rolePermissionService->saveRole($roleName, $dealCategoryId);
			$query = array_merge($query, RoleUtil::buildInsertPermissionQuery($permissions, $roleId));

			if ($roleKey === 'MANAGER')
			{
				$managerRoleId = $roleId;
			}

			if ($roleKey === 'ADMIN')
			{
				$adminRoleId = $roleId;
			}
		}

		RoleUtil::insertPermissions($query);
		(new RoleRelationService())->saveRoleRelation([
			[
				'id' => $managerRoleId,
				'accessCodes' => [
					'AE0' => 'usergroups'
				],
			],
			[
				'id' => $adminRoleId,
				'accessCodes' => [
					'G1' => ''
				],
			],
		]);

		return $this->rolePermissionService->getRoleList(
			[

				"filter" => ["=DEAL_CATEGORY_ID" => $dealCategoryId]
			]
		);
	}
}