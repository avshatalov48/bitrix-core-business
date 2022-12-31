<?php

namespace Bitrix\Catalog\Access\Filter;

use Bitrix\Catalog\Access\Permission\PermissionDictionary;
use Bitrix\Catalog\StoreDocumentElementTable;
use Bitrix\Catalog\StoreDocumentTable;
use Bitrix\Main\Access\Filter\AbstractAccessFilter;
use Bitrix\Main\Access\Filter\UnknownEntityException;
use Bitrix\Main\Loader;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Internals\ShipmentItemTable;
use Bitrix\Sale\Internals\ShipmentTable;

/**
 * Access filter for `StoreDocument` entity.
 *
 * @see \Bitrix\Catalog\Access\Model\StoreDocument
 */
class StoreDocumentFilter extends AbstractAccessFilter
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

		Loader::includeModule('sale');

		$this->validateEntity($entity);

		if ($this->user->isAdmin())
		{
			return [];
		}

		if ($entity === StoreDocumentTable::class)
		{
			return $this->getDocumentFilter($action);
		}

		if ($entity === StoreDocumentElementTable::class)
		{
			return $this->getElementFilter($action);
		}

		if ($entity === ShipmentTable::class)
		{
			return $this->getShipmentFilter($action);
		}

		if ($entity === ShipmentItemTable::class)
		{
			return $this->getShipmentItemFilter($action);
		}
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
			StoreDocumentTable::class,
			StoreDocumentElementTable::class,
			ShipmentTable::class,
			ShipmentItemTable::class,
		];

		if (!in_array($entity, $available, true))
		{
			throw new UnknownEntityException($entity, $this);
		}
	}

	/**
	 * Filter for documents.
	 *
	 * @param string $action
	 *
	 * @return array|null
	 */
	private function getDocumentFilter(string $action): ?array
	{
		$types = array_filter(
			PermissionDictionary::getAvailableStoreDocuments(),
			fn(string $docType) => $this->controller->check($action, null, ['value' => $docType])
		);

		if (empty($types))
		{
			return [
				'=ID' => null,
			];
		}

		return [
			'=DOC_TYPE' => $types,
		];
	}

	/**
	 * Filter for document elements.
	 *
	 * @param string $action
	 *
	 * @return array|null
	 */
	private function getElementFilter(string $action): ?array
	{
		$types = array_filter(
			PermissionDictionary::getAvailableStoreDocuments(),
			fn(string $docType) => $this->controller->check($action, null, ['value' => $docType])
		);

		if (empty($types))
		{
			return [
				'=ID' => null,
			];
		}

		return [
			'=DOCUMENT.DOC_TYPE' => $types,
		];
	}

	/**
	 * Filter for shipment.
	 *
	 * @param string $action
	 *
	 * @return array|null
	 */
	private function getShipmentFilter(string $action): ?array
	{
		$can = $this->controller->check($action, null, ['value' => StoreDocumentTable::TYPE_SALES_ORDERS]);
		if (!$can)
		{
			return [
				'=ID' => null,
			];
		}

		return [];
	}

	/**
	 * Filter for shipment items.
	 *
	 * @param string $action
	 *
	 * @return array|null
	 */
	private function getShipmentItemFilter(string $action): ?array
	{
		// identical checks
		return $this->getShipmentFilter($action);
	}
}
