<?php

namespace Bitrix\Rest\V3\Structures\Filtering\Expressions;

/**
 * Wrapper for column length as value
 * @package    bitrix
 * @subpackage main
 */
class LengthExpression implements Expression
{
	/**
	 * @param string $property
	 */
	public function __construct(readonly protected string $property)
	{
    }

	/**
	 * @return string
	 */
	public function getProperty(): string
	{
		return $this->property;
	}
}
