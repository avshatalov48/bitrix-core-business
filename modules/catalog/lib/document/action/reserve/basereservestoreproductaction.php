<?php

namespace Bitrix\Catalog\Document\Action\Reserve;

use Bitrix\Catalog\Document\Action\ProductAndStoreInfo;
use Bitrix\Main\Application;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;
use CCatalogProduct;
use CCatalogStoreProduct;

Loc::loadMessages(
	Application::getDocumentRoot() .'/bitrix/modules/catalog/general/store_docs_type.php'
);

/**
 * Base functions for reserve working.
 */
trait BaseReserveStoreProductAction
{
	use ProductAndStoreInfo;

	/**
	 * @var int
	 */
	protected $storeId;

	/**
	 * @var int
	 */
	protected $productId;

	/**
	 * @var float
	 */
	protected $amount;

	/**
	 * @param int $storeId
	 * @param int $productId
	 * @param float $amount
	 */
	public function __construct(int $storeId, int $productId, float $amount)
	{
		$this->storeId = $storeId;
		$this->productId = $productId;
		$this->amount = $amount;
	}

	/**
	 * Product quantity after action.
	 *
	 * @return float
	 */
	abstract protected function getNewProductQuantity(): float;

	/**
	 * Reserved quantity total after action.
	 *
	 * @return float
	 */
	abstract protected function getNewProductReservedQuantity(): float;

	/**
	 * Reserved quantity on store after action.
	 *
	 * @return float
	 */
	abstract protected function getNewStoreReservedQuantity(): float;

	/**
	 * @inheritDoc
	 */
	public function execute(): Result
	{
		$result = new Result();

		// update store info
		$ret = CCatalogStoreProduct::UpdateFromForm([
			'PRODUCT_ID' => $this->productId,
			'STORE_ID' => $this->storeId,
			'AMOUNT' => $this->getStoreProductAmount(),
			'QUANTITY_RESERVED' => $this->getNewStoreReservedQuantity(),
		]);
		if (!$ret)
		{
			$result->addError(
				new Error(Loc::getMessage("CATALOG_STORE_DOCS_ERR_CANT_UPDATE_STORE_PRODUCT"))
			);
			return $result;
		}

		// update product info
		$ret = CCatalogProduct::Update($this->productId, [
			'QUANTITY' => $this->getNewProductQuantity(),
			'QUANTITY_RESERVED' => $this->getNewProductReservedQuantity(),
		]);
		if (!$ret)
		{
			$result->addError(
				new Error(Loc::getMessage("CATALOG_STORE_DOCS_ERR_PURCHASING_INFO_ERROR"))
			);
			return $result;
		}

		return $result;
	}
}