<?php
namespace Bitrix\Catalog\Access\Component;

use Bitrix\Catalog\Access\Component\PermissionConfig\RoleMembersInfo;
use Bitrix\Catalog\Access\Permission\PermissionDictionary;
use Bitrix\Catalog\Access\Permission\PermissionTable;
use Bitrix\Catalog\Access\Permission\PermissionArticles;
use Bitrix\Catalog\StoreDocumentTable;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Catalog\Access\Role\RoleUtil;
use Bitrix\Catalog\Access\Role\RoleDictionary;
use Bitrix\Catalog\Config\State;
use Bitrix\Main\ModuleManager;

Loc::loadMessages(__FILE__);

class PermissionConfig
{
	public const SECTION_CATALOG = 'SECTION_CATALOG';
	public const SECTION_CATALOG_SETTINGS = 'SECTION_CATALOG_SETTINGS';
	public const SECTION_INVENTORY_MANAGMENT = 'SECTION_INVENTORY_MANAGMENT';
	public const SECTION_SETTINGS = 'SECTION_SETTINGS';
	public const SECTION_RESERVATION = 'SECTION_RESERVATION';
	public const SECTION_STORE_DOCUMENT_ARRIVAL = 'SECTION_STORE_DOCUMENT_ARRIVAL';
	public const SECTION_STORE_DOCUMENT_STORE_ADJUSTMENT = 'SECTION_STORE_DOCUMENT_STORE_ADJUSTMENT';
	public const SECTION_STORE_DOCUMENT_MOVING = 'SECTION_STORE_DOCUMENT_MOVING';
	public const SECTION_STORE_DOCUMENT_DEDUCT = 'SECTION_STORE_DOCUMENT_DEDUCT';
	public const SECTION_STORE_DOCUMENT_SALES_ORDER = 'SECTION_STORE_DOCUMENT_SALES_ORDER';

	/**
	 * Access rights.
	 *
	 * @return array in format for `BX.UI.AccessRights.Section` js class.
	 */
	public function getAccessRights(): array
	{
		if (State::isUsedInventoryManagement())
		{
			return $this->getAccessRightsWithInventoryManagement();
		}

		return $this->getAccessRightsGeneral();
	}

	/**
	 * All access rights (with inventory management).
	 *
	 * @return array
	 */
	private function getAccessRightsWithInventoryManagement(): array
	{
		$res = [];

		$sections = $this->getSections(true);
		$storeDocumentsMap = $this->getStoreDocumentSectionCodesMap();
		foreach ($sections as $sectionName => $permissions)
		{
			$isStoreSectionName = isset($storeDocumentsMap[$sectionName]);
			$rights = [];
			foreach ($permissions as $permissionId)
			{
				if ($isStoreSectionName)
				{
					[$permissionId, $documentId] = explode('_', $permissionId);
					$rights[] = PermissionDictionary::getStoreDocumentPermission($permissionId, $documentId);
				}
				else
				{
					$rights[] = PermissionDictionary::getPermission($permissionId);
				}
			}

			$res[] = [
				'sectionCode' => $sectionName,
				'sectionTitle' => Loc::getMessage('CATALOG_CONFIG_PERMISSIONS_' . $sectionName) ?? $sectionName,
				'sectionHint' => Loc::getMessage('HINT_CATALOG_CONFIG_PERMISSIONS_' . $sectionName),
				'rights' => $rights
			];
		}

		$res = $this->appendArticleLinks($res);

		return $res;
	}

	/**
	 * Append article links to permissions hints.
	 *
	 * @param array $res
	 *
	 * @return array
	 */
	private function appendArticleLinks(array $res): array
	{
		$articles = new PermissionArticles();

		foreach ($res as $i => $info)
		{
			if (isset($info['sectionHint']))
			{
				$articleLink = $articles->getSectionArticleLink($info['sectionCode']);
				if ($articleLink)
				{
					$res[$i]['sectionHint'] .= " {$articleLink}";
				}
			}

			foreach ($info['rights'] as $z => $right)
			{
				$permissionId = $right['id'];

				if (isset($right['hint']))
				{
					$articleLink = $articles->getPermissionArticleLink($permissionId);
					if ($articleLink)
					{
						$res[$i]['rights'][$z]['hint'] .= " {$articleLink}";
					}
				}
			}
		}

		return $res;
	}

	/**
	 * Only general access rights (without inventory management rights).
	 *
	 * @return array
	 */
	private function getAccessRightsGeneral(): array
	{
		$res = [];

		$sections = $this->getSections(false);
		foreach ($sections as $sectionName => $permissions)
		{
			$rights = [];
			foreach ($permissions as $permissionId)
			{
				$rights[] = PermissionDictionary::getPermission($permissionId);
			}

			$res[] = [
				'sectionTitle' => Loc::getMessage('CATALOG_CONFIG_PERMISSIONS_' . $sectionName) ?? $sectionName,
				'rights' => $rights
			];
		}

		return $res;
	}

	/**
	 * Get saved user roles.
	 *
	 * @return array in format for `BX.UI.AccessRights.Grid.userGroups` js property.
	 */
	public function getUserGroups(): array
	{
		$list = RoleUtil::getRoles();

		$members = $this->getRoleMembersMap();
		$accessRights = $this->getRoleAccessRightsMap();

		$roles = [];
		foreach ($list as $row)
		{
			$roleId = (int) $row['ID'];

			$roles[] = [
				'id' => $roleId,
				'title'  => RoleDictionary::getRoleName($row['NAME']),
				'accessRights' => $accessRights[$roleId] ?? [],
				'members' => $members[$roleId] ?? [],
			];
		}

		return $roles;
	}

	/**
	 * Get sections for view on rights settings page.
	 *
	 * @param bool $withInventoryManagmentSections
	 *
	 * @return array
	 */
	private function getSections(bool $withInventoryManagmentSections): array
	{
		$sections = [
			self::SECTION_CATALOG => $this->getCommonCatalogSection(),
		];

		if ($withInventoryManagmentSections)
		{
			$sections[self::SECTION_INVENTORY_MANAGMENT] = [
				PermissionDictionary::CATALOG_INVENTORY_MANAGEMENT_ACCESS,
				PermissionDictionary::CATALOG_STORE_MODIFY,
				PermissionDictionary::CATALOG_STORE_VIEW,
			];

			if (Loader::includeModule('report'))
			{
				$sections[self::SECTION_INVENTORY_MANAGMENT][] = PermissionDictionary::CATALOG_STORE_ANALYTIC_VIEW;
			}
			$sections[self::SECTION_INVENTORY_MANAGMENT][] = PermissionDictionary::CATALOG_SETTINGS_STORE_DOCUMENT_CARD_EDIT;

			foreach ($this->getStoreDocumentSectionCodesMap() as $code => $typeId)
			{
				$sections[$code] = $this->getStoreDocumentsSectionPermissions($typeId);
			}

			$reservationSection = $this->getReservationSection();
			if ($reservationSection)
			{
				$sections[self::SECTION_RESERVATION] = $reservationSection;
			}
		}

		$sections[self::SECTION_CATALOG_SETTINGS] = $this->getCatalogSettingsSection();
		$sections[self::SECTION_SETTINGS] = [
			PermissionDictionary::CATALOG_SETTINGS_ACCESS,
			PermissionDictionary::CATALOG_SETTINGS_EDIT_RIGHTS,
			PermissionDictionary::CATALOG_SETTINGS_SELL_NEGATIVE_COMMODITIES,
		];

		if ($withInventoryManagmentSections)
		{
			$sections[self::SECTION_RESERVATION][] = PermissionDictionary::CATALOG_RESERVE_SETTINGS;
		}

		return $sections;
	}

	private function getReservationSection(): array
	{
		$result = [];

		if (Loader::includeModule('crm'))
		{
			$result[] = PermissionDictionary::CATALOG_RESERVE_DEAL;
		}

		// TODO: now - not used, maybe in future.
		//$result[] = PermissionDictionary::CATALOG_STORE_RESERVE;

		return $result;
	}

	private function getCommonCatalogSection(): array
	{
		$result = [
			PermissionDictionary::CATALOG_PRODUCT_READ,
			PermissionDictionary::CATALOG_PRODUCT_PURCHASING_PRICE_VIEW,
			PermissionDictionary::CATALOG_PRODUCT_ADD,
			PermissionDictionary::CATALOG_PRODUCT_EDIT,
			PermissionDictionary::CATALOG_PRODUCT_DELETE,
			PermissionDictionary::CATALOG_PRODUCT_EDIT_CATALOG_PRICE,
			PermissionDictionary::CATALOG_PRODUCT_EDIT_ENTITY_PRICE,
			PermissionDictionary::CATALOG_PRODUCT_SET_DISCOUNT,
		];

		if (Loader::includeModule('bitrix24'))
		{
			$result[] = PermissionDictionary::CATALOG_PRODUCT_PUBLIC_VISIBILITY;
		}

		$result[] = PermissionDictionary::CATALOG_IMPORT_EXECUTION;
		$result[] = PermissionDictionary::CATALOG_EXPORT_EXECUTION;

		return $result;
	}

	private function getCatalogSettingsSection(): array
	{
		$result = [
			PermissionDictionary::CATALOG_SETTINGS_PRODUCT_CARD_EDIT,
			PermissionDictionary::CATALOG_SETTINGS_PRODUCT_CARD_SET_PROFILE_FOR_USERS,
			PermissionDictionary::CATALOG_VAT_MODIFY,
			PermissionDictionary::CATALOG_MEASURE_MODIFY,
			PermissionDictionary::CATALOG_PRICE_GROUP_MODIFY,
			PermissionDictionary::CATALOG_PRODUCT_PRICE_EXTRA_EDIT,
		];

		$onlyBox = !ModuleManager::isModuleInstalled('bitrix24');
		if ($onlyBox)
		{
			array_push($result, ...[
				PermissionDictionary::CATALOG_IMPORT_EDIT,
				PermissionDictionary::CATALOG_EXPORT_EDIT,
			]);
		}

		return $result;
	}

	private function getStoreDocumentSectionCodesMap(): array
	{
		return [
			self::SECTION_STORE_DOCUMENT_ARRIVAL => StoreDocumentTable::TYPE_ARRIVAL,
			self::SECTION_STORE_DOCUMENT_STORE_ADJUSTMENT => StoreDocumentTable::TYPE_STORE_ADJUSTMENT,
			self::SECTION_STORE_DOCUMENT_MOVING => StoreDocumentTable::TYPE_MOVING,
			self::SECTION_STORE_DOCUMENT_DEDUCT => StoreDocumentTable::TYPE_DEDUCT,
			self::SECTION_STORE_DOCUMENT_SALES_ORDER => StoreDocumentTable::TYPE_SALES_ORDERS,
		];
	}

	/**
	 * Permissions for document section.
	 *
	 * @param string $typeId
	 *
	 * @return array
	 */
	private function getStoreDocumentsSectionPermissions(string $typeId): array
	{
		$permissions = [
			PermissionDictionary::CATALOG_STORE_DOCUMENT_VIEW,
			PermissionDictionary::CATALOG_STORE_DOCUMENT_MODIFY,
			PermissionDictionary::CATALOG_STORE_DOCUMENT_CONDUCT,
			PermissionDictionary::CATALOG_STORE_DOCUMENT_CANCEL,
			PermissionDictionary::CATALOG_STORE_DOCUMENT_DELETE,
		];

		$typesWithNag = [
			StoreDocumentTable::TYPE_DEDUCT,
			StoreDocumentTable::TYPE_MOVING,
			//StoreDocumentTable::TYPE_SALES_ORDERS,
		];
		if (in_array($typeId, $typesWithNag, true))
		{
			$permissions[] = PermissionDictionary::CATALOG_STORE_DOCUMENT_ALLOW_NEGATION_PRODUCT_QUANTITY;
		}

		$result = [];
		foreach ($permissions as $permission)
		{
			$result[] = "{$permission}_{$typeId}";
		}

		return $result;
	}

	/**
	 * All roles members.
	 *
	 * @return array
	 */
	private function getRoleMembersMap(): array
	{
		return (new RoleMembersInfo)->getMemberInfos();
	}

	/**
	 * All roles access rights.
	 *
	 * @return array in format `[roleId => [ [id => ..., value => ...], [id => ..., value => ...], ... ]]`
	 */
	private function getRoleAccessRightsMap(): array
	{
		$result = [];

		$rows = PermissionTable::getList([
			'select' => [
				'ROLE_ID',
				'PERMISSION_ID',
				'VALUE',
			],
		]);
		foreach ($rows as $row)
		{
			$roleId = $row['ROLE_ID'];

			$result[$roleId][] = [
				'id' => $row['PERMISSION_ID'],
				'value' => $row['VALUE']
			];
		}

		return $result;
	}

	/**
	 * Get permissions only for inventory management.
	 *
	 * @return array
	 */
	public function getInventoryManagementPermissions(): array
	{
		$result = [];

		$sections = $this->getSections(false);
		$sectionsWithInventoryManagment = $this->getSections(true);

		foreach ($sectionsWithInventoryManagment as $code => $permissions)
		{
			$generalPermissions = $sections[$code] ?? null;
			if (!isset($generalPermissions))
			{
				array_push($result, ... $permissions);
				continue;
			}


			foreach ($permissions as $permissionId)
			{
				if (!in_array($permissionId, $generalPermissions, true))
				{
					$result[] = $permissionId;
				}
			}
		}

		return $result;
	}
}
