<?php

namespace Bitrix\Catalog\Document\Action\Store;

use Bitrix\Catalog\Document\Action\ProductAndStoreInfo;
use Bitrix\Catalog\StoreProductTable;
use Bitrix\Main\Application;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\Result;
use CCatalogProduct;
use CCatalogStoreProduct;

Loc::loadMessages(
	Application::getDocumentRoot() .'/bitrix/modules/catalog/general/store_docs_type.php'
);

/**
 * Base action for increase and decrease actions.
 */
trait BaseStoreQuantityAction
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
	 * Update product quantity by real quantity from store.
	 *
	 * @return bool
	 */
	protected function updateProductQuantity(): bool
	{
		$row = StoreProductTable::getRow([
			'select' => [
				'SUM_AMOUNT',
			],
			'filter' => [
				'=STORE.ACTIVE' => 'Y',
				'=PRODUCT_ID' => $this->productId,
			],
			'runtime' => [
				new ExpressionField('SUM_AMOUNT', 'SUM(%s)', 'AMOUNT'),
			],
			'group' => [
				'PRODUCT_ID',
			],
		]);
		if (!$row)
		{
			return false;
		}

		return CCatalogProduct::Update($this->productId, [
			'QUANTITY' => (float)$row['SUM_AMOUNT'] - $this->getProductTotalReservedQuantity()
		]);
	}

	/**
	 * The amount of the product that should become after execute action.
	 *
	 * @return float
	 */
	abstract protected function getProductAmountNew(): float;

	/**
	 * @inheritDoc
	 */
	public function execute(): Result
	{
		$result = new Result();

		$ret = CCatalogStoreProduct::UpdateFromForm([
			'STORE_ID' => $this->storeId,
			'PRODUCT_ID' => $this->productId,
			'AMOUNT' => $this->getProductAmountNew(),
		]);
		if (!$ret)
		{
			$result->addError(
				new Error(Loc::getMessage('CATALOG_STORE_DOCS_ERR_CANT_UPDATE_STORE_PRODUCT'))
			);
		}

		if (!$this->updateProductQuantity())
		{
			$result->addError(
				new Error(Loc::getMessage("CATALOG_STORE_DOCS_ERR_PURCHASING_INFO_ERROR"))
			);
		}

		return $result;
	}
}