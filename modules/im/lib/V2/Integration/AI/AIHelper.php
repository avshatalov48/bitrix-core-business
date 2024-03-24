<?php

namespace Bitrix\Im\V2\Integration\AI;

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

		$engine = \Bitrix\AI\Engine::getByCategory(\Bitrix\AI\Engine::CATEGORIES['text'], \Bitrix\AI\Context::getFake());
		if (isset($engine))
		{
			return $engine->getIEngine()->getName();
		}

		return null;
	}
}