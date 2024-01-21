<?php
namespace Bitrix\Catalog\Product\Store\BatchBalancer\Method;

use Bitrix\Catalog\EO_StoreBatchDocumentElement;
use Bitrix\Catalog\Product\Store\BatchBalancer\Balancer;
use Bitrix\Catalog\Product\Store\BatchBalancer\ElementBatchTree;
use Bitrix\Catalog\Product\Store\BatchBalancer\InventoryTree;
use Bitrix\Catalog\Product\Store\BatchBalancer\Entity;
use Bitrix\Catalog\StoreBatchDocumentElementTable;
use Bitrix\Catalog\StoreDocumentElementTable;
use Bitrix\Catalog\StoreDocumentTable;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\Loader;
use Bitrix\Main\Result;
use Bitrix\Sale\Internals\ShipmentItemStoreTable;

/**
 * Class Base
 *
 * @package Bitrix\Catalog\Product\Store\BatchBalancer
 */
abstract class Base
{
	protected Balancer $balancer;
	protected ?InventoryTree $elementBatchTree;

	protected array $storeConditions = [];

	public function __construct(Balancer $balancer)
	{
		$this->balancer = $balancer;

		$this->elementBatchTree = $this->buildInventoryTree();
	}

	abstract public function fill(): Result;

	protected function buildInventoryTree(): ?InventoryTree
	{
		$registryUnchanged = $this->loadConductedDocumentElements();

		if (empty($registryUnchanged))
		{
			return null;
		}

		$elementBatchTree = new ElementBatchTree();

		foreach ($registryUnchanged as $item)
		{
			if (!empty($item['STORE_TO']))
			{
				$batchElement = new EO_StoreBatchDocumentElement();
				$batchElement->setAmount($item['AMOUNT']);
				$batchElement->setShipmentItemStoreId($item['STORE_TO']);
				$batchElement->setDocumentElementId($item['ID']);
				$batchElement->setBatchPrice($item['PURCHASING_PRICE']);
				$batchElement->setBatchCurrency($item['CURRENCY']);

				$elementBatchTree->append(new Entity\ElementBatchItem($batchElement, $item['STORE_TO']));
			}

			if (!empty($item['STORE_FROM']))
			{
				$batchElement = new EO_StoreBatchDocumentElement();
				$batchElement->setAmount(-$item['AMOUNT']);
				$batchElement->setShipmentItemStoreId($item['STORE_TO']);
				if ($item['DOCUMENT_TYPE'] === StoreDocumentTable::TYPE_SALES_ORDERS)
				{
					$batchElement->setShipmentItemStoreId($item['ID']);
				}
				else
				{
					$batchElement->setDocumentElementId($item['ID']);
				}

				$elementBatchTree->append(new Entity\ElementBatchItem($batchElement, $item['STORE_FROM']));
			}
		}

		return $elementBatchTree;
	}

	private function loadConductedDocumentElements(): array
	{
		$filterDocs = [
			'ELEMENT_ID' => $this->balancer->getProductId(),
			'=DOCUMENT.STATUS' => 'Y',
		];

		if ($this->balancer->getStartDate() !== null)
		{
			$filterDocs['>=STATUS_DATE'] = $this->balancer->getStartDate();
		}

		$registry = StoreDocumentElementTable::getList([
				'filter' => $filterDocs,
				'select' => [
					'ID',
					'STORE_FROM',
					'STORE_TO',
					'PURCHASING_PRICE',
					'AMOUNT',
					'DOCUMENT_TYPE' => 'DOCUMENT.DOC_TYPE',
					'CURRENCY' => 'DOCUMENT.CURRENCY',
					'DATE' => 'DOCUMENT.DATE_STATUS',
					'BATCH_ID' => 'BATCH_BINDING.PRODUCT_BATCH_ID',
				],
				'runtime' => [
					new ReferenceField(
						'BATCH_BINDING',
						StoreBatchDocumentElementTable::getEntity(),
						array('=this.ID' => 'ref.DOCUMENT_ELEMENT_ID'),
						array('join_type' => 'left'),
					),
				],
				'order' => ['DATE' => 'ASC'],
			])
			->fetchAll()
		;

		if (Loader::includeModule('sale'))
		{
			$filterSales = [
				'=ORDER_DELIVERY_BASKET.BASKET.PRODUCT_ID' => $this->balancer->getProductId(),
				'=ORDER_DELIVERY_BASKET.DELIVERY.DEDUCTED' => 'Y',
			];

			if ($this->balancer->getStartDate() !== null)
			{
				$filterSales['>=ORDER_DELIVERY_BASKET.DELIVERY.DATE_DEDUCTED'] = $this->balancer->getStartDate();
			}

			$salesElements = ShipmentItemStoreTable::getList([
				'filter' => $filterSales,
				'select' => [
					'ID',
					'STORE_FROM' => 'STORE_ID',
					'AMOUNT' => 'QUANTITY',
					'DATE' => 'ORDER_DELIVERY_BASKET.DELIVERY.DATE_DEDUCTED',
					'BATCH_ID' => 'BATCH_BINDING.PRODUCT_BATCH_ID',
				],
				'runtime' => [
					new ReferenceField(
						'BATCH_BINDING',
						StoreBatchDocumentElementTable::getEntity(),
						array('=this.ID' => 'ref.SHIPMENT_ITEM_STORE_ID'),
						array('join_type' => 'left'),
					),
				],
				'order' => ['DATE' => 'ASC'],
			]);

			while ($element = $salesElements->fetch())
			{
				$element['DOCUMENT_TYPE'] = StoreDocumentTable::TYPE_SALES_ORDERS;
				$registry[] = $element;
			}
		}

		\Bitrix\Main\Type\Collection::sortByColumn($registry, ['DATE' => SORT_ASC]);

		return $registry;
	}
}