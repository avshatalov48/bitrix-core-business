<?php

namespace Bitrix\Im\V2\Import;

use Bitrix\Im\V2\Error;
use Bitrix\Main\Localization\Loc;

class ImportError extends Error
{
	public const ACCESS_ERROR = 'IMPORT_ACCESS_ERROR';
	public const FILE_ACCESS_ERROR = 'IMPORT_FILE_ACCESS_ERROR';
	public const UPDATE_MESSAGE_ERROR = 'IMPORT_UPDATE_MESSAGE_ERROR';
	public const ADD_MESSAGE_ERROR = 'IMPORT_ADD_MESSAGE_ERROR';
	public const DATETIME_FORMAT_ERROR = 'IMPORT_DATETIME_ERROR';
	public const FILE_NOT_FOUND = 'IMPORT_FILE_NOT_FOUND';
	public const DATETIME_FORMAT_ERROR_FIRST = 'IMPORT_DATETIME_FORMAT_ERROR_FIRST';
	public const CHRONOLOGY_ERROR = 'IMPORT_CHRONOLOGY_ERROR';
	public const PRIVATE_CHAT_COUNT_USERS_ERROR = 'IMPORT_PRIVATE_CHAT_COUNT_USERS_ERROR';

	protected function loadErrorMessage($code, $replacements): string
	{
		return Loc::getMessage("ERROR_IMPORT_{$code}", $replacements) ?: '';
	}

	protected function loadErrorDescription($code, $replacements): string
	{
		return Loc::getMessage("ERROR_IMPORT_{$code}_DESC", $replacements) ?: '';
	}
}