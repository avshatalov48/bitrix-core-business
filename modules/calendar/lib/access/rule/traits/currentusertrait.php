<?php

namespace Bitrix\Calendar\Access\Rule\Traits;

trait CurrentUserTrait
{
	private function hasCurrentUser(): bool
	{
		global $USER;
		if (!$USER || !is_object($USER))
		{
			return false;
		}

		return true;
	}
}