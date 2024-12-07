<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage iblock
 */
namespace Bitrix\Iblock\Template\Functions;

/**
 * Class FunctionStripTags
 * Represents function for remove tags from string {=striptags this.name}.
 *
 * @package Bitrix\Iblock\Template\Functions
 */
class FunctionStripTags extends FunctionBase
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
		return strip_tags($this->parametersToString($parameters));
	}
}
