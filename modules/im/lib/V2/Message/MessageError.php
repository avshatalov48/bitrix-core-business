<?php

namespace Bitrix\Im\V2\Message;

use Bitrix\Im\V2\Error;
use Bitrix\Main\Localization\Loc;

class MessageError extends Error
{
	public const
		WRONG_PARAMETER = 'WRONG_PARAMETER',
		WRONG_SENDER = 'WRONG_SENDER',
		EMPTY_MESSAGE = 'EMPTY_MESSAGE',
		NOTIFY_MODULE = 'NOTIFY_MODULE',
		NOTIFY_EVENT = 'NOTIFY_EVENT',
		NOTIFY_TYPE = 'NOTIFY_TYPE',
		NOTIFY_BUTTONS = 'NOTIFY_BUTTONS',
		MESSAGE_NOT_FOUND = 'MESSAGE_NOT_FOUND',
		MESSAGE_DUPLICATED_BY_UUID = 'MESSAGE_DUPLICATED_BY_UUID',
		MESSAGE_IS_ALREADY_FAVORITE = 'MESSAGE_IS_ALREADY_FAVORITE',
		MESSAGE_IS_ALREADY_PIN = 'MESSAGE_IS_ALREADY_PIN',
		MESSAGE_IS_ALREADY_IN_REMINDERS = 'MESSAGE_IS_ALREADY_IN_REMINDERS',
		MESSAGE_ACCESS_ERROR = 'MESSAGE_ACCESS_ERROR',
		DIFFERENT_CHAT_ERROR = 'MESSAGES_IN_DIFFERENT_CHAT_ERROR',
		TOO_MANY_MESSAGES = 'TOO_MANY_MESSAGES',
		SENDING_FAILED = 'SENDING_FAILED',
		MARK_FAILED = 'MESSAGE_MARK_FAILED'
	;

	protected function loadErrorMessage($code, $replacements): string
	{
		return Loc::getMessage("ERROR_MESSAGE_{$code}", $replacements) ?: '';
	}

	protected function loadErrorDescription($code, $replacements): string
	{
		return Loc::getMessage("ERROR_MESSAGE_{$code}_DESC", $replacements) ?: '';
	}
}
