<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2016 Bitrix
 */

namespace Bitrix\Main\ORM\Query\Filter\Expressions;

/**
 * Wrapper for columns values in QueryFilter.
 * @package    bitrix
 * @subpackage main
 */
class ColumnExpression extends Expression
{
	/**
	 * @var string
	 */
	protected $definition;

	/**
	 * @param $definition
	 */
	public function __construct($definition)
	{
		$this->definition = $definition;
	}

	/**
	 * @return string
	 */
	public function getDefinition()
	{
		return $this->definition;
	}

	/**
	 * @param string $definition
	 */
	public function setDefinition($definition)
	{
		$this->definition = $definition;
	}
}
