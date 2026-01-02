<?php

declare(strict_types=1);

namespace Bitrix\Main\Entity;

use Bitrix\Main\ArgumentException;

interface EntityCollectionInterface extends \Countable, \IteratorAggregate
{
	/**
	 * Add item to the collection and check its class.
	 *
	 * @param EntityInterface $item
	 *
	 * @return void
	 *
	 * @throws ArgumentException If passed item of the wrong class.
	 */
	public function add(EntityInterface $item): void;

	public function isEmpty(): bool;

	public function find(callable $callback): ?EntityInterface;

	public function filter(callable $callback): static;

	/**
	 * Run a map over each of the items in the collection
	 *
	 * @param callable $callback
	 *
	 * @return array Result of applying callback to each item of the collection
	 */
	public function map(callable $callback): array;
}
