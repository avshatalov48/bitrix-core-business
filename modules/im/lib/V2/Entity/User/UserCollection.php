<?php

namespace Bitrix\Im\V2\Entity\User;

use Bitrix\Im\Model\StatusTable;
use Bitrix\Im\V2\Entity\EntityCollection;
use Bitrix\Main\UserTable;

/**
 * @method User next()
 * @method User current()
 * @method User offsetGet($offset)
 * @method User getById(int $id)
 */
class UserCollection extends EntityCollection
{
	public function __construct(array $usersIds = [])
	{
		parent::__construct();

		foreach ($usersIds as $userId)
		{
			$this[] = User::getInstance($userId);
		}
	}

	public function fillOnlineData(): void
	{
		$idsUsersWithoutOnlineData = [];

		foreach ($this as $user)
		{
			if (!$user->isOnlineDataFilled())
			{
				$idsUsersWithoutOnlineData[] = $user->getId();
			}
		}

		$idsUsersWithoutOnlineData = array_unique($idsUsersWithoutOnlineData);

		if (empty($idsUsersWithoutOnlineData))
		{
			return;
		}

		$select = ['USER_ID', 'STATUS', 'IDLE', 'MOBILE_LAST_DATE', 'LAST_ACTIVITY_DATE' => 'USER.LAST_ACTIVITY_DATE'];
		$statusesData = StatusTable::query()
			->setSelect($select)
			->whereIn('USER_ID', $idsUsersWithoutOnlineData)
			->fetchAll() ?: []
		;

		foreach ($statusesData as $statusData)
		{
			$this->getById((int)$statusData['USER_ID'])->setOnlineData($statusData);
		}
	}

	public function filterExtranet(): self
	{
		$filteredUsers = new static();

		foreach ($this as $user)
		{
			if (!$user->isExtranet())
			{
				$filteredUsers[] = $user;
			}
		}

		return $filteredUsers;
	}

	public static function filterOnlineUserId(array $userIds): array
	{
		if (empty($userIds))
		{
			return [];
		}

		$result = UserTable::query()
			->setSelect(['ID'])
			->whereIn('ID', $userIds)
			->where('IS_ONLINE', true)
			->fetchAll()
		;
		$onlineUsers = [];
		foreach ($result as $row)
		{
			$onlineUsers[] = (int)$row['ID'];
		}

		return $onlineUsers;
	}

	public function toRestFormat(array $option = []): array
	{
		$this->fillOnlineData();

		return parent::toRestFormat($option);
	}

	public static function getRestEntityName(): string
	{
		return 'users';
	}

	/**
	 * Collect only existing users
	 * @param $offset
	 * @param $value
	 * @return void
	 */
	public function offsetSet($offset, $value): void
	{
		/** @var User $value */
		if (!$value->isExist())
		{
			return;
		}

		parent::offsetSet($offset, $value);
	}
}