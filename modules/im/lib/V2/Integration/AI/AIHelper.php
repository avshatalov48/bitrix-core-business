<?php

namespace Bitrix\Im\V2\Integration\AI;

use Bitrix\AI\Context;
use Bitrix\Imbot\Bot\CopilotChatBot;
use Bitrix\Main\Loader;

class AIHelper
{
	public static function containsCopilotBot(array $userIds): bool
	{
		return Loader::includeModule('imbot') && in_array(CopilotChatBot::getBotId(), $userIds, true);
	}

	public static function getCopilotBotId(): int
	{
		if (!Loader::includeModule('imbot'))
		{
			return 0;
		}

		return CopilotChatBot::getBotId() ?: CopilotChatBot::register();
	}
}
