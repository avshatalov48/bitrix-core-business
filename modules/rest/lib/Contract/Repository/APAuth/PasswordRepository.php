<?php

declare(strict_types=1);

namespace Bitrix\Rest\Contract\Repository\APAuth;

use Bitrix\Rest\Entity;
use Bitrix\Rest\Enum;
use Bitrix\Rest\Entity\Collection\APAuth\PasswordCollection;
use Bitrix\Rest\Repository\Exception\CreationFailedException;

interface PasswordRepository
{
	/**
	 * @throws CreationFailedException
	 */
	public function create(Entity\APAuth\Password $password): Entity\APAuth\Password;
	public function getByType(Enum\APAuth\PasswordType $type): PasswordCollection;
	public function deleteById(int $id): bool;
	public function getById(int $id): ?Entity\APAuth\Password;
}
