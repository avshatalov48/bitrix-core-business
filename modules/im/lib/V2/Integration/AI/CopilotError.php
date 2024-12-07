<?php

namespace Bitrix\Im\V2\Integration\AI;

use Bitrix\Im\V2\Error;
use Bitrix\Main\Localization\Loc;

class CopilotError extends Error
{
	public const
		AI_NOT_INSTALLED = 'COPILOT_NOT_INSTALLED',
		ROLE_NOT_FOUNT = 'ROLE_NOT_FOUNT',
		WRONG_CHAT_TYPE = 'WRONG_CHAT_TYPE',
		IDENTICAL_ROLE = 'IDENTICAL_ROLE'
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