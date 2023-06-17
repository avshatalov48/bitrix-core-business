<?php

namespace Bitrix\Im\V2\Entity\Url;

use Bitrix\Im\V2\Error;
use Bitrix\Main\Localization\Loc;

class UrlError  extends Error
{
	public const NOT_FOUND = 'URL_NOT_FOUND';
	public const DELETE_ERROR = 'URL_DELETE_ERROR';
	public const ACCESS_ERROR = 'URL_ACCESS_ERROR';
	public const SAVE_BEFORE_MIGRATION_ERROR = 'URL_SAVE_BEFORE_MIGRATION_ERROR';

	protected function loadErrorMessage($code, $replacements): string
	{
		return Loc::getMessage("ERROR_URL_{$code}", $replacements) ?: '';
	}

	protected function loadErrorDescription($code, $replacements): string
	{
		return Loc::getMessage("ERROR_URL_{$code}_DESC", $replacements) ?: '';
	}
}