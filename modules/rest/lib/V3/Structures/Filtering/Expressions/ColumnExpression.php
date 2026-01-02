<?php

namespace Bitrix\Rest\V3\Structures\Filtering\Expressions;

/**
 * Wrapper for column as value
 * @package    bitrix
 * @subpackage main
 */
class ColumnExpression implements Expression
{
	/**
	 * @param string $property
	 */
	public function __construct(protected string $property)
	{
    }

	/**
	 * @return string
	 */
	public function getProperty(): string
	{
		return $this->property;
	}

	/**
	 * @param string $property
	 */
	public function setProperty(string $property): void
	{
		$this->property = $property;
	}
}
