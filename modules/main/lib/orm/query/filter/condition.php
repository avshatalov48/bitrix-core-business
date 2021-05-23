<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2017 Bitrix
 */

namespace Bitrix\Main\ORM\Query\Filter;
use Bitrix\Main\ORM\Fields\Field;
use Bitrix\Main\ORM\Query\Filter\Expressions\Expression as FilterExpression;
use Bitrix\Main\ORM\Query\Filter\Expressions\ColumnExpression;

/**
 * Single condition handler.
 *
 * @package    bitrix
 * @subpackage main
 */
class Condition
{
	/** @var string|Field|null */
	protected $column;

	/** @var string Key of Operator::$operators */
	protected $operator;

	/** @var mixed */
	protected $value;

	/**
	 * QueryFilterCondition constructor.
	 *
	 * @param string|Field|null $column
	 * @param string $operator
	 * @param mixed  $value
	 */
	public function __construct($column, $operator, $value)
	{
		$this->column = $column;
		$this->operator = $operator;
		$this->value = $value;
	}

	/**
	 * @return string|Field
	 */
	public function getColumn()
	{
		return $this->column;
	}

	/**
	 * @param string|Field|null $column
	 */
	public function setColumn($column)
	{
		$this->column = $column;
	}

	/**
	 * @return string
	 */
	public function getOperator()
	{
		return $this->operator;
	}

	/**
	 * @param string $operator
	 */
	public function setOperator($operator)
	{
		$this->operator = $operator;
	}

	/**
	 * @return mixed|ColumnExpression
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * @param mixed $value
	 */
	public function setValue($value)
	{
		$this->value = $value;
	}

	/**
	 * Returns value as a set of atomic (composite) values.
	 *
	 * In classic condition there is only one value v1.
	 * In whereIn there is an array [v1, v2, v3, ...].
	 * In whereBetween there are two values v1, v2.
	 * etc.
	 *
	 * This method returns array of all the values regardless the operator.
	 *
	 * @return array
	 */
	public function getAtomicValues()
	{
		if ($this->hasMultiValues())
		{
			return $this->value;
		}

		return array($this->value);
	}

	/**
	 * Checks for multi-values (array for IN etc.)
	 *
	 * @return bool
	 */
	public function hasMultiValues()
	{
		return in_array($this->operator, array('in', 'between'), true) && is_array($this->value);
	}

	/**
	 * @return string|Field|null
	 */
	public function getDefinition()
	{
		return $this->getColumn();
	}

	/**
	 * @param string|Field|null $definition
	 */
	public function setDefinition($definition)
	{
		$this->setColumn($definition);
	}

	public function __clone()
	{
		// clone value if there any filter expressions
		$newValues = array();

		foreach ($this->getAtomicValues() as $atomicValue)
		{
			$newValues[] =  ($atomicValue instanceof FilterExpression) ? clone $atomicValue : $atomicValue;
		}

		$this->value = $this->hasMultiValues() ? $newValues : $newValues[0];

		// clone field
		if ($this->column instanceof Field)
		{
			$this->column = clone $this->column;
		}
	}
}
