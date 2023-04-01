<?php

namespace Bitrix\Catalog\Document\Action\Store;

use Bitrix\Catalog\Access\AccessController;
use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Catalog\Access\Model\StoreDocument;
use Bitrix\Catalog\Config\Options\CheckRightsOnDecreaseStoreAmount;
use Bitrix\Catalog\Document\Action;
use Bitrix\Catalog\ProductTable;
use Bitrix\Catalog\StoreProductTable;
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

	private string $docType;

	/**
	 * @param int $storeId
	 * @param int $productId
	 * @param float $amount
	 * @param string $docType
	 */
	public function __construct(int $storeId, int $productId, float $amount, string $docType)
	{
		$this->storeId = $storeId;
		$this->productId = $productId;
		$this->amount = $amount;
		$this->docType = $docType;
	}

	/**
	 * @inheritDoc
	 */
	public function canExecute(): Result
	{
		$result = new Result();

		$amount = $this->getProductAmountNew();
		if ($amount < 0)
		{
			$can = false;

			$product = ProductTable::getRowById($this->productId);
			if (!$product || CheckRightsOnDecreaseStoreAmount::isDisabled())
			{
				$can = false;
			}
			elseif (CheckRightsOnDecreaseStoreAmount::isEnabled())
			{
				$can = AccessController::getCurrent()->check(
					ActionDictionary::ACTION_STORE_DOCUMENT_ALLOW_NEGATION_PRODUCT_QUANTITY,
					StoreDocument::createFromArray([
						'DOC_TYPE' => $this->docType,
					])
				);
			}
			elseif (CheckRightsOnDecreaseStoreAmount::isNotUsed())
			{
				$can = $product['NEGATIVE_AMOUNT_TRACE'] === 'Y';
			}

			if (!$can)
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

	/**
	 * @inheritDoc
	 */
	protected function getStoreProductRow(): array
	{
		$row = null;

		$storeId = $this->getStoreId();
		$productId = $this->getProductId();
		if (isset($storeId, $productId))
		{
			// load without cache to maintain the actual state.
			$row = StoreProductTable::getRow([
				'select' => [
					'AMOUNT',
					'QUANTITY_RESERVED',
				],
				'filter' => [
					'=PRODUCT_ID' => $productId,
					'=STORE_ID' => $storeId,
				],
			]);
		}

		return $row ?? [];
	}
}
