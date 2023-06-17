<?php

namespace Bitrix\Im\V2\Message\Reaction;

use Bitrix\Im\V2\Error;
use Bitrix\Main\Localization\Loc;

class ReactionError extends Error
{
	public const NOT_FOUND = 'REACTION_NOT_FOUND';
	public const ALREADY_SET = 'REACTION_ALREADY_SET';

	protected function loadErrorMessage($code, $replacements): string
	{
		return Loc::getMessage("ERROR_REACTION_{$code}", $replacements) ?: '';
	}

	protected function loadErrorDescription($code, $replacements): string
	{
		return Loc::getMessage("ERROR_REACTION_{$code}_DESC", $replacements) ?: '';
	}
}