<?php

namespace Bitrix\Catalog\Access\Rule;

use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Catalog\Access\Model\StoreDocument;
use Bitrix\Catalog\Access\Permission\PermissionDictionary;

class StoreDocumentPerformRule extends BaseRule
{
	/**
	 * @param array $params
	 *
	 * @return string | null
	 */
	protected static function getPermissionCode(array $params): ?string
	{
		$docCode = $params['value'] ?? null;
		$docItem = $params['item'] ?? null;

		if ($docItem instanceof StoreDocument)
		{
			$docCode = $docItem->getType();
		}

		if (!$docCode || !in_array($docCode, PermissionDictionary::getAvailableStoreDocuments(), true))
		{
			return null;
		}

		$permissionId = (string)ActionDictionary::getStoreDocumentActionPermissionMap()[$params['action']];
		if (!$permissionId)
		{
			return null;
		}

		return PermissionDictionary::getStoreDocumentPermissionId($permissionId, $docCode);
	}
}
