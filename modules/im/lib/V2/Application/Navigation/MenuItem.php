<?php

declare(strict_types=1);

namespace Bitrix\Im\V2\Application\Navigation;

/**
 * Represents a single menu item for communication navigation menu
 */
class MenuItem
{
	/**
	 * @param string $id non-unique id
	 * @param string|null $text text caption to display
	 * @param bool $isVisible visibility marker
	 * @param int|null $entityId rest app id
	 */
	public function __construct(
		protected string $id,
		protected ?string $text,
		protected bool $isVisible = true,
		protected ?int $entityId = null,
	) {}

	/**
	 * Returns formatted menu item data as array structure
	 *
	 * @return array{id: string, text: string, entityId: int|null}
	 */
	public function toArray(): array
	{
		return [
			'id' => $this->id,
			'text' => $this->text,
			'entityId' => $this->entityId,
		];
	}

	public function isVisible(): bool
	{
		return $this->isVisible;
	}
}
