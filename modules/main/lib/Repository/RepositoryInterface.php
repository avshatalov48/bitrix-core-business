<?php

declare(strict_types=1);

namespace Bitrix\Main\Repository;

use Bitrix\Main\Entity\EntityInterface;
use Bitrix\Main\Repository\Exception\PersistenceException;

interface RepositoryInterface
{
	public function getById(mixed $id): ?EntityInterface;

	/**
	 * @throws PersistenceException
	 */
	public function save(EntityInterface $entity): void;

	/**
	 * @throws PersistenceException
	 */
	public function delete(mixed $id): void;
}
