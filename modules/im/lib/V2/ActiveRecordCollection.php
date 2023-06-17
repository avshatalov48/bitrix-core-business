<?php

namespace Bitrix\Im\V2;

use Bitrix\Main\ORM\Objectify\Collection;
use Bitrix\Main\ORM\Objectify\EntityObject;
use Bitrix\Im\V2\Service\Context;

interface ActiveRecordCollection
{
	/**
	 * @return int[]
	 */
	public function getPrimaryIds(): array;

	/**
	 * Returns ORM tablet class name.
	 * @return string
	 */
	public static function getDataClass(): string;

	/**
	 * Returns collection item's  class name.
	 * @return string|ActiveRecord
	 */
	public static function getCollectionElementClass(): string;

	/**
	 * Restores object state from database.
	 * @param int[]|array|EntityObject[]|Collection $source
	 * @return Result
	 */
	public function load($source): Result;

	/**
	 * Fills and prepares the fields of the data entity
	 * @return Result
	 */
	//public function prepareFields(): Result;

	/**
	 * Returns ORM data entity.
	 * @return Collection
	 */
	public function getDataEntityCollection(): Collection;

	/**
	 * Saves collection objects states into database.
	 * @return Result
	 */
	public function save(): Result;

	/**
	 * Drops object from database.
	 * @return Result
	 */
	public function delete(): Result;

	/**
	 * @param array $filter
	 * @param array $order
	 * @param int|null $limit
	 * @param Context|null $context
	 * @return static
	 */
	public static function find(array $filter, array $order, ?int $limit = null, ?Context $context = null): self;

	/**
	 * Append collection with new item.
	 * @param ActiveRecord $entry
	 * @return static
	 */
	public function add(ActiveRecord $entry): self;
}