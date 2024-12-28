<?php

namespace Bitrix\Calendar\Integration\SocialNetwork\Collab;

use Bitrix\Main\Loader;

final class CollabFeature
{
	public static function isAvailable(): bool
	{
		return Loader::includeModule('socialnetwork')
			&& \Bitrix\Socialnetwork\Collab\CollabFeature::isOn()
		;
	}
}
