<?php

namespace Bitrix\Catalog\Document\Action\Reserve;

use Bitrix\Catalog\Document\Action;
use Bitrix\Main\Application;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;

Loc::loadMessages(
	Application::getDocumentRoot() .'/bitrix/modules/catalog/general/store_docs_type.php'
);

/**
 * Unreserve product.
 */
class UnReserveStoreProductAction implements Action
{
	use BaseReserveStoreProductAction;

	/**
	 * @inheritDoc
	 */
	public function canExecute(): Result
	{
		$result = new Result();

		$reservedQuantity = $this->getProductTotalReservedQuantity();
		if ($reservedQuantity < $this->amount)
		{
			$message = Loc::getMessage("CATALOG_STORE_DOCS_ERR_WRONG_RESERVED_AMOUNT", [
				'#PRODUCT#' => $this->getProductName(),
			]);
			$result->addError(new Error($message));
			return $result;
		}

		$reservedQuantity = $this->getStoreReservedQuantity();
		if ($reservedQuantity < $this->amount)
		{
			$message = Loc::getMessage("CATALOG_STORE_DOCS_ERR_INSUFFICIENTLY_AMOUNT", [
				'#PRODUCT#' => $this->getProductName(),
				'#STORE#' => $this->getStoreName(),
			]);
			$result->addError(new Error($message));
			return $result;
		}

		return $result;
	}

	/**
	 * @inheritDoc
	 */
	protected function getNewProductQuantity(): float
	{
		return $this->getProductTotalQuantity() + $this->amount;
	}

	/**
	 * @inheritDoc
	 */
	protected function getNewProductReservedQuantity(): float
	{
		return $this->getProductTotalReservedQuantity() - $this->amount;
	}

	/**
	 * @inheritDoc
	 */
	protected function getNewStoreReservedQuantity(): float
	{
		return $this->getStoreReservedQuantity() - $this->amount;
	}
}