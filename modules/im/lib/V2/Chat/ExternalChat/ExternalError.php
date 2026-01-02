<?php

namespace Bitrix\Im\V2\Chat\ExternalChat;

use Bitrix\Im\V2\Error;
use Bitrix\Main\Localization\Loc;

class ExternalError extends Error
{
	public const FROM_EVENT = 'ERROR_FROM_EVENT';

	protected function loadErrorMessage($code, $replacements): string
	{
		return Loc::getMessage("ERROR_EXTERNAL_{$code}", $replacements) ?: '';
	}

	protected function loadErrorDescription($code, $replacements): string
	{
		return Loc::getMessage("ERROR_EXTERNAL_{$code}_DESC", $replacements) ?: '';
	}
}