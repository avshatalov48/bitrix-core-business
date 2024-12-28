<?php

namespace Bitrix\SocialNetwork\Collab\Access\Rule\Trait;

use Bitrix\Socialnetwork\Collab\Provider\CollabOptionProvider;

trait GetOptionTrait
{
	protected static array $options = [];

	protected function getCollabOption(int $collabId, string $name): mixed
	{
		if (!isset(static::$options[$collabId]))
		{
			$options = CollabOptionProvider::getInstance()->get($collabId);
			foreach ($options as $option)
			{
				static::$options[$collabId][$option->name] = $option->value;
			}
		}

		return static::$options[$collabId][$name] ?? null;
	}
}