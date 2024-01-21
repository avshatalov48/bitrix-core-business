<?php

namespace Bitrix\Sender\Internals\Dto;

/**
 * Collection can only contain UpdateContactDTO items
 */
class UpdateContactDtoCollection
{
	/**
	 * @var array|UpdateContactDTO[]
	 */
	private array $items = [];

	/**
	 * Append update contact DTO to collection
	 *
	 * @param UpdateContactDTO $item
	 *
	 * @return $this
	 */
	public function append(UpdateContactDTO $item): self {
		$this->items[] = $item;
		return $this;
	}

	/**
	 * Get all items
	 *
	 * @return array|UpdateContactDTO[]
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
		return array_map(fn(UpdateContactDTO $item) => $item->toArray(), $this->items);
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
