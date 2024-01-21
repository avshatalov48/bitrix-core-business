<?php

namespace Bitrix\Catalog\Document\Action\Store;

use Bitrix\Catalog\Document\Action\ProductAndStoreInfo;
use Bitrix\Catalog\EO_StoreDocumentElement;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;

/**
 * Base action for increase and decrease actions.
 */
trait WriteOffAmountValidator
{
	use ProductAndStoreInfo;

	protected function checkStoreAmount(EO_StoreDocumentElement $storeDocumentElement = null): Result
	{
		$result = new Result();

		if (empty($storeDocumentElement))
		{
			$result->addError(new Error(Loc::getMessage("CATALOG_STORE_EMPTY_DOC_ELEMENT")));

			return $result;
		}

		if ($this->getStoreProductAmount() - $storeDocumentElement->getAmount() < 0)
		{
			$message = Loc::getMessage(
				"CATALOG_STORE_DOCS_ERR_INSUFFICIENTLY_AMOUNT_EXT",
				[
					"#STORE#" => $this->getStoreName(),
					"#PRODUCT#" => $this->getProductName(),
				]
			);

			$result->addError(new Error($message));
		}

		return $result;
	}
}