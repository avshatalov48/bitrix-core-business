<?php

namespace Bitrix\Im\V2\Service;

use Bitrix\Im\V2\Error;
use Bitrix\Main\Localization\Loc;

class MessengerError extends Error
{
	public const PULL_NOT_ENABLED = 'PULL_NOT_ENABLED';
	public const MESSENGER_NOT_ENABLED = 'MESSENGER_NOT_ENABLED';

	protected function loadErrorMessage($code, $replacements): string
	{
		return Loc::getMessage("ERROR_MESSENGER_{$code}", $replacements) ?: '';
	}

	protected function loadErrorDescription($code, $replacements): string
	{
		return Loc::getMessage("ERROR_MESSENGER_{$code}_DESC", $replacements) ?: '';
	}
}