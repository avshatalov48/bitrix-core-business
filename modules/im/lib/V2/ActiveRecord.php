<?php

namespace Bitrix\Im\V2;

use Bitrix\Main\ORM\Objectify\EntityObject;

interface ActiveRecord
{
	/**
	 * @return int|null
	 */
	public function getPrimaryId(): ?int;

	/**
	 * @param int $primaryId
	 * @return self
	 */
	public function setPrimaryId(int $primaryId): self;

	/**
	 * Returns ORM tablet class name.
	 * @return string
	 */
	public static function getDataClass(): string;

	/**
	 * Restores object state from database.
	 * @param int|array|EntityObject $source
	 * @return Result
	 */
	public function load($source): Result;

	/**
	 * Fills and prepares the fields of the data entity.
	 * @return Result
	 */
	public function prepareFields(): Result;

	/**
	 * Returns ORM data entity.
	 * @return EntityObject
	 */
	public function getDataEntity(): EntityObject;

	/**
	 * Saves object state into database.
	 * @return Result
	 */
	public function save(): Result;

	/**
	 * Drops object from database.
	 * @return Result
	 */
	public function delete(): Result;

	/**
	 * Marks object changed.
	 * @return self
	 */
	public function markChanged(): self;

	/**
	 * Tells true if object has been changed.
	 * @return bool
	 */
	public function isChanged(): bool;

	/**
	 * Marks object to drop on save.
	 * @return self
	 */
	public function markDrop(): self;

	/**
	 * Tells true if object marked to drop.
	 * @return bool
	 */
	public function isDeleted(): bool;

	/**
	 * Fills object's fields with provided values.
	 * @param array $source
	 * @return self
	 */
	public function fill(array $source): self;

	/**
	 * Returns object state as array.
	 * @return array
	 */
	public function toArray(): array;
}