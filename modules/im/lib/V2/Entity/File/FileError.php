<?php

namespace Bitrix\Im\V2\Entity\File;

use Bitrix\Im\V2\Error;
use Bitrix\Main\Localization\Loc;

class FileError extends Error
{
	public const UNKNOWN_FILE_SUBTYPE = 'UNKNOWN_FILE_SUBTYPE';
	public const NOT_FOUND = 'FILE_NOT_FOUND';
	public const CREATE_SYMLINK = 'CREATE_SYMLINK_ERROR';
	public const SAVE_BEFORE_MIGRATION_ERROR = 'FILE_SAVE_BEFORE_MIGRATION_ERROR';

	protected function loadErrorMessage($code, $replacements): string
	{
		return Loc::getMessage("ERROR_FILE_{$code}", $replacements) ?: '';
	}

	protected function loadErrorDescription($code, $replacements): string
	{
		return Loc::getMessage("ERROR_FILE_{$code}_DESC", $replacements) ?: '';
	}
}