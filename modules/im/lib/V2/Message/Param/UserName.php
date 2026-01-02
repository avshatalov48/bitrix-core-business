<?php

namespace Bitrix\Im\V2\Message\Param;

use Bitrix\Im\V2\Message\Param;

class UserName extends Param
{
	/**
	 * @return string
	 */
	public function getValue(): string
	{
		if (!empty($this->value))
		{
			return \htmlspecialcharsbx($this->value);
		}

		return '';
	}
}
