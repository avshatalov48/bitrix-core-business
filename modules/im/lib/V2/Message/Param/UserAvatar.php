<?php

namespace Bitrix\Im\V2\Message\Param;

use Bitrix\Im\V2\Message\Param;

class UserAvatar extends Param
{
	/**
	 * @return string
	 */
	public function getValue(): string
	{
		if ((int)$this->value > 0)
		{
			return \CIMChat::getAvatarImage((int)$this->value, 200, false);
		}

		return '';
	}

	/**
	 * @return string
	 */
	public function toPullFormat(): string
	{
		return $this->getValue();
	}
}
