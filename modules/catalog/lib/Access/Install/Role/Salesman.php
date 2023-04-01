<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage catalog
 * @copyright 2001-2022 Bitrix
 */

namespace Bitrix\Catalog\Access\Install\Role;

use Bitrix\Catalog\Access\Role\RoleDictionary;
use Bitrix\Catalog\Access\Permission\PermissionDictionary;
use Bitrix\Catalog\StoreDocumentTable;

class Salesman extends Base
{
	public function getPermissions(): array
	{
		return array_merge(
			[
				PermissionDictionary::CATALOG_INVENTORY_MANAGEMENT_ACCESS,
				PermissionDictionary::CATALOG_STORE_VIEW,
				PermissionDictionary::CATALOG_STORE_ANALYTIC_VIEW,
				PermissionDictionary::CATALOG_RESERVE_DEAL,
				PermissionDictionary::CATALOG_STORE_RESERVE,
				PermissionDictionary::CATALOG_PRODUCT_VIEW,
				PermissionDictionary::CATALOG_PRODUCT_READ,
				PermissionDictionary::CATALOG_PRODUCT_ADD,
				PermissionDictionary::CATALOG_PRODUCT_EDIT_ENTITY_PRICE,
				PermissionDictionary::CATALOG_PRODUCT_SET_DISCOUNT,
				PermissionDictionary::CATALOG_PRODUCT_PUBLIC_VISIBILITY,
			],
			PermissionDictionary::getStoreDocumentPermissionRules(
				[
					PermissionDictionary::CATALOG_STORE_DOCUMENT_VIEW,
					PermissionDictionary::CATALOG_STORE_DOCUMENT_MODIFY,
				]
			),
			PermissionDictionary::getStoreDocumentPermissionRules(
				[
					PermissionDictionary::CATALOG_STORE_DOCUMENT_CONDUCT,
				],
				[
					StoreDocumentTable::TYPE_DEDUCT,
				]
			)
		);
	}

	protected function getPermissionValue($permissionId): array
	{
		if (
			$permissionId === PermissionDictionary::CATALOG_PRODUCT_EDIT_ENTITY_PRICE
			|| $permissionId === PermissionDictionary::CATALOG_PRODUCT_SET_DISCOUNT
		)
		{
			return [\CCrmOwnerType::Deal, \CCrmOwnerType::Lead];
		}

		return [PermissionDictionary::getDefaultPermissionValue($permissionId)];
	}
}