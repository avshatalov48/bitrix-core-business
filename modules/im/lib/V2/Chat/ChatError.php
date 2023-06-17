<?php

namespace Bitrix\Im\V2\Chat;

use Bitrix\Im\V2\Error;
use Bitrix\Main\Localization\Loc;

class ChatError extends Error
{
	public const
		WRONG_TYPE = 'WRONG_MESSAGE_TYPE',
		WRONG_PARAMETER = 'WRONG_PARAMETER',
		WRONG_SENDER = 'WRONG_SENDER',
		WRONG_RECIPIENT = 'WRONG_RECIPIENT',
		WRONG_TARGET_CHAT = 'WRONG_TARGET_CHAT',
		WRONG_COLOR = 'WRONG_COLOR',
		WRONG_PARENT_CHAT = 'WRONG_PARENT_CHAT',
		WRONG_PARENT_MESSAGE = 'WRONG_PARENT_MESSAGE',
		ACCESS_DENIED = 'ACCESS_DENIED',
		NOT_FOUND = 'NOT_FOUND',
		BEFORE_SEND_EVENT = 'EVENT_MESSAGE_SEND',
		FROM_OTHER_MODULE = 'FROM_OTHER_MODULE',
		CREATION_ERROR = 'CREATION_ERROR',
		ID_EMPTY_ERROR = 'ID_EMPTY_ERROR'
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
