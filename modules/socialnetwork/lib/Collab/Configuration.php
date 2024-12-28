<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab;

class Configuration
{
	public static function getEntityConfig(string $type): ?array
	{
		$collabConfig = \Bitrix\Main\Config\Configuration::getInstance('socialnetwork')->get('collab');
		if (!is_array($collabConfig))
		{
			return null;
		}

		$entities = $collabConfig['entities'] ?? [];
		if (!is_array($entities))
		{
			return null;
		}

		return $entities[$type] ?? null;
	}
}