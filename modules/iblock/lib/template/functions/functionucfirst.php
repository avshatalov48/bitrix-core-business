<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage iblock
 */
namespace Bitrix\Iblock\Template\Functions;

/**
 * Class FunctionUcfirst
 * Represents function for uppercase first character {=ucfirst this.name}.
 *
 * @package Bitrix\Iblock\Template\Functions
 */
class FunctionUcfirst extends FunctionBase
{
	/**
	 * Called by engine to process function call.
	 *
	 * @param array $parameters Function parameters.
	 *
	 * @return string
	 */
	public function calculate(array $parameters)
	{
		$value = $this->parametersToString($parameters);

		return mb_strtoupper(mb_substr($value, 0, 1)) . mb_substr($value, 1);
	}
}
