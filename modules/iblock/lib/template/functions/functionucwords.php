<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage iblock
 */
namespace Bitrix\Iblock\Template\Functions;

/**
 * Class FunctionUcwords
 * Represents function for uppercase first character of each word {=ucwords this.name}.
 *
 * @package Bitrix\Iblock\Template\Functions
 */
class FunctionUcwords extends FunctionBase
{
	/**
	 * Called by engine to process function call.
	 *
	 * @param array $parameters Function parameters.
	 *
	 * @return string
	 */
	public function calculate(array $parameters): string
	{
		$value = $this->parametersToString($parameters);

		return mb_convert_case($value, MB_CASE_TITLE);
	}
}
