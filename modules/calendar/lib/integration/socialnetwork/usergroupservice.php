<?php

namespace Bitrix\Calendar\Integration\SocialNetwork;

use Bitrix\Main\Loader;
use Bitrix\Socialnetwork\Internals\Registry\UserRegistry;

final class UserGroupService
{
	private static ?self $instance;

	public static function getInstance(): self
	{
		self::$instance ??= new self();

		return self::$instance;
	}

	/**
	 * Returns array like [<groupId:int> => <groupType:string>]
	 *
 	 * @return array<int, string>
	 */
	public function getUserGroups(int $userId): array
	{
		if (!$this->isAvailable())
		{
			return [];
		}

		return UserRegistry::getInstance($userId)->getUserGroups();
	}

	private function isAvailable(): bool
	{
		return Loader::includeModule('socialnetwork');
	}

	private function __construct()
	{
	}
}
