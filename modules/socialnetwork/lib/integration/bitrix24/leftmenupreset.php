<?php

namespace Bitrix\Socialnetwork\Integration\Bitrix24;

use Bitrix\Bitrix24\Preset\PresetSocialAI;
use Bitrix\Main\Loader;

class LeftMenuPreset
{
	public function getSocialAiCode(): ?string
	{
		if (!Loader::includeModule('bitrix24'))
		{
			return null;
		}

		return PresetSocialAI::CODE;
	}
}