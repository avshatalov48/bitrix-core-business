<?php

namespace Bitrix\Calendar\Sharing\Link\Rule;

use Bitrix\Calendar\Sharing\Link\Helper;

class UserCrmDealRule extends LinkObjectRule
{
	public function getObjectType(): string
	{
		return Helper::USER_CRM_DEAL_SHARING_TYPE;
	}
}