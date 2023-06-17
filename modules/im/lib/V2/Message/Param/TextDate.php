<?php

namespace Bitrix\Im\V2\Message\Param;

use Bitrix\Im\V2\Message\ParamArray;

class TextDate extends ParamArray
{
	/**
	 * @return string[]
	 */
	public function getValue(): array
	{
		$values = parent::getValue();

		if (!empty($values))
		{
			$values = array_map('htmlspecialcharsbx', $values);
		}

		return $values;
	}
}
