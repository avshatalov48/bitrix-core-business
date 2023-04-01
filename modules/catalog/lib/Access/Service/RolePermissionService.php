<?php
namespace Bitrix\Catalog\Access\Service;

use Bitrix\Catalog\Access\Component\PermissionConfig;
use Bitrix\Catalog\Access\Permission\Catalog\IblockCatalogPermissionStepper;
use Bitrix\Catalog\Access\Permission\PermissionDictionary;
use Bitrix\Main\Event;
use Bitrix\Main\DB\SqlQueryException;
use Bitrix\Main\Text\Encoding;
use Bitrix\Catalog\Access\Permission\PermissionTable;
use Bitrix\Catalog\Access\Role\RoleTable;
use Bitrix\Catalog\Access\Role\RoleUtil;
use Bitrix\Main\Application;
use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\SystemException;
use Throwable;

class RolePermissionService implements RolePermissionServiceInterface
{
	private const DB_ERROR_KEY = "CATALOG_CONFIG_PERMISSIONS_DB_ERROR";
	private const EVENT_ON_BEFORE_SAVE = "onBeforeCatalogRolePermissionSave";
	private const EVENT_ON_AFTER_SAVE = "onAfterCatalogRolePermissionSave";

	/**
	 * @var RoleRelationServiceInterface
	 */
	private $roleRelationService;

	/**
	 * @param array $permissionSettings permission settings array
	 *
	 * @return array
	 *
	 * @throws SqlQueryException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function saveRolePermissions(array $permissionSettings): array
	{
		$event = new Event(
			'catalog',
			self::EVENT_ON_BEFORE_SAVE,
			$permissionSettings
		);
		$event->send();

		$query = [];
		$roles = [];

		$catalogStoreDocumentPermissions = PermissionDictionary::getStoreDocumentPermissionRules(
			[
				PermissionDictionary::CATALOG_STORE_DOCUMENT_VIEW,
				PermissionDictionary::CATALOG_STORE_DOCUMENT_MODIFY,
				PermissionDictionary::CATALOG_STORE_DOCUMENT_CANCEL,
				PermissionDictionary::CATALOG_STORE_DOCUMENT_CONDUCT,
				PermissionDictionary::CATALOG_STORE_DOCUMENT_DELETE,
				PermissionDictionary::CATALOG_STORE_DOCUMENT_ALLOW_NEGATION_PRODUCT_QUANTITY,
			]
		);

		foreach ($permissionSettings as &$setting)
		{
			$roleId = (int)$setting['id'];
			$roleTitle = (string)$setting['title'];

			$roleId = $this->saveRole($roleTitle, $roleId);
			$setting['id'] = $roleId;
			$roles[] = $roleId;

			if(!isset($setting['accessRights']))
			{
				continue;
			}

			foreach ($setting['accessRights'] as $permission)
			{
				$permissionId =
					in_array($permission['id'], $catalogStoreDocumentPermissions, true)
						? $permission['id']
						: (int)$permission['id']
				;

				if ($permissionId < 1)
				{
					continue;
				}

				$query[] = new SqlExpression(
					'(?i, ?, ?i)',
					$roleId,
					$permissionId,
					$permission['value']
				);
			}
		}

		if ($query)
		{
			$db = Application::getConnection();

			try
			{
				$db->startTransaction();

				if (!PermissionTable::deleteList(["=ROLE_ID" => $roles]))
				{
					throw new SqlQueryException(self::DB_ERROR_KEY);
				}

				RoleUtil::insertPermissions($query);
				if (\Bitrix\Main\Loader::includeModule("intranet"))
				{
					\CIntranetUtils::clearMenuCache();
				}

				$this->roleRelationService->saveRoleRelation($permissionSettings);

				$db->commitTransaction();

				IblockCatalogPermissionStepper::bind(1);
			}
			catch (\Exception $e)
			{
				$db->rollbackTransaction();

				throw new SqlQueryException(self::DB_ERROR_KEY);
			}
		}

		$event = new Event(
			'catalog',
			self::EVENT_ON_AFTER_SAVE,
			$permissionSettings
		);
		$event->send();

		return $permissionSettings;
	}

	/**
	 * @param string $name
	 * @param int|null $roleId  role identification number
	 *
	 * @return int
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function saveRole(string $name, int $roleId = null): int
	{
		$nameField = [
			"NAME" => Encoding::convertEncodingToCurrent($name),
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
		$db = Application::getConnection();

		try
		{
			$db->startTransaction();

			PermissionTable::deleteList(["=ROLE_ID" => $roleId]);

			$this->roleRelationService->deleteRoleRelations($roleId);

			/**
			 * @var \Bitrix\Main\ORM\Data\DeleteResult
			 */
			$result = RoleTable::delete($roleId);
			if (!$result->isSuccess())
			{
				throw new SqlQueryException(self::DB_ERROR_KEY);
			}

			$db->commitTransaction();
		}
		catch (Throwable $e)
		{
			$db->rollbackTransaction();

			throw $e;
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
	 * Append saved inventory management permissions.
	 *
	 * May be used to save roles when inventory management is disabled,because these rights will not be shown in the interface,
	 * respectively, they will not be saved.
	 *
	 * @param array $permissionSettings
	 *
	 * @return array
	 */
	public function appendInventoryManagmentPermissions(array $permissionSettings): array
	{
		$inventoryManagementPermissions = (new PermissionConfig)->getInventoryManagementPermissions();

		foreach ($permissionSettings as &$setting)
		{
			$roleId = (int)$setting['id'];
			if (!$roleId)
			{
				continue;
			}

			$newRights = $setting['accessRights'] ?? [];
			if (!is_array($newRights))
			{
				throw new SystemException('Parameter `acessRights` must be array');
			}

			$inventoryManagementRights = array_map(
				static function (array $item) {
					return [
						'id' => $item['PERMISSION_ID'],
						'value' => $item['VALUE'],
					];
				},
				$this->getSavedPermissions([
					'filter' => [
						'=ROLE_ID' => $roleId,
						'=PERMISSION_ID' => $inventoryManagementPermissions,
					],
				])
			);

			$diffRights = array_udiff($inventoryManagementRights, $newRights, static function ($a, $b) {
				$a = (string)$a['id'];
				$b = (string)$b['id'];

				return $a <=> $b;
			});
			if (empty($diffRights))
			{
				continue;
			}

			array_push($newRights, ... $diffRights);

			$setting['accessRights'] = $newRights;
		}

		return $permissionSettings;
	}

	/**
	 * Map of access codes and roles.
	 *
	 * @return array in format `[roleId => [accessCode, accessCode, ...]]`
	 */
	private function getAccessCodesMap(): array
	{
		$result = [];

		$rows = $this->roleRelationService->getRelationList([
			'select' => [
				'ROLE_ID',
				'RELATION',
			],
		]);
		foreach ($rows as $row)
		{
			$roleId = (int)$row['ROLE_ID'];

			$result[$roleId] ??= [];
			$result[$roleId][] = (string)$row['RELATION'];
		}

		return $result;
	}
}
