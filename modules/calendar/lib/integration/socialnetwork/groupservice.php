<?php

namespace Bitrix\Calendar\Integration\SocialNetwork;

use Bitrix\Main\Loader;
use Bitrix\Socialnetwork\Internals\Registry\GroupRegistry;
use Bitrix\Socialnetwork\Item\Workgroup;

final class GroupService
{
	private static ?self $instance;

	public static function getInstance(): self
	{
		self::$instance ??= new self();

		return self::$instance;
	}

	public function getGroup(int $id): ?Workgroup
	{
		if (!$this->isAvailable())
		{
			return null;
		}

		return GroupRegistry::getInstance()->get($id);
	}

	public function getAvatar(int $imageId): ?array
	{
		if (!$this->isAvailable())
		{
			return null;
		}

		return (new AvatarService())->getAvatar($imageId)->toArray();
	}

	private function isAvailable(): bool
	{
		return Loader::includeModule('socialnetwork');
	}

	private function __construct()
	{
	}
}
