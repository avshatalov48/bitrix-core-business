<?php

namespace Bitrix\Im\V2\Bot;

use Bitrix\Im\V2\Error;
use Bitrix\Main\Localization\Loc;

class BotError extends Error
{
	public const BOT_CONTEXT_ERROR = 'BOT_CONTEXT_ERROR';

	protected function loadErrorMessage($code, $replacements): string
	{
		return Loc::getMessage("ERROR_BOT_{$code}", $replacements) ?: '';
	}

	protected function loadErrorDescription($code, $replacements): string
	{
		return Loc::getMessage("ERROR_BOT_{$code}_DESC", $replacements) ?: '';
	}
}
