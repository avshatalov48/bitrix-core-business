<?php

namespace Bitrix\Im\V2\Link\Reminder;

use Bitrix\Im\V2\Error;
use Bitrix\Main\Localization\Loc;

class ReminderError extends Error
{
	public const DATE_REMIND_PASSED = 'DATE_REMIND_PASSED';
	public const REMINDER_NOTIFY_ADD_ERROR = 'REMINDER_NOTIFY_ADD_ERROR';
	public const REMINDER_NOTIFY_DELETE_ERROR = 'REMINDER_NOTIFY_DELETE_ERROR';
	public const DATE_REMIND_EMPTY = 'DATE_REMIND_EMPTY';
	public const REMINDER_NOT_FOUND = 'REMINDER_NOT_FOUND';

	protected function loadErrorMessage($code, $replacements): string
	{
		return Loc::getMessage("ERROR_REMINDER_{$code}", $replacements) ?: '';
	}

	protected function loadErrorDescription($code, $replacements): string
	{
		return Loc::getMessage("ERROR_REMINDER_{$code}_DESC", $replacements) ?: '';
	}
}