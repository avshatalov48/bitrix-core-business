<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Site;

use Bitrix\Socialnetwork\Collab\Url\UrlManager;
use Bitrix\Socialnetwork\Item\Workgroup\Type;
use Bitrix\Socialnetwork\Provider\GroupProvider;

class GroupUrl
{
	public static function get(int $groupId, null|string|Type $groupType = null, array $parameters = []): string
	{
		$site = Site::getInstance();
		$type = Type::getDefault();

		if ($groupType === null)
		{
			$type = GroupProvider::getInstance()->getGroupType($groupId);
		}
		elseif (is_string($groupType))
		{
			$type = Type::tryFrom($groupType);
		}

		if ($type !== Type::Collab)
		{
			return $site->getDirectory() . 'workgroups/group/' . $groupId . '/';
		}

		return UrlManager::getCollabUrlById($groupId, $parameters);
	}
}