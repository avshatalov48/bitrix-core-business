<?php

namespace Bitrix\Catalog\Access\Rule;

use Bitrix\Catalog\Access\Model\StoreDocument;
use Bitrix\Catalog\Access\Permission\PermissionDictionary;
use Bitrix\Catalog\Config\Feature;
use Bitrix\Main\Access\AccessibleItem;
use Bitrix\Main\Access\Rule\AbstractRule;

/**
 * Rule for action `ACTION_STORE_DOCUMENT_ALLOW_NEGATION_PRODUCT_QUANTITY`
 */
class StoreDocumentAllowNegationProductQuantityRule extends AbstractRule
{
	/**
	 * @inheritDoc
	 */
	public function execute(AccessibleItem $item = null, $params = null): bool
	{
		if (!Feature::isAccessControllerCheckingEnabled())
		{
			return true;
		}

		if ($item instanceof StoreDocument)
		{
			$typesWithCheck = [
				StoreDocument::TYPE_DEDUCT,
				StoreDocument::TYPE_MOVING,
				StoreDocument::TYPE_SALES_ORDERS,
			];
			if (in_array($item->getType(), $typesWithCheck, true))
			{
				$permission = PermissionDictionary::getStoreDocumentPermissionId(
					PermissionDictionary::CATALOG_STORE_DOCUMENT_ALLOW_NEGATION_PRODUCT_QUANTITY,
					$item->getType()
				);

				return $this->checkPermission($permission);
			}

			// for other types, allows to go into the negative.
			return true;
		}

		return false;
	}

	/**
	 * Check user permission
	 *
	 * @param string $permission
	 *
	 * @return bool
	 */
	private function checkPermission(string $permission): bool
	{
		return $this->user->getPermission($permission) === 1;
	}
}
