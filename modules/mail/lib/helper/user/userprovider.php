<?php

namespace Bitrix\Mail\Helper\User;

use Bitrix\Main\UserTable;

final class UserProvider
{
	private static array $usersCached = [];

	private Tool $userTool;

	public function __construct()
	{
		$this->userTool = new Tool();
	}

	/**
	 * @return User[]
	 */
	public function getUsersInfo(array $userIds): array
	{
		$usersObjects = [];

		foreach ($userIds as $key => $userId)
		{
			if (!is_int($userId))
			{
				continue;
			}

			$userData = $this->getFromCache($userId);
			if ($userData)
			{
				$usersObjects[$userId] = new User(
					$userData,
				);
				unset($userIds[$key]);
			}
		}
		if (!$userIds)
		{
			return $usersObjects;
		}

		$usersData = $this->getUsers($userIds);

		foreach ($usersData as $userData)
		{
			$photoId = $userData['PERSONAL_PHOTO'] ?: 0;
			$userData['id'] = $userData['ID'];
			$userData['avatar'] = $photoId ? $this->userTool->resizePhoto($photoId, 100, 100) : [];
			$userData['name'] = $this->userTool->formatName($userData);
			$userData['pathToProfile'] = $this->userTool->getPathToProfile($userData['ID']);
			$userData['position'] = $userData['WORK_POSITION'];

			$usersObjects[$userData['ID']] = $user = new User(
				$userData,
			);

			self::$usersCached[$userData['ID']] = $user->toArray();
		}

		return $usersObjects;
	}

	private function getFromCache(int $userId): ?array
	{
		return self::$usersCached[$userId] ?? null;
	}

	/**
	 * @param array $userIds
	 * @return array{
	 *     ID: string,
	 *     NAME: string,
	 *     LAST_NAME: string,
	 *     LOGIN: string,
	 *     PERSONAL_PHOTO: string,
	 *     WORK_POSITION: string,
	 * }
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	private function getUsers(array $userIds): array
	{
		$query = UserTable::query()
			->setSelect([
				'ID',
				'NAME',
				'LAST_NAME',
				'LOGIN',
				'PERSONAL_PHOTO',
				'WORK_POSITION',
			])
			->whereIn('ID', $userIds)
		;

		return $query->fetchAll();
	}
}
