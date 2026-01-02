<?php

namespace Bitrix\Vote\Integration\Mobile;

use Bitrix\Main\Loader;
use Bitrix\Mobile\Provider\UserRepository;
use Bitrix\Vote\Model\Dto\UserDtoByIdMap;
use Bitrix\Vote\Service\UserProvider\UserProviderContract;

class MobileUserProvider implements UserProviderContract
{
	public function isAvailable(): bool
	{
		return Loader::includeModule('mobile')
			&& class_exists('\Bitrix\Mobile\Provider\UserRepository')
			&& method_exists(UserRepository::class, 'getByIds')
		;
	}

	/**
	 * @param list<int> $ids
	 *
	 * @return UserDtoByIdMap
	 */
	public function getByUserIds(array $ids): UserDtoByIdMap
	{
		$map = new UserDtoByIdMap();
		if (!$this->isAvailable())
		{
			return $map;
		}

		$users = UserRepository::getByIds($ids);
		foreach ($users as $user)
		{
			$map->add($user->id, $user);
		}

		return $map;
	}
}