<?php

namespace Bitrix\Catalog\Access\Filter;

use Bitrix\Catalog\Access\AccessController;
use Bitrix\Catalog\Access\Permission\PermissionDictionary;
use Bitrix\Catalog\StoreDocumentTable;
use Bitrix\Catalog\StoreProductTable;
use Bitrix\Catalog\StoreTable;
use Bitrix\Main\Access\Filter\AbstractAccessFilter;
use Bitrix\Main\Access\Filter\UnknownEntityException;
use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\Loader;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Internals\ShipmentItemStoreTable;
use Bitrix\Sale\Internals\ShipmentTable;

/**
 * Access filter for `Store` entity.
 *
 * @property AccessController $controller
 */
class StoreViewFilter extends AbstractAccessFilter
{
	/**
	 * @inheritDoc
	 */
	public function getFilter(string $entity, array $params = []): array
	{
		$action = (string)($params['action'] ?? '');
		if (empty($action))
		{
			throw new SystemException('No action is set in the parameters');
		}

		$this->validateEntity($entity);

		if ($this->user->isAdmin())
		{
			return [];
		}

		if ($entity === StoreTable::class)
		{
			return $this->getStoreFilter($action);
		}

		if ($entity === StoreDocumentTable::class)
		{
			return $this->getDocumentFilter($action);
		}

		if ($entity === StoreProductTable::class)
		{
			return $this->getProductFilter($action);
		}

		if (Loader::includeModule('sale'))
		{
			if ($entity === ShipmentTable::class)
			{
				return $this->getSaleShipmentFilter($action);
			}
		}

		throw new UnknownEntityException($entity, $this);
	}

	/**
	 * Verifying the correctness of the requested entity.
	 *
	 * @param string $entity
	 *
	 * @return void
	 * @throws UnknownEntityException
	 */
	private function validateEntity(string $entity): void
	{
		$available = [
			StoreTable::class,
			StoreProductTable::class,
			StoreDocumentTable::class,
			// sale
			ShipmentTable::class,
		];

		if (!in_array($entity, $available, true))
		{
			throw new UnknownEntityException($entity, $this);
		}
	}

	/**
	 * Filter for store.
	 *
	 * @param string $action
	 *
	 * @return array
	 */
	private function getStoreFilter(string $action): array
	{
		$allowedStores = $this->controller->getPermissionValue($action);
		if (empty($allowedStores))
		{
			return [
				'=ID' => null,
			];
		}

		if (in_array(PermissionDictionary::VALUE_VARIATION_ALL, $allowedStores, true))
		{
			return [];
		}

		return [
			'=ID' => $allowedStores,
		];
	}

	/**
	 * Filter for store documents.
	 *
	 * @param string $action
	 *
	 * @return array
	 */
	private function getDocumentFilter(string $action): array
	{
		$allowedStores = $this->controller->getPermissionValue($action);
		if (empty($allowedStores))
		{
			$documentFilter = [
				'LOGIC' => 'OR',
				'=ELEMENTS.ID' => null, // without elements
				[
					'=ELEMENTS.STORE_TO' => null,
					'=ELEMENTS.STORE_FROM' => null,
				]
			];
		}
		elseif (in_array(PermissionDictionary::VALUE_VARIATION_ALL, $allowedStores, true))
		{
			return [];
		}
		else
		{
			$documentFilter = [
				'LOGIC' => 'OR',
				'=ELEMENTS.ID' => null, // without elements
				'=ELEMENTS.STORE_TO' => $allowedStores,
				'=ELEMENTS.STORE_FROM' => $allowedStores,
				[
					'=ELEMENTS.STORE_TO' => null,
					'=ELEMENTS.STORE_FROM' => null,
				]
			];
		}

		$query =
			StoreDocumentTable::query()
				->setSelect(['ID'])
				->setFilter($documentFilter)
				->getQuery()
		;

		return [
			'@ID' => new SqlExpression($query),
		];
	}

	/**
	 * Filter for store products.
	 *
	 * @param string $action
	 *
	 * @return array
	 */
	private function getProductFilter(string $action): array
	{
		$allowedStores = $this->controller->getPermissionValue($action);
		if (empty($allowedStores))
		{
			return [
				'=ID' => null,
			];
		}

		if (in_array(PermissionDictionary::VALUE_VARIATION_ALL, $allowedStores, true))
		{
			return [];
		}

		return [
			'=STORE_ID' => $allowedStores,
		];
	}

	/**
	 * Filter for sale shipments (aka 'catalog realizations')
	 *
	 * @param string $action
	 *
	 * @return array
	 */
	private function getSaleShipmentFilter(string $action): array
	{
		$allowedStores = $this->controller->getPermissionValue($action);
		if (empty($allowedStores))
		{
			return [
				'=ID' => null,
			];
		}

		if (in_array(PermissionDictionary::VALUE_VARIATION_ALL, $allowedStores, true))
		{
			return [];
		}

		$allowedStores[] = null;
		$storeFilter = [
			'=STORE_ID' => $allowedStores,
		];

		$query =
			ShipmentItemStoreTable::query()
				->setSelect(['ORDER_DELIVERY_BASKET_ID'])
				->setFilter($storeFilter)
				->getQuery()
		;

		return [
			[
				'LOGIC' => 'OR',
				'=SHIPMENT_ITEM.ID' => null,
				'@SHIPMENT_ITEM.ID' => new SqlExpression($query),
			]
		];
	}
}
