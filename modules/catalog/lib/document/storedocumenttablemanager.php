<?php

namespace Bitrix\Catalog\Document;

use Bitrix\Catalog\Document\Type\StoreDocumentArrivalTable;
use Bitrix\Catalog\Document\Type\StoreDocumentDeductTable;
use Bitrix\Catalog\Document\Type\StoreDocumentMovingTable;
use Bitrix\Catalog\Document\Type\StoreDocumentReturnTable;
use Bitrix\Catalog\Document\Type\StoreDocumentStoreAdjustmentTable;
use Bitrix\Catalog\Document\Type\StoreDocumentUndoReserveTable;
use Bitrix\Catalog\StoreDocumentTable;

class StoreDocumentTableManager
{
	private static $typeClassMap = [
		StoreDocumentTable::TYPE_ARRIVAL => StoreDocumentArrivalTable::class,
		StoreDocumentTable::TYPE_STORE_ADJUSTMENT => StoreDocumentStoreAdjustmentTable::class,
		StoreDocumentTable::TYPE_MOVING => StoreDocumentMovingTable::class,
		StoreDocumentTable::TYPE_DEDUCT => StoreDocumentDeductTable::class,
		StoreDocumentTable::TYPE_RETURN => StoreDocumentReturnTable::class,
		StoreDocumentTable::TYPE_UNDO_RESERVE => StoreDocumentUndoReserveTable::class,
	];

	/**
	 * @param string $docType
	 * @return string
	 */
	public static function getTableClassByType(string $docType): string
	{
		return self::$typeClassMap[$docType] ?? '';
	}

	/**
	 * @return array
	 */
	public static function getUfEntityIds(): array
	{
		return array_map(static fn($tableClass): string => $tableClass::getUfId(), self::$typeClassMap);
	}

	/**
	 * @param string $entityId
	 * @return string
	 */
	public static function getTypeByUfId(string $entityId): string
	{
		$map = array_flip(self::getUfEntityIds());

		return $map[$entityId] ?? '';
	}
}
