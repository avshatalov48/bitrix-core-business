<?php

declare(strict_types=1);

namespace Bitrix\Rest\Service\APAuth;

use Bitrix\Main\Application;
use Bitrix\Main\Security\Random;
use Bitrix\Main\Type\DateTime;
use Bitrix\Rest\Entity\APAuth\Password;
use Bitrix\Rest\Enum;
use Bitrix\Rest\Entity\Collection\APAuth\PasswordCollection;
use Bitrix\Rest\Contract;
use Bitrix\Rest\Dto;
use Bitrix\Rest\Repository\APAuth\PasswordRepository;
use Bitrix\Rest\Repository\Exception\CreationFailedException;
use Bitrix\Rest\Service\ServiceContainer;

class PasswordService implements Contract\Service\APAuth\PasswordService
{
	private const BASE_CACHE_DIR = 'rest/apauth/password';

	public function __construct(
		private ?Contract\Repository\APAuth\PasswordRepository $passwordRepository = null,
		private ?Contract\Service\APAuth\PermissionService $permissionService = null,
	)
	{
		$this->passwordRepository ??= new PasswordRepository();
		$this->permissionService ??= ServiceContainer::getInstance()->getAPAuthPermissionService();
	}

	public function getSystemPasswordCollection(): PasswordCollection
	{
		return $this->passwordRepository->getByType(Enum\APAuth\PasswordType::System);
	}

	public function isSystemPasswordById(int $id): bool
	{
		$cache = Application::getInstance()->getCache();

		if (
			$cache->initCache(
				86400 * 7,
				'is_system_password',
				self::BASE_CACHE_DIR . '/' . $id))
		{
			$isSystemPassword = $cache->getVars();
		}
		else
		{
			$isSystemPassword = $this->passwordRepository->getById($id)?->getType() === Enum\APAuth\PasswordType::System;
			$cache->startDataCache();
			$cache->endDataCache($isSystemPassword);
		}

		return $isSystemPassword;
	}

	public function getPasswordById(int $id): ?Password
	{
		return $this->passwordRepository->getById($id);
	}

	public function deleteById(int $id): bool
	{
		return $this->passwordRepository->deleteById($id);
	}

	public function create(Dto\APAuth\CreatePasswordDto $createPasswordDto): ?Password
	{
		$password = new Password(
			id: 0,
			passwordString: Random::getString(16),
			userId: $createPasswordDto->getUserId(),
			type: $createPasswordDto->getType(),
			title: $createPasswordDto->getTitle(),
			comment: $createPasswordDto->getComment(),
			createdAt: new DateTime(),
		);

		try
		{
			$password = $this->passwordRepository->create($password);

			foreach ($createPasswordDto->getPermissions() as $permissionCode)
			{
				$createPermissionDto = new Dto\APAuth\CreatePermissionDto(
					permissionCode: $permissionCode,
					passwordId: $password->getId()
				);
				$this->permissionService->create($createPermissionDto);
			}

			return $password;
		}
		catch (CreationFailedException)
		{
			return null;
		}
	}

	public function clearCacheById(int $id): void
	{
		Application::getInstance()->getCache()->clean(self::BASE_CACHE_DIR . '/' . $id);
	}
}
