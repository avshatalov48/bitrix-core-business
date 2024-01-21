<?php

namespace Bitrix\Calendar\Sharing\Link\Rule;

use Bitrix\Calendar\Sharing\Link\Helper;

class UserRule extends LinkObjectRule
{
	public function getObjectType(): string
	{
		return Helper::USER_SHARING_TYPE;
	}
}