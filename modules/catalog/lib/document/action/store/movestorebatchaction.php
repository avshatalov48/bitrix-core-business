<?php
namespace Bitrix\Catalog\Document\Action\Store;

use Bitrix\Catalog\EO_StoreDocumentElement;
use Bitrix\Catalog\Product\Store\BatchManager;
use Bitrix\Catalog\Product\Store\CostPriceCalculator;
use Bitrix\Catalog\Product\Store\DistributionStrategy\DeductDocument;
use Bitrix\Catalog\StoreBatchDocumentElementTable;
use Bitrix\Catalog\StoreDocumentElementTable;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Result;

/**
 * Move products from one store to another.
 */
class MoveStoreBatchAction extends UpsertStoreBatchAction
{
	use WriteOffAmountValidator;
	private int $storeFromId;

	private int $storeToId;
	private int $storeId;

	private ?EO_StoreDocumentElement $storeDocumentElement;
	public function __construct(
		int $storeFromId,
		int $storeToId,
		int $productId,
		float $amount,
		int $documentElementId = null,
	)
	{
		$this->storeFromId = $storeFromId;
		$this->storeToId = $storeToId;

		parent::__construct($storeToId, $productId, $amount, $documentElementId);

		if ($documentElementId)
		{
			$this->storeDocumentElement = StoreDocumentElementTable::getList([
				'filter' => [
					'=ID' => $documentElementId,
				],
				'limit' => 1
			])
				->fetchObject()
			;
		}
	}

	public function canExecute(): Result
	{
		$this->productId = $this->storeDocumentElement->getElementId();
		$this->storeId = $this->storeDocumentElement->getStoreFrom();

		return $this->checkStoreAmount($this->storeDocumentElement);
	}

	/**
	 * @inheritDoc
	 */
	public function execute(): Result
	{
		$result = new Result();

		$distributor = new DeductDocument(
			new BatchManager($this->productId),
			$this->storeDocumentElement
		);

		$writeOffResult = $distributor->writeOff($this->amount);

		if (!$writeOffResult->isSuccess())
		{
			return $writeOffResult;
		}

		$writeOffBindings = StoreBatchDocumentElementTable::getList([
			'filter' => [
				'=DOCUMENT_ELEMENT_ID' => $this->storeDocumentElement->getId(),
				'<AMOUNT' => 0,
			],
		]);

		while ($writeOffBinding = $writeOffBindings->fetchObject())
		{
			$batch = null;
			$moveAmount = abs($writeOffBinding->getAmount());
			if (CostPriceCalculator::getMethod() === CostPriceCalculator::METHOD_AVERAGE)
			{
				$batch = $this->loadBatch($this->storeToId);

				if ($batch !== null)
				{
					$this->recalculateBatch(
						$batch,
						$moveAmount,
						$writeOffBinding->getBatchPrice(),
						$writeOffBinding->getBatchCurrency(),
					);
					$resultUpdate = $batch->save();
					if (!$resultUpdate->isSuccess())
					{
						$result->addError(
							new Error(Loc::getMessage('CATALOG_STORE_DOCS_ERR_CANT_MOVE_STORE_PRODUCT'))
						);

						return $result;
					}
				}
			}

			if ($batch === null)
			{
				$batch = $this->createBatch(
					$this->storeToId,
					$moveAmount,
					$writeOffBinding->getBatchPrice(),
					$writeOffBinding->getBatchCurrency(),
				);
			}

			if ($batch === null)
			{
				$result->addError(
					new Error(Loc::getMessage('CATALOG_STORE_DOCS_ERR_CANT_MOVE_STORE_PRODUCT'))
				);

				return $result;
			}

			$this->addDocumentElementBatchBinding(
				$batch,
				$moveAmount,
				$writeOffBinding->getBatchPrice(),
				$writeOffBinding->getBatchCurrency(),
			);
		}

		return $result;
	}
}
