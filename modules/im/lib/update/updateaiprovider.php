<?php

namespace Bitrix\Im\Update;

use Bitrix\AI\Engine;
use Bitrix\AI\Tuning\Manager;
use Bitrix\Im\V2\Integration\AI\Restriction;
use Bitrix\Main\Loader;

class UpdateAIProvider
{
	public static function updateProviderAgent()
	{
		if (!Loader::includeModule('im') || !Loader::includeModule('ai'))
		{
			return '';
		}

		$textProvider = Engine::getByCategory(\Bitrix\AI\Engine::CATEGORIES['text'], \Bitrix\AI\Context::getFake());
		if (!isset($textProvider))
		{
			return '';
		}

		$oldProviderCode = $textProvider->getIEngine()->getCode();

		$manager = new Manager();
		$item = $manager->getItem(Restriction::SETTING_COPILOT_CHAT_PROVIDER);
		if (!isset($item))
		{
			return '';
		}

		if ($item->getCode() === $oldProviderCode)
		{
			return '';
		}

		$item->setValue($oldProviderCode);
		$manager->save();

		return '';
	}
}