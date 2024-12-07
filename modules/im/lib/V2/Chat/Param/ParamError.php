<?php

namespace Bitrix\Im\V2\Chat\Param;

use Bitrix\Im\V2\Error;
use Bitrix\Main\Localization\Loc;

class ParamError extends Error
{
	public const
		EMPTY_PARAM = 'EMPTY_PARAM',
		EMPTY_PARAM_NAME = 'EMPTY_PARAM_NAME'
	;

	protected function loadErrorMessage($code, $replacements): string
	{
		return Loc::getMessage("ERROR_PARAM_CHAT_{$code}", $replacements) ?: '';
	}

	protected function loadErrorDescription($code, $replacements): string
	{
		return Loc::getMessage("ERROR_PARAM_CHAT_{$code}_DESC", $replacements) ?: '';
	}
}