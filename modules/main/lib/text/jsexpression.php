<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2015 Bitrix
 */

namespace Bitrix\Main\Text;

/**
 * Class description
 * @package bitrix
 * @subpackage main
 */
class JsExpression
{
	/** @var string */
	protected $expression;

	function __construct($expression)
	{
		$this->expression = $expression;
	}

	function __toString()
	{
		return $this->expression;
	}
}
