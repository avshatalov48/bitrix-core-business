<?php

namespace Bitrix\Catalog\Access\Model;

use Bitrix\Catalog\StoreDocumentElementTable;
use Bitrix\Main\Access\AccessibleItem;

class StoreDocumentElement implements AccessibleItem
{
	private int $id;
	/**
	 * @var int[]
	 */
	private array $storeIds;

	/**
	 * @param int $id
	 * @param array $storeIds
	 */
	public function __construct(int $id, array $storeIds = [])
	{
		$this->id = $id;
		$this->storeIds = array_unique(
			array_map('intval', $storeIds)
		);
	}

	private static function getStoresById(int $id): array
	{
		$storeIds = [];

		if ($id > 0)
		{
			$row = StoreDocumentElementTable::getRow([
				'select' => [
					'STORE_TO',
					'STORE_FROM',
				],
				'filter' => [
					'=ID' => $id,
				],
			]);
			if ($row)
			{
				if (isset($row['STORE_TO']))
				{
					$storeIds[] = $row['STORE_TO'];
				}

				if (isset($row['STORE_FROM']))
				{
					$storeIds[] = $row['STORE_FROM'];
				}
			}
		}

		return $storeIds;
	}

	/**
	 * @inheritDoc
	 *
	 * @param int $itemId
	 * @param array|null $storeIds
	 *
	 * @return StoreDocumentElement
	 */
	public static function createFromId(int $itemId, ?array $storeIds = null): StoreDocumentElement
	{
		return new static(
			$itemId,
			$storeIds ?? self::getStoresById($itemId)
		);
	}

	/**
	 * Create from fields array.
	 *
	 * @param array $fields
	 *
	 * @return StoreDocumentElement
	 */
	public static function createFromArray(array $fields): StoreDocumentElement
	{
		$id = (int)($fields['ID'] ?? 0);
		$storeIds = [];

		if (isset($fields['STORE_TO']))
		{
			$storeIds[] = $fields['STORE_TO'];
		}

		if (isset($fields['STORE_FROM']))
		{
			$storeIds[] = $fields['STORE_FROM'];
		}

		array_push($storeIds, ... self::getStoresById($id));

		return new static($id, $storeIds);
	}

	/**
	 * @inheritDoc
	 */
	public function getId(): int
	{
		return $this->id;
	}

	/**
	 * Store ids used in document element.
	 *
	 * @return int[]
	 */
	public function getStoreIds(): array
	{
		return $this->storeIds;
	}
}
