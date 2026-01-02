<?php

namespace Bitrix\Sender\Internals\Dto;

/**
 * Collection can only contain UpdateContact items
 */
class UpdateContactDtoCollection
{
	/**
	 * @var array|UpdateContact[]
	 */
	private array $items = [];

	/**
	 * Append update contact DTO to collection
	 *
	 * @param UpdateContact $item
	 *
	 * @return $this
	 */
	public function append(UpdateContact $item): self {
		$this->items[] = $item;
		return $this;
	}

	/**
	 * Get all items
	 *
	 * @return array|UpdateContact[]
	 */
	public function all(): array {
		return $this->items;
	}

	/**
	 * To array all items
	 *
	 * @return array
	 */
	public function toArray(): array {
		return array_map(fn(UpdateContact $item) => $item->toArray(), $this->items);
	}

	/**
	 * @return array
	 */
	public function getOnDuplicateKeyUpdateFields()
	{
		$item = $this->items[0] ?? null;

		if (!$item)
		{
			return [];
		}

		return $item->getOnDuplicateKeyUpdateFields();
	}

	/**
	 * Get count
	 *
	 * @return int
	 */
	public function count(): int {
		return count($this->items);
	}
}
