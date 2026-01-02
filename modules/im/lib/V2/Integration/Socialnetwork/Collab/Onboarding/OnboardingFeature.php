<?php

declare(strict_types=1);

namespace Bitrix\Im\V2\Integration\Socialnetwork\Collab\Onboarding;

use Bitrix\Main\Loader;

class OnboardingFeature
{
	public static function isAvailable(): bool
	{
		if (!Loader::includeModule('socialnetwork'))
		{
			return false;
		}

		if (!class_exists(\Bitrix\Socialnetwork\Collab\Onboarding\OnboardingFeature::class))
		{
			return false;
		}

		return \Bitrix\Socialnetwork\Collab\Onboarding\OnboardingFeature::isAvailable();
	}
}
