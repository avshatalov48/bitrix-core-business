<?php

namespace Bitrix\Socialnetwork\Integration\Tasks\Flow;

use Bitrix\Main\Loader;

class FlowFeature
{
	public static function isOn(): bool
	{
		if (!Loader::includeModule('tasks') || !class_exists('\Bitrix\Tasks\Flow\FlowFeature'))
		{
			return false;
		}

		return \Bitrix\Tasks\Flow\FlowFeature::isOn();
	}
}