<?php

namespace Bitrix\Im\V2\Sync;

use Bitrix\Im\V2\Error;
use Bitrix\Main\Localization\Loc;

class SyncError extends Error
{
	public const LAST_ID_AND_DATE_EMPTY = 'LAST_ID_AND_DATE_EMPTY_ERROR';

	protected function loadErrorMessage($code, $replacements): string
	{
		return Loc::getMessage("ERROR_SYNC_{$code}", $replacements) ?: '';
	}

	protected function loadErrorDescription($code, $replacements): string
	{
		return Loc::getMessage("ERROR_SYNC_{$code}_DESC", $replacements) ?: '';
	}
}