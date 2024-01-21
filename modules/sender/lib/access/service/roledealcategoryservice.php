<?php
namespace Bitrix\Sender\Access\Service;

use Bitrix\Main;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\DB\SqlQueryException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Bitrix\Sender\Access\AccessController;
use Bitrix\Sender\Access\ActionDictionary;
use Bitrix\Sender\Access\Permission\PermissionDictionary;
use Bitrix\Sender\Access\Role\RoleUtil;
use Bitrix\Sender\Access\Permission\PermissionTable;
use Bitrix\Sender\Access\Role\RoleTable;
use Bitrix\Main\ORM\Fields\Relations\Reference;
use Bitrix\Main\ORM\Query\Join;

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
	 * Get able deal categories by user id
	 *
	 * @param int $userId User ID
	 *
	 * @return array [[CATEGORY_ID => 0], ...]
	 */
	public function getAbleDealCategories(int $userId): array
	{
		return PermissionTable::query()
			->registerRuntimeField('role',
				(new Reference('role',RoleTable::class, Join::on('this.ROLE_ID', 'ref.ID')))
				->configureJoinType(Join::TYPE_INNER)
			)
			->setDistinct()
			->setSelect(['CATEGORY_ID' => 'role.DEAL_CATEGORY_ID'])
			->where('PERMISSION_ID', PermissionDictionary::SEGMENT_CLIENT_OWN_CATEGORY)
			->where('VALUE', PermissionDictionary::VALUE_YES)
			->whereIn('ROLE_ID', $this->rolePermissionService->getRoleListByUser($userId))
			->fetchAll();
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

		$dealCategories[''] =  $categories['']??Loc::getMessage('SENDER_DEAL_CATEGORY_WITHOUT_DEAL_PREP_MSG_1');
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
