<?php

namespace Bitrix\Im\V2\Recent;

use Bitrix\Im\V2\Error;
use Bitrix\Main\Localization\Loc;

class RecentError extends Error
{
	public const
		WRONG_DATETIME_FORMAT = 'WRONG_DATETIME_FORMAT'
	;

	protected function loadErrorMessage($code, $replacements): string
	{
		return Loc::getMessage("ERROR_CHAT_{$code}", $replacements) ?: '';
	}

	protected function loadErrorDescription($code, $replacements): string
	{
		return Loc::getMessage("ERROR_CHAT_{$code}_DESC", $replacements) ?: '';
	}
}
