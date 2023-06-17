<?php

namespace Bitrix\Im\V2\Entity\Calendar;

use Bitrix\Im\V2\Error;
use Bitrix\Main\Localization\Loc;

class CalendarError extends Error
{
	public const CALENDAR_NOT_INSTALLED = 'CALENDAR_NOT_INSTALLED';
	public const ADD_CALENDAR_MESSAGE_FAILED = 'ADD_CALENDAR_MESSAGE_FAILED';
	public const ACCESS_ERROR = 'CALENDAR_ACCESS_ERROR';
	public const NOT_FOUND = 'CALENDAR_NOT_FOUND';
	public const DELETE_ERROR = 'CALENDAR_DELETE_ERROR';

	protected function loadErrorMessage($code, $replacements): string
	{
		return Loc::getMessage("ERROR_CALENDAR_{$code}", $replacements) ?: '';
	}

	protected function loadErrorDescription($code, $replacements): string
	{
		return Loc::getMessage("ERROR_CALENDAR_{$code}_DESC", $replacements) ?: '';
	}
}