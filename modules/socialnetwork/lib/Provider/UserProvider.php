<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Provider;

use Bitrix\Main\UserTable;
use Bitrix\Socialnetwork\Helper\InstanceTrait;
use Bitrix\Socialnetwork\Provider\User\User;
use Bitrix\Socialnetwork\Provider\User\UserCollection;
use CSite;
use CUser;

class UserProvider
{
	use InstanceTrait;

	protected static array $users = [];

	public function enrich(array $userIds): UserCollection
	{
		if (empty($userIds))
		{
			return new UserCollection();
		}

		$notStoredUserIds = $this->getNotStoredUserIds($userIds);
		$storedUserIds = $this->getStoredUserIds($userIds);

		$storedUsers = $this->getUsersFromStorage($storedUserIds);

		$userCollection = new UserCollection(...$storedUsers);
		if (empty($notStoredUserIds))
		{
			return $userCollection;
		}

		$select = [
			'ID',
			'NAME',
			'LAST_NAME',
			'SECOND_NAME',
			'LOGIN',
			'EMAIL',
			'TITLE',
		];

		$nameFormat = CSite::getNameFormat();

		$users = UserTable::query()
			->setSelect($select)
			->whereIn('ID', $notStoredUserIds)
			->exec()
			->fetchCollection();

		foreach ($users as $userEntity)
		{
			$fullName = CUser::FormatName(
				$nameFormat,
				[
					'NAME' => $userEntity->getName(),
					'LAST_NAME' => $userEntity->getLastName(),
					'SECOND_NAME' => $userEntity->getSecondName(),
					'LOGIN' => $userEntity->getLogin(),
					'EMAIL' => $userEntity->getEmail(),
					'TITLE' => $userEntity->getTitle(),
				],
				true,
				false
			);

			$user = new User(
				id: $userEntity->getId(),
				firstName: $userEntity->getName(),
				lastName: $userEntity->getLastName(),
				fullName: $fullName,
			);

			$userCollection->add($user);
		}

		return $userCollection;
	}

	protected function getNotStoredUserIds(array $userIds): array
	{
		return array_filter($userIds, static fn (int $userId): bool => !isset(static::$users[$userId]));
	}

	protected function getStoredUserIds(array $userIds): array
	{
		return array_filter($userIds, static fn (int $userId): bool => isset(static::$users[$userId]));
	}

	/** @return User[] */
	protected function getUsersFromStorage(array $userIds): array
	{
		return array_filter(static::$users, static fn (User $user): bool => in_array($user->getId(), $userIds, true));
	}
}