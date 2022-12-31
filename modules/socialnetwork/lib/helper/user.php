<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2021 Bitrix
 */
namespace Bitrix\Socialnetwork\Helper;

use Bitrix\Main\ModuleManager;
use Bitrix\Main\UserTable;

class User
{
	protected static string $userTableClass = UserTable::class;

	public static function getCurrentUserId(): int
	{
		global $USER;

		return (int)$USER->getId();
	}

	public static function getUserListNameFormatted(array $userIdList = [], $params = []): array
	{
		static $cache = [];

		$nameFormat = ($params['nameFormat'] ?? \CSite::getNameFormat());

		$result = [];

		$userIdList = array_map(static function ($userId) {
			return (int)$userId;
		}, $userIdList);

		$userIdList = array_filter($userIdList, static function ($userId) {
			return $userId > 0;
		});

		$userIdList = array_unique($userIdList);

		if (empty($userIdList))
		{
			return $result;
		}

		if (!isset($cache[$nameFormat]))
		{
			$cache[$nameFormat] = [];
		}

		$result = array_filter($cache[$nameFormat], function($cacheItem, $userId) use ($userIdList) {
			return in_array($userId, $userIdList);
		}, ARRAY_FILTER_USE_BOTH);

		$userIdListToGet = array_diff($userIdList, array_keys($cache[$nameFormat]));
		if (!empty($userIdListToGet))
		{
			$res = self::$userTableClass::getList([
				'filter' => [
					'@ID' => $userIdListToGet,
				],
				'select' => [
					'ID',
					'LOGIN',
					'EMAIL',
					'NAME',
					'SECOND_NAME',
					'LAST_NAME',
				],
			]);

			$useLogin = ModuleManager::isModuleInstalled('intranet');

			while ($userFields = $res->fetch())
			{
				$value = \CUser::FormatName($nameFormat, $userFields, $useLogin, false);
				$result[(int)$userFields['ID']] = $value;
				$cache[$nameFormat][(int)$userFields['ID']] = $value;
			}
		}

		return $result;
	}
}
