<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2021 Bitrix
 */

namespace Bitrix\Main\Authentication\Policy;

class GreaterRule extends Rule
{
	/**
	 * @inheritdoc
	 */
	public function compare($value): bool
	{
		return ($value > $this->value);
	}
}
