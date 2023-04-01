<?php

namespace Bitrix\Calendar\Core\Role;

use Bitrix\Calendar\Core\Base\BaseException;
use Bitrix\Main\UserTable;
use Bitrix\Main;

class Helper
{
	/**
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 * @throws Main\ArgumentException
	 */
	public static function getRole(int $id, string $type): Role
	{
		switch ($type)
		{
			case User::TYPE:
				return self::getUserRole($id);
			case Company::TYPE:
				return self::getCompanyRole($id);
			case Group::TYPE:
				return self::getGroupRole($id);
			default:
				throw new BaseException('you should send type from Dictionary');
		}
	}

	/**
	 * @param int $id
	 *
	 * @return User|Role|null
	 *
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public static function getUserRole(int $id): Role
	{
		$user = User::$users[$id] ?? null;
		if (!$user)
		{
			$user = self::getUserObject($id);
		}

		if ($user)
		{
			User::$users[$id] = $user;

			$roleEntity = self::createUserRoleEntity($user);

			return new Role($roleEntity);
		}

		throw new BaseException('we not find this user');
	}

	public static function getCompanyRole(int $id): Role
	{
		$company = new Company('');
		return new Role($company);
	}

	public static function getGroupRole(int $id): Role
	{
		$group = new Group('');
		$group->setId($id);
		return new Role($group);
	}

	/**
	 * @throws Main\ArgumentException
	 * @throws BaseException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public static function getAttendeeRole(int $id): Attendee
	{
		if (! ($user = User::$users[$id]))
		{
			$user = self::getUserObject($id);
		}

		if ($user)
		{
			User::$users[$id] = $user;

			$roleEntity = self::createUserRoleEntity($user);

			return new Attendee($roleEntity);
		}

		throw new BaseException('we not find this user');
	}

	/**
	 * @param int $id
	 * @return Main\EO_User|null
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public static function getUserObject(int $id): ?Main\EO_User
	{
		if (isset(User::$users[$id]) && User::$users[$id])
		{
			return User::$users[$id];
		}

		return UserTable::query()
			->addFilter('=ID', $id)
			->setSelect(['NAME', 'LAST_NAME', 'ID', 'NOTIFICATION_LANGUAGE_ID', 'EMAIL', 'ACTIVE', 'EXTERNAL_AUTH_ID'])
			->exec()
			->fetchObject()
		;
	}

	/**
	 * @param Main\EO_User $user
	 * @return User
	 */
	public static function createUserRoleEntity(Main\EO_User $user): User
	{
		return (new User($user->getName()))
			->setLastName($user->getLastName())
			->setId($user->getId())
			->setLanguageId($user->get('NOTIFICATION_LANGUAGE_ID') ?? LANGUAGE_ID)
		;
	}
}