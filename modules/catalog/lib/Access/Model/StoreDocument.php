<?php

namespace Bitrix\Catalog\Access\Model;

use Bitrix\Catalog\StoreDocumentTable;
use Bitrix\Main\Access\AccessibleItem;

class StoreDocument implements AccessibleItem
{
	public const TYPE_ARRIVAL = StoreDocumentTable::TYPE_ARRIVAL;
	public const TYPE_STORE_ADJUSTMENT = StoreDocumentTable::TYPE_STORE_ADJUSTMENT;
	public const TYPE_MOVING = StoreDocumentTable::TYPE_MOVING;
	public const TYPE_DEDUCT = StoreDocumentTable::TYPE_DEDUCT;
	public const TYPE_SALES_ORDERS = StoreDocumentTable::TYPE_SALES_ORDERS;

	private int $id;
	private ?string $type;

	/**
	 * @param int $id
	 */
	public function __construct(int $id)
	{
		$this->id = $id;
	}

	/**
	 * @inheritDoc
	 *
	 * @return StoreDocument
	 */
	public static function createFromId(int $itemId): StoreDocument
	{
		return new static($itemId);
	}

	/**
	 * Create from fields array.
	 *
	 * @param array $fields
	 *
	 * @return StoreDocument
	 */
	public static function createFromArray(array $fields): StoreDocument
	{
		$self = new static(
			(int)($fields['ID'] ?? 0)
		);
		$self->type = $fields['DOC_TYPE'] ?? null;

		return $self;
	}

	/**
	 * Create for sale realization.
	 *
	 * Sets need document type.
	 *
	 * @param int $id
	 *
	 * @return StoreDocument
	 */
	public static function createForSaleRealization(int $id): StoreDocument
	{
		$self = new static($id);
		$self->type = self::TYPE_SALES_ORDERS;

		return $self;
	}

	/**
	 * @inheritDoc
	 */
	public function getId(): int
	{
		return $this->id;
	}

	/**
	 * Document type.
	 *
	 * @return string|null returns `null` is document not found.
	 */
	public function getType(): ?string
	{
		if (!isset($this->type))
		{
			$row = StoreDocumentTable::getRow([
				'select' => [
					'DOC_TYPE',
				],
				'filter' => [
					'=ID' => $this->getId(),
				],
			]);

			if ($row['DOC_TYPE'])
			{
				$this->type = (string)$row['DOC_TYPE'];
			}
			else
			{
				$this->type = null;
			}
		}

		return $this->type;
	}
}
