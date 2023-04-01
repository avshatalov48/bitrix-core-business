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

class Director extends Base
{
	public function getPermissions(): array
	{
		return array_merge(
			[
				PermissionDictionary::CATALOG_INVENTORY_MANAGEMENT_ACCESS,
				PermissionDictionary::CATALOG_STORE_VIEW,
				PermissionDictionary::CATALOG_STORE_MODIFY,
				PermissionDictionary::CATALOG_STORE_ANALYTIC_VIEW,
				PermissionDictionary::CATALOG_RESERVE_DEAL,
				PermissionDictionary::CATALOG_STORE_RESERVE,
				PermissionDictionary::CATALOG_RESERVE_SETTINGS,
				PermissionDictionary::CATALOG_SETTINGS_ACCESS,
				PermissionDictionary::CATALOG_SETTINGS_SELL_NEGATIVE_COMMODITIES,
				PermissionDictionary::CATALOG_SETTINGS_STORE_DOCUMENT_CARD_EDIT,
				PermissionDictionary::CATALOG_SETTINGS_PRODUCT_CARD_EDIT,
				PermissionDictionary::CATALOG_SETTINGS_PRODUCT_CARD_SET_PROFILE_FOR_USERS,
				PermissionDictionary::CATALOG_PRODUCT_READ,
				PermissionDictionary::CATALOG_PRODUCT_ADD,
				PermissionDictionary::CATALOG_PRODUCT_EDIT,
				PermissionDictionary::CATALOG_PRODUCT_PURCHASING_PRICE_VIEW,
				PermissionDictionary::CATALOG_PRODUCT_EDIT_CATALOG_PRICE,
				PermissionDictionary::CATALOG_PRODUCT_EDIT_ENTITY_PRICE,
				PermissionDictionary::CATALOG_PRODUCT_SET_DISCOUNT,
				PermissionDictionary::CATALOG_PRODUCT_PUBLIC_VISIBILITY,
				PermissionDictionary::CATALOG_EXPORT_EXECUTION,
				PermissionDictionary::CATALOG_IMPORT_EXECUTION,
				PermissionDictionary::CATALOG_VAT_MODIFY,
				PermissionDictionary::CATALOG_MEASURE_MODIFY,
				PermissionDictionary::CATALOG_PRICE_GROUP_MODIFY,
				PermissionDictionary::CATALOG_PRODUCT_PRICE_EXTRA_EDIT,
				PermissionDictionary::CATALOG_IMPORT_EDIT,
				PermissionDictionary::CATALOG_EXPORT_EDIT,
			],
			PermissionDictionary::getStoreDocumentPermissionRules(
				[
					PermissionDictionary::CATALOG_STORE_DOCUMENT_VIEW,
					PermissionDictionary::CATALOG_STORE_DOCUMENT_MODIFY,
					PermissionDictionary::CATALOG_STORE_DOCUMENT_CANCEL,
					PermissionDictionary::CATALOG_STORE_DOCUMENT_CONDUCT
				]
			),
			PermissionDictionary::getStoreDocumentPermissionRules(
				[
					PermissionDictionary::CATALOG_STORE_DOCUMENT_ALLOW_NEGATION_PRODUCT_QUANTITY,
				],
				[
					StoreDocumentTable::TYPE_SALES_ORDERS,
					StoreDocumentTable::TYPE_MOVING,
					StoreDocumentTable::TYPE_DEDUCT,
				]
			),
			PermissionDictionary::getStoreDocumentPermissionRules(
				[
					PermissionDictionary::CATALOG_STORE_DOCUMENT_DELETE,
				],
				[
					StoreDocumentTable::TYPE_SALES_ORDERS,
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