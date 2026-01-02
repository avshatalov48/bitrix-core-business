<?php

namespace Bitrix\Im\V2\Integration\AI;

use Bitrix\Im\V2\Error;
use Bitrix\Main\Localization\Loc;

class CopilotError extends Error
{
	public const
		AI_NOT_INSTALLED = 'COPILOT_NOT_INSTALLED',
		ROLE_NOT_FOUND = 'ROLE_NOT_FOUND',
		WRONG_CHAT_TYPE = 'WRONG_CHAT_TYPE',
		IDENTICAL_ROLE = 'IDENTICAL_ROLE',
		ENGINE_NOT_FOUND = 'ENGINE_NOT_FOUND',
		IDENTICAL_ENGINE = 'IDENTICAL_ENGINE',
		AI_NOT_AVAILABLE = 'AI_NOT_AVAILABLE',
		AI_NOT_ACTIVE = 'AI_NOT_ACTIVE',
		TRANSCRIPTION_NOT_ACTIVE = 'TRANSCRIPTION_NOT_ACTIVE',
		FILE_NOT_TRANSCRIBABLE = 'FILE_NOT_TRANSCRIBABLE'
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
