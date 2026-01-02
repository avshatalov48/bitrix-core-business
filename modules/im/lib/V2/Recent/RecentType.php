<?php

namespace Bitrix\Im\V2\Recent;

use Bitrix\Im\V2\Chat;
use JsonSerializable;

enum RecentType: string
	implements JsonSerializable
{
	case User = 'user';
	case Chat = 'chat';

	public static function tryFromChatType(string $chatType): self
	{
		return match ($chatType)
		{
			Chat::IM_TYPE_PRIVATE => self::User,
			default => self::Chat,
		};
	}

	public function jsonSerialize(): string
	{
		return $this->value;
	}
}
