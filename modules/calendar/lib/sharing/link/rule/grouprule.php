<?php

namespace Bitrix\Calendar\Sharing\Link\Rule;

use Bitrix\Calendar\Sharing\Link\Helper;

class GroupRule extends LinkObjectRule
{
	public function getObjectType(): string
	{
		return Helper::GROUP_SHARING_TYPE;
	}
}
