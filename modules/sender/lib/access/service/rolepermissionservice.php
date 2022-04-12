<?php
namespace Bitrix\Sender\Access\Service;

use Bitrix\Main\Access\AccessCode;
use Bitrix\Main\DB\SqlQueryException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Text\Encoding;
use Bitrix\Main\UI\AccessRights\DataProvider;
use Bitrix\Sender\Access\Permission\PermissionDictionary;
use Bitrix\Sender\Access\Permission\PermissionTable;
use Bitrix\Sender\Access\Role\RoleTable;
use Bitrix\Sender\Access\Role\RoleUtil;
use Bitrix\Sender\Access\SectionDictionary;
use Bitrix\Sender\Integration\Bitrix24\Service;

Loc::loadMessages(__FILE__);

class RolePermissionService implements RolePermissionServiceInterface
{
	private const DB_ERROR_KEY = "SENDER_CONFIG_PERMISSIONS_DB_ERROR";

	/**
	 * @var RoleRelationServiceInterface
	 */
	private $roleRelationService;

	/**
	 * @param array $permissionSettings permission settings array
	 *
	 * @param int $dealCategoryId deal category identification number
	 *
	 * @throws SqlQueryException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function saveRolePermissions(array &$permissionSettings, int $dealCategoryId = -1): void
	{
		$query = [];
		$roles = [];

		foreach ($permissionSettings as &$setting)
		{

			$roleId = (int) $setting['id'];
			$roleTitle = (string) $setting['title'];

			$roleId = $this->saveRole($roleTitle, $dealCategoryId, $roleId);
			$setting['id'] = $roleId;
			$roles[] = $roleId;

			if(!isset($setting['accessRights']))
			{
				continue;
			}

			foreach ($setting['accessRights'] as $permission)
			{
				if((int)$permission['id'] < 1)
				{
					continue;
				}

				$query[] = sprintf(
					'(%d, %d, %d)',
					$roleId,
					(int)$permission['id'],
					$permission['value']
				);
			}
		}

		if($query)
		{
			if (!PermissionTable::deleteList(["=ROLE_ID" =>$roles]))
			{
				throw new SqlQueryException(Loc::getMessage(self::DB_ERROR_KEY));
			}

			try
			{
				RoleUtil::insertPermissions($query);
				if (\Bitrix\Main\Loader::includeModule("intranet"))
				{
					\CIntranetUtils::clearMenuCache();
				}
			} catch (\Exception $e)
			{
				throw new SqlQueryException(self::DB_ERROR_KEY);
			}
		}
	}

	/**
	 * @param string $name
	 * @param int $dealCategoryId deal category identification number
	 * @param int|null $roleId  role identification number
	 *
	 * @return int
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function saveRole(string $name, int $dealCategoryId = -1, int $roleId = null): int
	{
		$nameField = [
			"NAME" => Encoding::convertEncodingToCurrent($name),
			"DEAL_CATEGORY_ID" => $dealCategoryId
		];

		try
		{
			if(!$roleId)
			{
				if(!
					(
						$role = RoleTable::getList(
						['filter' => [
							'=NAME' => $nameField['NAME'],
							'=DEAL_CATEGORY_ID' => $nameField['DEAL_CATEGORY_ID']
						]])->fetchObject()
					)
				)
				{
					$role = RoleTable::add($nameField);
				}
			}
			else
			{
				$role = RoleTable::update($roleId, $nameField);
			}
		} catch (\Exception $e)
		{
			throw new SqlQueryException(self::DB_ERROR_KEY);
		}

		return $role->getId();
	}

	/**
	 * @param int $roleId role identification number
	 * @throws SqlQueryException
	 */
	public function deleteRole(int $roleId): void
	{
		if(!is_int($roleId))
		{
			return;
		}

		if (!PermissionTable::deleteList(["=ROLE_ID" => $roleId]))
		{
			throw new SqlQueryException(Loc::getMessage(self::DB_ERROR_KEY));
		}

		$this->roleRelationService->deleteRoleRelations($roleId);

		if (!RoleTable::delete($roleId))
		{
			throw new SqlQueryException(Loc::getMessage(self::DB_ERROR_KEY));
		}
	}

	public function __construct()
	{
		$this->roleRelationService = new RoleRelationService();
	}

	/**
	 * @inheritDoc
	 */
	public function getRoleList(array $parameters = []): array
	{
		return RoleTable::getList($parameters)->fetchAll();
	}

	/**
	 * @inheritDoc
	 */
	public function getSavedPermissions(array $parameters = []): array
	{
		return PermissionTable::getList($parameters)->fetchAll();
	}

	/**
	 * @inheritDoc
	 */
	public function getRoleListByUser(int $userId): array
	{
		$userAccessCodes = \CAccess::getUserCodesArray($userId);

		return $this->roleRelationService->getRolesByRelations($userAccessCodes);
	}

	/**
	 * @param int $dealCategoryId
	 *
	 * @return array
	 */
	public function getUserGroups(int $dealCategoryId): array
	{
		$res = $this->getRoleList(
			[
				"filter" => ["=DEAL_CATEGORY_ID" => $dealCategoryId]
			]
		);

		$roles = [];
		foreach ($res as $row)
		{
			$roleId = (int) $row['ID'];

			$roles[] = [
				'id' 			=> $roleId,
				'title' 		=> $row['NAME'],
				'accessRights' 	=> $this->getRoleAccessRights($roleId),
				'members' 		=> $this->getRoleMembers($roleId)
			];
		}

		return $roles;
	}

	/**
	 * returns access rights list
	 * @return array
	 */
	public function getAccessRights(): array
	{
		$sections = SectionDictionary::getMap();
		$adsAccessMap = SectionDictionary::getAdsAccessMap();

		$res = [];

		foreach ($sections as $sectionId => $permissions)
		{

			$rights = [];
			foreach ($permissions as $permissionId)
			{
				if(
					isset($adsAccessMap[$permissionId])
					&& !Service::isAdVisibleInRegion($adsAccessMap[$permissionId])
				)
				{
					continue;
				}
				$rights[] = [
					'id' => $permissionId,
					'type' => PermissionDictionary::getType($permissionId),
					'title' => PermissionDictionary::getTitle($permissionId)
				];
			}
			$res[] = [
				'sectionTitle' => SectionDictionary::getTitle($sectionId),
				'rights' => $rights
			];
		}

		return $res;
	}

	private function getRoleAccessRights(int $roleId): array
	{
		$settings = $this->getSettings();

		$accessRights = [];
		if (array_key_exists($roleId, $settings))
		{
			foreach ($settings[$roleId] as $permissionId => $permission)
			{
				$accessRights[] = [
					'id' => $permissionId,
					'value' => $permission['VALUE']
				];
			}
		}

		return $accessRights;
	}

	private function getMemberInfo(string $code)
	{
		$accessCode = new AccessCode($code);
		$member = (new DataProvider())->getEntity($accessCode->getEntityType(), $accessCode->getEntityId());
		return $member->getMetaData();
	}


	private function getRoleMembers(int $roleId): array
	{
		$members = [];

		$relations = $this
			->roleRelationService
			->getRelationList(["filter" =>["=ROLE_ID" => $roleId]]);

		foreach ($relations as $row)
		{
			$accessCode = $row['RELATION'];
			$members[$accessCode] = $this->getMemberInfo($accessCode);
		}

		return $members;
	}

	private function getSettings()
	{
		$settings = [];
		$res = $this->getSavedPermissions();

		foreach ($res as $row)
		{
			$settings[$row['ROLE_ID']][$row['PERMISSION_ID']] = $row;
		}
		return $settings;
	}
}