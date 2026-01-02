<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Integration\IM;

use Bitrix\Main\Loader;
use Bitrix\Socialnetwork\Item\Workgroup\Type;

class ChatType
{
	public static function getChatTypeByGroupType(Type $type): string
	{
		return match ($type) {
			Type::Collab => self::getCollabType(),
			default => self::getChatType(),
		};
	}

	public static function getChatType(): string
	{
		if (!self::isAvailable())
		{
			return '';
		}

		return \Bitrix\Im\V2\Chat::IM_TYPE_CHAT;
	}

	private static function isAvailable(): bool
	{
		return Loader::includeModule('im');
	}

	public static function getCollabType(): string
	{
		if (!self::isAvailable())
		{
			return '';
		}

		return \Bitrix\Im\V2\Chat::IM_TYPE_COLLAB;
	}
}
