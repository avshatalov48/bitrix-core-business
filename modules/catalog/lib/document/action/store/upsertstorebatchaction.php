<?php

namespace Bitrix\Catalog\Document\Action\Store;

use Bitrix\Catalog\Document\Action;
use Bitrix\Catalog\Document\Action\ProductAndStoreInfo;
use Bitrix\Catalog\EO_StoreBatch;
use Bitrix\Catalog\Product\Store\CostPriceCalculator;
use Bitrix\Catalog\StoreBatchDocumentElementTable;
use Bitrix\Catalog\StoreBatchTable;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;

/**
 * Update price and quantity for inventory method by average or create new batch of products.
 */
class UpsertStoreBatchAction implements Action
{
	use ProductAndStoreInfo;

	private int $storeId;
	protected int $productId;
	protected float $amount;
	protected ?int $documentElementId;
	protected ?float $purchasingPrice;
	protected ?string $purchasingCurrency;
	public function __construct(
		int $storeId,
		int $productId,
		float $amount,
		int $documentElementId = null,
		float $purchasingPrice = null,
		string $purchasingCurrency = null
	)
	{
		$this->storeId = $storeId;
		$this->productId = $productId;
		$this->amount = $amount;
		$this->documentElementId = $documentElementId;
		$this->purchasingPrice = $purchasingPrice;
		$this->purchasingCurrency = $purchasingCurrency;
	}

	public function canExecute(): Result
	{
		return new Result();
	}

	/**
	 * @inheritDoc
	 */
	public function execute(): Result
	{
		$result = new Result();

		$batch = null;
		if (CostPriceCalculator::getMethod() === CostPriceCalculator::METHOD_AVERAGE)
		{
			$batch = $this->loadBatch($this->storeId);

			if ($batch !== null)
			{
				$this->recalculateBatch(
					$batch,
					$this->amount,
					(float)$this->purchasingPrice,
					$this->purchasingCurrency,
				);
				$resultUpdate = $batch->save();
				if (!$resultUpdate->isSuccess())
				{
					$result->addError(
						new Error(Loc::getMessage('CATALOG_STORE_DOCS_ERR_CANT_UPDATE_STORE_PRODUCT'))
					);

					return $result;
				}
			}
		}

		if ($batch === null)
		{
			$batch = $this->createBatch(
				$this->storeId,
				$this->amount,
				$this->purchasingPrice,
				$this->purchasingCurrency,
			);
		}

		if ($batch === null)
		{
			$result->addError(
				new Error(Loc::getMessage('CATALOG_STORE_DOCS_ERR_CANT_UPDATE_STORE_PRODUCT'))
			);

			return $result;
		}

		$this->addDocumentElementBatchBinding(
			$batch,
			$this->amount,
			$this->purchasingPrice,
			$this->purchasingCurrency,
		);

		return $result;
	}

	protected function getDocumentElementId(): int
	{
		return $this->documentElementId;
	}

	protected function loadBatch(int $storeId): ?EO_StoreBatch
	{
		return StoreBatchTable::getList([
				'filter' => [
					'STORE_ID' => $storeId,
					'ELEMENT_ID' => $this->getProductId(),
				],
				'limit' => 1,
			])
			->fetchObject()
		;
	}

	protected function createBatch(
		int $storeId,
		float $amount,
		float $purchasingPrice = null,
		string $purchasingCurrency = null,
	): ?EO_StoreBatch
	{
		$resultAdd = StoreBatchTable::add([
			'STORE_ID' => $storeId,
			'ELEMENT_ID' => $this->getProductId(),
			'AVAILABLE_AMOUNT' => $amount,
			'PURCHASING_PRICE' => $purchasingPrice,
			'PURCHASING_CURRENCY' => $purchasingCurrency,
		]);

		if (!$resultAdd->isSuccess())
		{
			return null;
		}
		/** @var EO_StoreBatch $batch */
		$batch = $resultAdd->getObject();

		return $batch;
	}

	protected function addDocumentElementBatchBinding(
		EO_StoreBatch $batch,
		float $amount,
		float $purchasingPrice = null,
		string $purchasingCurrency = null,
	): void
	{
		StoreBatchDocumentElementTable::add([
			'DOCUMENT_ELEMENT_ID' => $this->getDocumentElementId(),
			'AMOUNT' => $amount,
			'PRODUCT_BATCH_ID' => $batch->getId(),
			'BATCH_PRICE' => $purchasingPrice,
			'BATCH_CURRENCY' => $purchasingCurrency,
		]);
	}

	protected function recalculateBatch(
		EO_StoreBatch $batch,
		float $amount,
		float $purchasingPrice,
		string $purchasingCurrency = null,
	): void
	{
		if ($purchasingCurrency && $purchasingCurrency !== $batch->getPurchasingCurrency())
		{
			$purchasingPrice = $this->convertPrice($purchasingPrice, $purchasingCurrency, $batch->getPurchasingCurrency());
		}

		$precision = (int)Option::get('sale', 'value_precision', 2);
		$newAvailableAmount = $batch->getAvailableAmount() + $amount;
		$newPurchasingPrice = ($batch->getPurchasingPrice() * $batch->getAvailableAmount() + $purchasingPrice * $amount) / $newAvailableAmount;
		$newPurchasingPrice = round($newPurchasingPrice, $precision);

		$batch->setAvailableAmount($newAvailableAmount);
		$batch->setPurchasingPrice($newPurchasingPrice);
	}

	private function convertPrice(
		string $purchasingPrice,
		string $purchasingCurrency,
		string $newCurrency
	): float
	{
		if (!Loader::includeModule('currency'))
		{
			return $purchasingPrice;
		}

		return \CCurrencyRates::convertCurrency(
			$purchasingPrice,
			$purchasingCurrency,
			$newCurrency
		);
	}
}
