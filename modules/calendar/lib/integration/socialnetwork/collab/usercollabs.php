<?php

namespace Bitrix\Calendar\Integration\SocialNetwork\Collab;

use Bitrix\Main\Loader;
use Bitrix\Socialnetwork\Collab\Provider\CollabProvider;
use Bitrix\Socialnetwork\Collab\Provider\CollabQuery;

final class UserCollabs
{
	private static ?self $instance;

	public static function getInstance(): self
	{
		self::$instance ??= new self();

		return self::$instance;
	}

	public function get(int $userId): array
	{
		if (!$this->isAvailable())
		{
			return [];
		}

		return (new CollabProvider())
			->getListByUserId($userId, (new CollabQuery())->setSelect(['ID', 'NAME']))
			->toArray();
	}

	/**
	 * @return int[]
	 */
	public function getIds(int $userId): array
	{
		if (!$this->isAvailable())
		{
			return [];
		}

		$ids = (new CollabProvider())->getListByUserId($userId, (new CollabQuery())->setSelect(['ID']))->getIdList();

		return array_map('intval', $ids);
	}

	private function isAvailable(): bool
	{
		return Loader::includeModule('socialnetwork');
	}

	private function __construct()
	{
	}
}
