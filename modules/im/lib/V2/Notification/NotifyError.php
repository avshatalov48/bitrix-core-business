<?php

namespace Bitrix\Im\V2\Notification;

use Bitrix\Im\V2\Error;
use Bitrix\Main\Localization\Loc;

class NotifyError extends Error
{
	public const GROUP_NOT_FOUND = 'GROUP_NOT_FOUND';
	public const CONDITION_NOT_FOUND = 'CONDITION_NOT_FOUND';
	public const CONDITION_ALREADY_EXISTS = 'CONDITION_ALREADY_EXISTS';
	public const GROUP_CREATE_ERROR = 'GROUP_CREATE_ERROR';

	protected function loadErrorMessage($code, $replacements): string
	{
		return match ($code)
		{
			self::GROUP_NOT_FOUND => Loc::getMessage('ERROR_NOTIFY_GROUP_NOT_FOUND') ?? '',
			self::CONDITION_NOT_FOUND => Loc::getMessage('ERROR_NOTIFY_CONDITION_NOT_FOUND') ?? '',
			self::CONDITION_ALREADY_EXISTS => Loc::getMessage('ERROR_NOTIFY_CONDITION_ALREADY_EXISTS') ?? '',
			self::GROUP_CREATE_ERROR => Loc::getMessage('ERROR_NOTIFY_GROUP_CREATE_ERROR') ?? '',
			default => '',
		};
	}

	protected function loadErrorDescription($code, $replacements): string
	{
		return '';
	}
}