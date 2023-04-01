<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage catalog
 * @copyright 2001-2022 Bitrix
 */

namespace Bitrix\Catalog\Access\Install\Role;

use Bitrix\Catalog\Access\Permission\PermissionDictionary;

class Stockman extends Base
{
	public function getPermissions(): array
	{
		return array_merge(
			[
				PermissionDictionary::CATALOG_INVENTORY_MANAGEMENT_ACCESS ,
				PermissionDictionary::CATALOG_STORE_VIEW,
				PermissionDictionary::CATALOG_STORE_ANALYTIC_VIEW,
				PermissionDictionary::CATALOG_STORE_MODIFY,
				PermissionDictionary::CATALOG_PRODUCT_READ,
				PermissionDictionary::CATALOG_PRODUCT_ADD,
				PermissionDictionary::CATALOG_PRODUCT_PURCHASING_PRICE_VIEW,
			],
			PermissionDictionary::getStoreDocumentPermissionRules(
				[
					PermissionDictionary::CATALOG_STORE_DOCUMENT_VIEW,
					PermissionDictionary::CATALOG_STORE_DOCUMENT_MODIFY,
					PermissionDictionary::CATALOG_STORE_DOCUMENT_CONDUCT,
				]
			)
		);
	}
}