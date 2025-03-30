<?php

namespace Bitrix\Im\V2\Entity\File;

use Bitrix\Im\V2\Error;
use Bitrix\Main\Localization\Loc;

class FileError extends Error
{
	public const DISK_NOT_INSTALLED = 'DISK_NOT_INSTALLED';
	public const ACCESS_ERROR = 'FILE_ACCESS_ERROR';
	public const STORAGE_NOT_FOUND = 'STORAGE_NOT_FOUND';
	public const FOLDER_NOT_FOUND = 'FOLDER_NOT_FOUND';
	public const COPY_ERROR = 'COPY_ERROR';
	public const UNKNOWN_FILE_SUBTYPE = 'UNKNOWN_FILE_SUBTYPE';
	public const NOT_FOUND = 'FILE_NOT_FOUND';
	public const CREATE_SYMLINK = 'CREATE_SYMLINK_ERROR';
	public const SAVE_BEFORE_MIGRATION_ERROR = 'FILE_SAVE_BEFORE_MIGRATION_ERROR';

	protected function loadErrorMessage($code, $replacements): string
	{
		$postfix = '';

		if ($code === self::CREATE_SYMLINK)
		{
			$postfix = '_V3';
		}

		return Loc::getMessage("ERROR_FILE_{$code}{$postfix}", $replacements) ?: '';
	}

	protected function loadErrorDescription($code, $replacements): string
	{
		return Loc::getMessage("ERROR_FILE_{$code}_DESC", $replacements) ?: '';
	}
}