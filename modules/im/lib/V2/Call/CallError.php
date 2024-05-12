<?php

namespace Bitrix\Im\V2\Call;

use Bitrix\Im\V2\Error;
use Bitrix\Main\Localization\Loc;

class CallError extends Error
{
	public const
		ZOOM_ACTIVE_ERROR = 'ZOOM_ACTIVE_ERROR',
		ZOOM_AVAILABLE_ERROR = 'ZOOM_AVAILABLE_ERROR',
		ZOOM_CONNECTED_ERROR = 'ZOOM_CONNECTED_ERROR',
		ZOOM_CREATE_ERROR = 'ZOOM_CREATE_ERROR',
		CALL_NOT_FOUND = 'CALL_NOT_FOUND',
		SEND_PULL_ERROR = 'SEND_PULL_ERROR'
	;

	protected function loadErrorMessage($code, $replacements): string
	{
		return Loc::getMessage("ERROR_CALL_{$code}", $replacements) ?: '';
	}

	protected function loadErrorDescription($code, $replacements): string
	{
		return Loc::getMessage("ERROR_CALL_{$code}_DESC", $replacements) ?: '';
	}
}