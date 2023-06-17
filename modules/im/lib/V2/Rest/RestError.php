<?php

namespace Bitrix\Im\V2\Rest;

use Bitrix\Im\V2\Error;
use Bitrix\Main\Localization\Loc;

class RestError extends Error
{
	public const ACCESS_ERROR = 'ACCESS_ERROR';

	protected function loadErrorMessage($code, $replacements): string
	{
		return Loc::getMessage("ERROR_REST_{$code}", $replacements) ?: '';
	}

	protected function loadErrorDescription($code, $replacements): string
	{
		return Loc::getMessage("ERROR_REST_{$code}_DESC", $replacements) ?: '';
	}
}