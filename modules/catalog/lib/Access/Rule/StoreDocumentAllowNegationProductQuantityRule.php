<?php

namespace Bitrix\Catalog\Access\Rule;

use Bitrix\Catalog\Access\Model\StoreDocument;
use Bitrix\Catalog\Access\Permission\PermissionDictionary;

/**
 * Rule for action `ACTION_STORE_DOCUMENT_ALLOW_NEGATION_PRODUCT_QUANTITY`
 */
class StoreDocumentAllowNegationProductQuantityRule extends BaseRule
{
	/**
	 * @inheritDoc
	 */
	public function check($params): bool
	{
		$item = $params['item'];

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
