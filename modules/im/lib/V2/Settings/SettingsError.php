<?php

namespace Bitrix\Im\V2\Settings;

use Bitrix\Im\V2\Error;
use Bitrix\Main\Localization\Loc;

class SettingsError extends Error
{
	public const UNDEFINED_GROUP_ID = 'SETTINGS_UNDEFINED_GROUP_ID';
	public const ACCESS_DENIED = 'SETTINGS_ACCESS_DENIED';
	public const WRONG_SCHEME = 'WRONG_SCHEME';

	protected function loadErrorMessage($code, $replacements): string
	{
		return Loc::getMessage("ERROR_SETTINGS_{$code}", $replacements) ?: '';
	}

	protected function loadErrorDescription($code, $replacements): string
	{
		return Loc::getMessage("ERROR_SETTINGS_{$code}_DESC", $replacements) ?: '';
	}
}