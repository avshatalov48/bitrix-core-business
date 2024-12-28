<?php

namespace Bitrix\Calendar\Integration\SocialNetwork\Collab;

use Bitrix\Calendar\Integration\SocialNetwork\AvatarService;
use Bitrix\Main\Loader;
use Bitrix\Main\ORM\Query\Filter\ConditionTree;
use Bitrix\Socialnetwork\Collab\Collab;
use Bitrix\Socialnetwork\Collab\Provider\CollabProvider;
use Bitrix\Socialnetwork\Collab\Provider\CollabQuery;

final class Collabs
{
	private static ?self $instance;

	public static function getInstance(): self
	{
		self::$instance ??= new self();

		return self::$instance;
	}

	public function getCollabIfExists(int $id): ?Collab
	{
		if (!$this->isAvailable())
		{
			return null;
		}

		try
		{
			return CollabProvider::getInstance()->getCollab($id);
		}
		catch (\Throwable)
		{
			return null;
		}
	}

	/**
	 * @param int[] $ids
	 * @return int[]
	 */
	public function getCollabIdsByGroupIds(array $ids): array
	{
		if (!$this->isAvailable())
		{
			return [];
		}

		$collabQuery = (new CollabQuery())
			->setWhere((new ConditionTree())->whereIn('ID', $ids))
		;

		return array_map(
			'intval',
			CollabProvider::getInstance()->getList($collabQuery)->getIdList()
		);
	}

	public function getById(int $id): ?Collab
	{
		if (!$this->isAvailable())
		{
			return null;
		}

		try
		{
			return (new CollabProvider())->getCollab($id);
		}
		catch (\Throwable)
		{
			return null;
		}
	}

	public function getCollabImagePath(int $imageId): ?string
	{
		if (!$this->isAvailable())
		{
			return null;
		}

		return (new AvatarService())->getAvatar($imageId)->getId();
	}

	private function isAvailable(): bool
	{
		return Loader::includeModule('socialnetwork');
	}

	private function __construct()
	{
	}
}
