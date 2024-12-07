<?php

namespace Bitrix\Im\V2\Integration\AI;

use Bitrix\AI\Engine;
use Bitrix\AI\Tuning\Manager;
use Bitrix\Imbot\Bot\CopilotChatBot;
use Bitrix\Main\Loader;

class AIHelper
{
	public const CONTEXT_MODULE = 'im';
	public const CONTEXT_ID = 'copilot_chat';

	public static function getProviderName(): ?string
	{
		if (!Loader::includeModule('ai'))
		{
			return null;
		}

		if (!isset(\Bitrix\AI\Engine::CATEGORIES['text']))
		{
			return null;
		}

		$manager = new Manager();
		$item = $manager->getItem(Restriction::SETTING_COPILOT_CHAT_PROVIDER);
		if (!isset($item))
		{
			return null;
		}

		$engine = Engine::getByCode($item->getValue(), \Bitrix\AI\Context::getFake(), Engine::CATEGORIES['text']);

		if (isset($engine))
		{
			return $engine->getIEngine()->getName();
		}

		return null;
	}

	public static function containsCopilotBot(array $userIds): bool
	{
		return Loader::includeModule('imbot') && in_array(CopilotChatBot::getBotId(), $userIds, true);
	}
}