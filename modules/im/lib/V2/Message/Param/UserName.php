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

	/**
	 * @param mixed $value
	 * @return mixed
	 */
	public function saveValueFilter($value)
	{
		if (!empty($value))
		{
			$value = \Bitrix\Im\Text::encodeEmoji($value);
		}

		return $value;
	}

	/**
	 * @param mixed $value
	 * @return mixed
	 */
	public function loadValueFilter($value)
	{
		if (!empty($value))
		{
			$value = \Bitrix\Im\Text::decodeEmoji($value);
		}

		return $value;
	}
}
