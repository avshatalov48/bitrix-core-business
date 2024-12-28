<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Control\Command\ValueObject;

use Bitrix\Socialnetwork\Collab\Control\Feature\CollabFeature;
use Bitrix\Socialnetwork\Control\Command\ValueObject\Features;

class CollabFeatures extends Features
{
	public static function createWithDefaultValue(): static
	{
		$value = new static();

		$allowedFeatures = array_keys(\CSocNetAllowed::getAllowedFeatures());

		foreach ($allowedFeatures as $featureName)
		{
			$value->features[$featureName] = false;
		}

		foreach (CollabFeature::FEATURES as $featureName)
		{
			$value->features[$featureName] = true;
		}

		return $value;
	}
}