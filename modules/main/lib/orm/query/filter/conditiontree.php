<?php
/**
 * Bitrix Framework
 * @package    bitrix
 * @subpackage main
 * @copyright  2001-2016 Bitrix
 */

namespace Bitrix\Main\ORM\Query\Filter;

use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\ORM\Query\Chain;
use Bitrix\Main\ORM\Fields\IReadable;
use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\SystemException;

/**
 * Handles filtering conditions for Query and join conditions for Entity References.
 * @package bitrix
 * @subpackage main
 */
class ConditionTree
{
	/** @var Chain[] */
	protected $chains;

	/** @var Condition[]|ConditionTree[] */
	protected $conditions = array();

	/**
	 * @var string and|or
	 * @see ConditionTree::logic()
	 */
	protected $logic;

	const LOGIC_OR = 'or';

	const LOGIC_AND = 'and';

	/**
	 * Whether to set NOT before all the conditions.
	 * @var bool
	 */
	protected $isNegative = false;

	/**
	 * ConditionTree constructor.
	 */
	public function __construct()
	{
		$this->logic = static::LOGIC_AND;
	}

	/**
	 * All conditions will be imploded by this logic: static::LOGIC_AND or static::LOGIC_OR
	 *
	 * @param string $logic and|or
	 *
	 * @return $this|string
	 * @throws ArgumentException
	 */
	public function logic($logic = null)
	{
		if ($logic === null)
		{
			return $this->logic;
		}

		if (!in_array(strtolower($logic), [static::LOGIC_AND, static::LOGIC_OR], true))
		{
			throw new ArgumentException("Unknown logic");
		}

		$this->logic = strtolower($logic);

		return $this;
	}

	/**
	 * Sets NOT before all the conditions.
	 *
	 * @param bool $negative
	 *
	 * @return $this
	 */
	public function negative($negative = true)
	{
		$this->isNegative = $negative;
		return $this;
	}

	/**
	 * General condition. In regular case used with 3 parameters:
	 *   where(columnName, operator, value), e.g. ('ID', '=', 1); ('SALARY', '>', '500')
	 *
	 * List of available operators can be found in Operator class.
	 * @see Operator::$operators
	 *
	 * Can be used in short format:
	 *   where(columnName, value), with operator '=' by default
	 * Can be used in ultra short format:
	 *   where(columnName), for boolean fields only
	 *
	 * Can be used for subfilter set:
	 *   where(ConditionTree subfilter)
	 *
	 * Instead of columnName, you can use runtime field:
	 *   where(new ExpressionField('TMP', 'CONCAT(%s, %s)', ["NAME", "LAST_NAME"]), 'Anton Ivanov')
	 *     or with expr helper
	 *   where(Query::expr()->concat("NAME", "LAST_NAME"), 'Anton Ivanov')
	 *
	 * @param array ...$filter
	 *
	 * @return $this
	 * @throws ArgumentException
	 */
	public function where()
	{
		$filter = func_get_args();

		// subfilter
		if (count($filter) == 1 && $filter[0] instanceof ConditionTree)
		{
			$this->conditions[] = $filter[0];
			return $this;
		}

		// ready condition
		if (count($filter) == 1 && $filter[0] instanceof Condition)
		{
			$this->conditions[] = $filter[0];
			return $this;
		}

		// array of conditions
		if (count($filter) == 1 && is_array($filter[0]))
		{
			foreach ($filter[0] as $condition)
			{
				// call `where` for each condition
				call_user_func_array(array($this, 'where'), $condition);
			}
			return $this;
		}

		// regular conditions
		if (count($filter) == 3)
		{
			// everything is clear
			list($column, $operator, $value) = $filter;
		}
		elseif (count($filter) == 2)
		{
			// equal by default
			list($column, $value) = $filter;
			$operator = '=';
		}
		elseif (count($filter) == 1)
		{
			// suppose it is boolean field with true value
			$column = $filter[0];
			$operator = '=';
			$value = true;
		}
		else
		{
			throw new ArgumentException('Wrong arguments');
		}

		// validate operator
		$operators = Operator::get();
		if (!isset($operators[$operator]))
		{
			throw new ArgumentException("Unknown operator `{$operator}`");
		}

		// add condition
		$this->conditions[] = new Condition($column, $operator, $value);

		return $this;
	}

	/**
	 * Sets NOT before any conditions or subfilter.
	 * @see ConditionTree::where()
	 *
	 * @param array ...$filter
	 *
	 * @return $this
	 */
	public function whereNot()
	{
		$filter = func_get_args();

		$subFilter = new static();
		call_user_func_array(array($subFilter, 'where'), $filter);

		$this->conditions[] = $subFilter->negative();
		return $this;
	}

	/**
	 * The same logic as where(), but value will be taken as another column name.
	 * @see ConditionTree::where()
	 *
	 * @param array ...$filter
	 *
	 * @return $this
	 * @throws ArgumentException
	 */
	public function whereColumn()
	{
		$filter = func_get_args();

		if (count($filter) == 3)
		{
			list($column, $operator, $value) = $filter;
		}
		elseif (count($filter) == 2)
		{
			list($column, $value) = $filter;
			$operator = '=';
		}
		else
		{
			throw new ArgumentException('Wrong arguments');
		}

		// convert value to column format
		$value = new Expressions\ColumnExpression($value);

		// put through general method
		$this->where($column, $operator, $value);

		return $this;
	}

	/**
	 * Compares column with NULL.
	 *
	 * @param string $column
	 *
	 * @return $this
	 */
	public function whereNull($column)
	{
		$this->conditions[] = new Condition($column, '=', null);

		return $this;
	}

	/**
	 * Compares column with NOT NULL.
	 *
	 * @param string $column
	 *
	 * @return $this
	 */
	public function whereNotNull($column)
	{
		$this->conditions[] = new Condition($column, '<>', null);

		return $this;
	}

	/**
	 * IN() condition.
	 *
	 * @param string                    $column
	 * @param array|Query|SqlExpression $values
	 *
	 * @return $this
	 */
	public function whereIn($column, $values)
	{
		$this->conditions[] = new Condition($column, 'in', $values);

		return $this;
	}

	/**
	 * Negative IN() condition.
	 * @see ConditionTree::whereIn()
	 *
	 * @param string                    $column
	 * @param array|Query|SqlExpression $values
	 *
	 * @return $this
	 */
	public function whereNotIn($column, $values)
	{
		$subFilter = new static();
		$this->conditions[] = $subFilter->whereIn($column, $values)->negative();

		return $this;
	}

	/**
	 * BETWEEN condition.
	 *
	 * @param $column
	 * @param $valueMin
	 * @param $valueMax
	 *
	 * @return $this
	 */
	public function whereBetween($column, $valueMin, $valueMax)
	{
		$this->conditions[] = new Condition($column, 'between', array($valueMin, $valueMax));

		return $this;
	}

	/**
	 * Negative BETWEEN condition.
	 * @see ConditionTree::whereBetween()
	 *
	 * @param $column
	 * @param $valueMin
	 * @param $valueMax
	 *
	 * @return $this
	 */
	public function whereNotBetween($column, $valueMin, $valueMax)
	{
		$subFilter = new static();
		$this->conditions[] = $subFilter->whereBetween($column, $valueMin, $valueMax)->negative();

		return $this;
	}

	/**
	 * LIKE condition, without default % placement.
	 *
	 * @param $column
	 * @param $value
	 *
	 * @return $this
	 */
	public function whereLike($column, $value)
	{
		$this->conditions[] = new Condition($column, 'like', $value);

		return $this;
	}

	/**
	 * Negative LIKE condition, without default % placement.
	 * @see ConditionTree::whereLike()
	 *
	 * @param $column
	 * @param $value
	 *
	 * @return $this
	 */
	public function whereNotLike($column, $value)
	{
		$subFilter = new static();
		$this->conditions[] = $subFilter->whereLike($column, $value)->negative();

		return $this;
	}

	/**
	 * Exists() condition. Can be used with Query object or plain sql wrapped with SqlExpression.
	 *
	 * @param Query|SqlExpression $query
	 *
	 * @return $this
	 */
	public function whereExists($query)
	{
		$this->conditions[] = new Condition(null, 'exists', $query);

		return $this;
	}

	/**
	 * Negative Exists() condition. Can be used with Query object or plain sql wrapped with SqlExpression.
	 * @see ConditionTree::whereExists()
	 *
	 * @param Query|SqlExpression $query
	 *
	 * @return $this
	 * @throws ArgumentException
	 */
	public function whereNotExists($query)
	{
		if ($query instanceof Query || $query instanceof SqlExpression)
		{
			$subFilter = new static();
			$this->conditions[] = $subFilter->whereExists($query)->negative();

			return $this;
		}

		throw new ArgumentException('Unknown type of query '.gettype($query));
	}

	/**
	 * Fulltext search condition.
	 * @see Helper::matchAgainstWildcard() for preparing $value for AGAINST.
	 *
	 * @param $column
	 * @param $value
	 *
	 * @return $this
	 */
	public function whereMatch($column, $value)
	{
		$this->conditions[] = new Condition($column, 'match', $value);

		return $this;
	}

	/**
	 * Negative fulltext search condition.
	 * @see Helper::matchAgainstWildcard() for preparing $value for AGAINST.
	 *
	 * @param $column
	 * @param $value
	 *
	 * @return $this
	 */
	public function whereNotMatch($column, $value)
	{
		$subFilter = new static();
		$this->conditions[] = $subFilter->whereMatch($column, $value)->negative();

		return $this;
	}

	/**
	 * Returns SQL for all conditions and subfilters.
	 *
	 * @param Chain[] $chains
	 *
	 * @return string
	 * @throws ArgumentException
	 * @throws SystemException
	 */
	public function getSql($chains)
	{
		// save chains
		$this->chains = $chains;

		$finalSql = array();

		// build sql
		foreach ($this->conditions as $condition)
		{
			if ($condition instanceof ConditionTree)
			{
				// subfilter
				$subFilter = $condition;
				$sql = $subFilter->getSql($chains);

				if (count($subFilter->getConditions()) > 1)
				{
					$sql = "({$sql})";
				}
			}
			else
			{
				// regular condition
				$columnSqlDefinition = null;
				$columnField = null;

				// define column field
				if ($condition->getColumn() !== null)
				{
					$chain = $chains[$condition->getDefinition()];
					$columnSqlDefinition = $chain->getSqlDefinition();

					/** @var IReadable $columnField */
					$columnField = $chain->getLastElement()->getValue();
				}

				// final value's sql
				if (in_array($condition->getOperator(), array('in', 'between'), true) && is_array($condition->getValue()))
				{
					// value is in array of atomic values
					$finalValue = $this->convertValues($condition->getValue(), $columnField);
				}
				else
				{
					$finalValue = $this->convertValue($condition->getValue(), $columnField);
				}

				// operation method
				$operators = Operator::get();
				$operator = $operators[$condition->getOperator()];

				// final sql
				$sql = call_user_func(
					array('Bitrix\Main\ORM\Query\Filter\Operator', $operator),
					$columnSqlDefinition, $finalValue
				);
			}

			$finalSql[] = $sql;
		}

		// concat with $this->logic
		$sql = join(" ".strtoupper($this->logic)." ", $finalSql);

		// and put NOT if negative
		if ($this->isNegative)
		{
			$sql = count($finalSql) > 1 ? "NOT ({$sql})" : "NOT {$sql}";
		}

		return $sql;
	}

	/**
	 * Returns all conditions and subfilters.
	 *
	 * @return ConditionTree[]|Condition[]
	 */
	public function getConditions()
	{
		return $this->conditions;
	}

	/**
	 * Adds prepared condition.
	 *
	 * @param Condition|ConditionTree $condition
	 *
	 * @return $this
	 * @throws ArgumentException
	 */
	public function addCondition($condition)
	{
		if ($condition instanceof Condition || $condition instanceof ConditionTree)
		{
			$this->conditions[] = $condition;
			return $this;
		}

		throw new ArgumentException('Unknown type of condition '.gettype($condition));
	}

	/**
	 * Checks if filter is not empty.
	 *
	 * @return bool
	 */
	public function hasConditions()
	{
		return !empty($this->conditions);
	}

	/**
	 * Replaces condition with a new one.
	 *
	 * @param $currentCondition
	 * @param $newCondition
	 *
	 * @return bool
	 */
	public function replaceCondition($currentCondition, $newCondition)
	{
		foreach ($this->conditions as $k => $condition)
		{
			if ($condition === $currentCondition)
			{
				$this->conditions[$k] = $newCondition;
				return true;
			}
		}

		return false;
	}

	/**
	 * Removes one condition
	 *
	 * @param $condition
	 *
	 * @return bool
	 */
	public function removeCondition($condition)
	{
		foreach ($this->conditions as $k => $_condition)
		{
			if ($condition === $_condition)
			{
				unset($this->conditions[$k]);
				return true;
			}
		}

		return false;
	}

	/**
	 * Removes all conditions
	 */
	public function removeAllConditions()
	{
		$this->conditions = [];
	}

	/**
	 * Converts any value to raw SQL, except of NULL, which is supposed to be handled in Operator.
	 *
	 * @param mixed     $value
	 * @param IReadable $field
	 *
	 * @return mixed|null|string
	 * @throws ArgumentException
	 * @throws SystemException
	 */
	protected function convertValue($value, IReadable $field = null)
	{
		// any sql expression
		if ($value instanceof SqlExpression)
		{
			return $value->compile();
		}

		// subquery
		if ($value instanceof Query)
		{
			return $value->getQuery();
		}

		// subfilter
		if ($value instanceof ConditionTree)
		{
			return $value->getSql($this->chains);
		}

		// nulls
		if ($value === null)
		{
			return new Expressions\NullExpression;
		}

		if ($value instanceof Expressions\ColumnExpression)
		{
			/** @var Chain $valueChain */
			$valueChain = $this->chains[$value->getDefinition()];
			return $valueChain->getSqlDefinition();
		}

		return $field->convertValueToDb($value); // give them current sql helper
	}

	/**
	 * Converts array of values to raw SQL.
	 * @see ConditionTree::convertValue()
	 *
	 * @param array                                  $values
	 * @param \Bitrix\Main\ORM\Fields\IReadable|null $field
	 *
	 * @return array
	 * @throws ArgumentException
	 * @throws SystemException
	 */
	protected function convertValues($values, IReadable $field = null)
	{
		foreach ($values as $k => $value)
		{
			$values[$k] = $this->convertValue($value, $field);
		}

		return $values;
	}

	public function __clone()
	{
		$newConditions = array();

		foreach ($this->conditions as $condition)
		{
			$newConditions[] = clone $condition;
		}

		$this->conditions = $newConditions;
	}
}
