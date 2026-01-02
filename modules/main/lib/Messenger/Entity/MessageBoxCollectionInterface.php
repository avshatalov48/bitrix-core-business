<?php

declare(strict_types=1);

namespace Bitrix\Main\Messenger\Entity;

/**
 * @internal
 */
interface MessageBoxCollectionInterface extends \Countable, \IteratorAggregate
{
	public function add(MessageBox $item): void;

	public function isEmpty(): bool;

	public function toArray(): array;
}
