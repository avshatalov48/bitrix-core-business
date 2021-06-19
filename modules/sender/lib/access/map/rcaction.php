<?php

namespace Bitrix\Sender\Access\Map;

use Bitrix\Sender\Access\ActionDictionary;
use Bitrix\Sender\Integration\Crm\ReturnCustomer\MessageBase;

class RcAction
{
	/**
	 * legacy action map
	 * @return array
	 */
	public static function getMap(): array
	{
		return [
			MessageBase::CODE_RC_LEAD => ActionDictionary::ACTION_RC_EDIT,
			MessageBase::CODE_RC_DEAL => ActionDictionary::ACTION_RC_EDIT,
			MessageBase::CODE_TOLOKA => ActionDictionary::ACTION_RC_VIEW,
		];
	}
}