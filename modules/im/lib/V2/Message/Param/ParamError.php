<?php

namespace Bitrix\Im\V2\Message\Param;

use Bitrix\Im\V2\Error;
use Bitrix\Main\Localization\Loc;

class ParamError extends Error
{
	public const ATTACH_ERROR = 'PARAM_ATTACH_ERROR';
	public const MENU_ERROR = 'PARAM_MENU_ERROR';
	public const KEYBOARD_ERROR = 'PARAM_KEYBOARD_ERROR';

	protected function loadErrorMessage($code, $replacements): string
	{
		return Loc::getMessage("ERROR_PARAM_{$code}", $replacements) ?: '';
	}

	protected function loadErrorDescription($code, $replacements): string
	{
		return Loc::getMessage("ERROR_PARAM_{$code}_DESC", $replacements) ?: '';
	}
}