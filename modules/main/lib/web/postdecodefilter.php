<?php

namespace Bitrix\Main\Web;

use Bitrix\Main\Type;

/**
 * @deprecated Does nothing.
 */
class PostDecodeFilter implements Type\IRequestFilter
{
	/**
	 * @param array $values
	 * @return null
	 */
	public function filter(array $values)
	{
		return null;
	}
}
