<?php

namespace Bitrix\Vote\Service\UserProvider;

use Bitrix\Vote\Model\Dto\UserDtoByIdMap;

interface UserProviderContract
{
	/**
	 * @param list<int> $ids
	 *
	 * @return UserDtoByIdMap
	 */
	public function getByUserIds(array $ids): UserDtoByIdMap;
}