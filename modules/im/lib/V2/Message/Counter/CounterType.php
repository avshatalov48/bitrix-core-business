<?php

namespace Bitrix\Im\V2\Message\Counter;

use Bitrix\Im\V2\Chat;

enum CounterType: string
{
	case Chat = 'chat';
	case Comment = 'comment';
	case Copilot = 'copilot';
	case Openline = 'openline';
	case Collab = 'collab';

	public static function tryFromType(?string $type): self
	{
		return match ($type)
		{
			Chat::IM_TYPE_COMMENT => self::Comment,
			Chat::IM_TYPE_OPEN_LINE => self::Openline,
			Chat::IM_TYPE_COPILOT => self::Copilot,
			Chat::IM_TYPE_COLLAB => self::Collab,
			default => self::Chat,
		};
	}

	public static function tryFromChat(Chat $chat): self
	{
		return self::tryFromType($chat->getType());
	}
}
