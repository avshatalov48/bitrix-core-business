<?php

namespace Bitrix\Socialnetwork\Integration\AI;

use Bitrix\AI\Context;
use Bitrix\AI\Engine;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;

final class Controller
{
	const TEXT_CATEGORY = 'text';
	const IMAGE_CATEGORY = 'image';

	public static function isAvailable(string $category, string $contextId = ''): bool
	{
		if (!Loader::includeModule('ai'))
		{
			return false;
		}

		$engine = Engine::getByCategory($category, new Context('socialnetwork', $contextId));
		if (is_null($engine))
		{
			return false;
		}

		return Option::get('socialnetwork', 'ai_base_enabled', 'N') === 'Y';
	}
}