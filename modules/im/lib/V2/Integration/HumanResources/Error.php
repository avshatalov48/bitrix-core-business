<?php

namespace Bitrix\Im\V2\Integration\HumanResources;

use Bitrix\Main\Localization\Loc;

class Error extends \Bitrix\Im\V2\Error
{
	public const LINK_ERROR = 'LINK_TO_STRUCTURE_NODE_ERROR';
	public const UNLINK_ERROR = 'UNLINK_STRUCTURE_NODE_ERROR';

	protected function loadErrorMessage($code, $replacements): string
	{
		return Loc::getMessage("ERROR_CHAT_HUMAN_RESOURCES_{$code}", $replacements) ?: '';
	}

	protected function loadErrorDescription($code, $replacements): string
	{
		return Loc::getMessage("ERROR_CHAT_HUMAN_RESOURCES_{$code}_DESC", $replacements) ?: '';
	}
}