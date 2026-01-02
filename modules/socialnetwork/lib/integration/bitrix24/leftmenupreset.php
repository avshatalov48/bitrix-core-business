<?php

namespace Bitrix\Socialnetwork\Integration\Bitrix24;

use Bitrix\Bitrix24\Preset\PresetSocialAI;
use Bitrix\Main\Config\Option;
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

	public function isCurrentPresetIsSocialAi(): bool
	{
		if (!Loader::includeModule('bitrix24'))
		{
			return false;
		}

		$currentPresetCode = Option::get('bitrix24', 'preset:id', null);
		$tasksAiPresetCode = $this->getSocialAiCode();

		return !is_null($currentPresetCode)
			&& $currentPresetCode === $tasksAiPresetCode;
	}
}