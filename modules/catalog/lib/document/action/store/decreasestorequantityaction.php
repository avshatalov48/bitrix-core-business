<?php

namespace Bitrix\Catalog\Document\Action\Store;

use Bitrix\Catalog\Document\Action;
use Bitrix\Catalog\ProductTable;
use Bitrix\Main\Application;
use Bitrix\Main\Result;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(
	Application::getDocumentRoot() .'/bitrix/modules/catalog/general/store_docs_type.php'
);

/**
 * Decrease store availability quantity of product.
 */
class DecreaseStoreQuantityAction  implements Action
{
	use BaseStoreQuantityAction;

	/**
	 * @inheritDoc
	 */
	public function canExecute(): Result
	{
		$result = new Result();

		$amount = $this->getProductAmountNew();
		if ($amount < 0)
		{
			$product = ProductTable::getRowById($this->productId);
			if (!$product || $product['NEGATIVE_AMOUNT_TRACE'] !== 'Y')
			{
				$message = Loc::getMessage("CATALOG_STORE_DOCS_ERR_INSUFFICIENTLY_AMOUNT_EXT", [
					"#STORE#" => $this->getStoreName(),
					"#PRODUCT#" => $this->getProductName(),
				]);
				$result->addError(new Error($message));
			}
		}

		return $result;
	}

	/**
	 * @inheritDoc
	 */
	protected function getProductAmountNew(): float
	{
		return $this->getStoreProductAmount() - $this->amount;
	}
}