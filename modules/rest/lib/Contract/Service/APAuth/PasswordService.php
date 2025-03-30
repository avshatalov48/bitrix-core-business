<?php

declare(strict_types=1);

namespace Bitrix\Rest\Contract\Service\APAuth;

use Bitrix\Rest\Entity\Collection\APAuth\PasswordCollection;
use Bitrix\Rest\Entity\APAuth\Password;
use Bitrix\Rest\Dto;

interface PasswordService
{
	public function getSystemPasswordCollection(): PasswordCollection;
	public function isSystemPasswordById(int $id): bool;
	public function deleteById(int $id): bool;
	public function getPasswordById(int $id): ?Password;
	public function create(Dto\APAuth\CreatePasswordDto $createPasswordDto): ?Password;
	public function clearCacheById(int $id): void;
}
