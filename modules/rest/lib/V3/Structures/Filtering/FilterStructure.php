<?php

namespace Bitrix\Rest\V3\Structures\Filtering;

use Bitrix\Rest\V3\Exceptions\InvalidFilterException;
use Bitrix\Rest\V3\Exceptions\UnknownDtoPropertyException;
use Bitrix\Rest\V3\Exceptions\UnknownFilterOperatorException;
use Bitrix\Rest\V3\Exceptions\Validation\DtoFieldRequiredAttributeException;
use Bitrix\Rest\V3\Interaction\Request\Request;
use Bitrix\Rest\V3\Structures\FieldsConverter;
use Bitrix\Rest\V3\Structures\Filtering\Expressions\Expression;
use Bitrix\Rest\V3\Structures\Structure;

final class FilterStructure extends Structure
{
	/** @var Condition[]|static[] */
	protected array $conditions = [];

	protected array $rawData = [];

	/** @var Logic All 0-level conditions will be imploded by this logic */
	protected Logic $logic = Logic::And;

	/** @var bool Whether to set NOT before all the conditions */
	protected bool $isNegative = false;

	protected \StringBackedEnum $operator;

	public static function create(mixed $value, string $dtoClass, Request $request): self
	{
		$value = (array)$value;

		$reflection = new \ReflectionClass($dtoClass);

		return self::fillStructure($value, $reflection);
	}

	/**
	 * Sets logic for conditions imploding and returns self
	 * Returns current logic when no argument passed
	 *
	 * @param ?Logic $logic
	 *
	 * @return $this|Logic
	 */
	public function logic(Logic $logic = null): static|Logic
	{
		if ($logic === null)
		{
			return $this->logic;
		}

		$this->logic = $logic;

		return $this;
	}

	/**
	 * Sets NOT before all the conditions.
	 *
	 * @param bool $negative
	 *
	 * @return $this
	 */
	public function negative(bool $negative = true): static
	{
		$this->isNegative = $negative;

		return $this;
	}

	public function isNegative(): bool
	{
		return $this->isNegative;
	}

	/**
	 * General condition. In regular case used with 3 parameters:
	 *   where(columnName, operator, value), e.g. ('id', '=', 1); ('salary', '>', '500')
	 *
	 * List of available operators can be found in Operator class.
	 * @param mixed ...$filter
	 *
	 * @return $this
	 *
	 * @throws InvalidFilterException
	 * @throws UnknownFilterOperatorException
	 * @see Operator::$operators
	 *
	 * Can be used in short format:
	 *   where(columnName, value), with operator '=' by default
	 * Can be used in ultra short format:
	 *   where(columnName), for boolean fields only
	 *
	 * Can be used for subfilter set:
	 *   where(Filter subfilter)
	 *
	 * Instead of columnName, you can use runtime field:
	 *   where(new ExpressionField('TMP', 'CONCAT(%s, %s)', ["NAME", "LAST_NAME"]), 'Anton Ivanov')
	 *     or with expr helper
	 *   where(Query::expr()->concat("NAME", "LAST_NAME"), 'Anton Ivanov')
	 */
	public function where(...$filter): static
	{
		$this->conditions[] = match (count($filter))
		{
			1 => $this->handleSingleFilter($filter[0]),
			2 => $this->createCondition($filter[0], is_array($filter[1]) ? Operator::In : Operator::Equal, $filter[1]),
			3 => $this->createCondition($filter[0], $filter[1], $filter[2]),
			default => throw new InvalidFilterException($filter),
		};

		return $this;
	}

	/**
	 * @throws InvalidFilterException
	 * @throws UnknownFilterOperatorException
	 */
	private function handleSingleFilter(mixed $filter): Condition|array
	{
		return match (true)
		{
			$filter instanceof static, $filter instanceof Condition => $filter,
			is_array($filter) => $this->handleArrayFilter($filter),
			default => $this->createCondition($filter, Operator::Equal, true),
		};
	}

	/**
	 * @throws InvalidFilterException
	 * @throws UnknownFilterOperatorException
	 */
	private function handleArrayFilter(array $filter): array
	{
		$conditions = [];
		foreach ($filter as $condition)
		{
			$conditions[] = $this->where(...$condition);
		}

		return $conditions;
	}

	/**
	 * @throws UnknownFilterOperatorException
	 * @throws InvalidFilterException
	 */
	private function createCondition(mixed $column, Operator|string $operator, mixed $value): Condition
	{
		$operator = $operator instanceof Operator ? $operator : FilterValidator::validateOperator($operator);

		return new Condition($column, $operator, $value);
	}

	/**
	 * Sets NOT before any conditions or subfilter.
	 * @param mixed ...$filter
	 *
	 * @return $this
	 * @see static::where()
	 */
	public function whereNot(...$filter): static
	{
		$subFilter = new static();
		call_user_func_array([$subFilter, 'where'], $filter);

		$this->conditions[] = $subFilter->negative();

		return $this;
	}

	/**
	 * The same logic as where(), but value will be taken as another column name.
	 * @param mixed ...$filter
	 *
	 * @return $this
	 * @throws InvalidFilterException
	 * @throws UnknownFilterOperatorException
	 * @see ConditionTree::where()
	 */
	public function whereColumn(...$filter): static
	{
		if (count($filter) === 3)
		{
			[$column, $operator, $value] = $filter;
		}
		elseif (count($filter) === 2)
		{
			[$column, $value] = $filter;
			$operator = Operator::Equal;
		}
		else
		{
			throw new InvalidFilterException($filter);
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
	public function whereNull(string $column): static
	{
		$this->conditions[] = new Condition($column, Operator::Equal, null);

		return $this;
	}

	/**
	 * Compares column with NOT NULL.
	 *
	 * @param string $column
	 *
	 * @return $this
	 */
	public function whereNotNull(string $column): static
	{
		$this->conditions[] = new Condition($column, Operator::NotEqual, null);

		return $this;
	}

	/**
	 * IN() condition.
	 *
	 * @param string $column
	 * @param array $values
	 *
	 * @return $this
	 */
	public function whereIn(string $column, array $values): static
	{
		if (!empty($values))
		{
			$this->conditions[] = new Condition($column, Operator::In, $values);
		}

		return $this;
	}

	/**
	 * Negative IN() condition.
	 * @param string $column
	 * @param array $values
	 *
	 * @return $this
	 * @see ConditionTree::whereIn()
	 */
	public function whereNotIn(string $column, array $values): static
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
	public function whereBetween($column, $valueMin, $valueMax): static
	{
		$this->conditions[] = new Condition($column, Operator::Between, [$valueMin, $valueMax]);

		return $this;
	}

	/**
	 * Negative BETWEEN condition.
	 * @param $column
	 * @param $valueMin
	 * @param $valueMax
	 *
	 * @return $this
	 * @see ConditionTree::whereBetween()
	 */
	public function whereNotBetween($column, $valueMin, $valueMax): static
	{
		$subFilter = new static();
		$this->conditions[] = $subFilter->whereBetween($column, $valueMin, $valueMax)->negative();

		return $this;
	}

	/**
	 * Returns all conditions and subfilters.
	 *
	 * @return static[]|Condition[]
	 */
	public function getConditions(): array
	{
		return $this->conditions;
	}

	/**
	 * Adds prepared condition.
	 *
	 * @param Condition|static $condition
	 *
	 * @return $this
	 */
	public function addCondition(Condition|self $condition): static
	{
		$this->conditions[] = $condition;

		return $this;
	}

	/**
	 * @throws DtoFieldRequiredAttributeException
	 * @throws InvalidFilterException
	 * @throws UnknownDtoPropertyException
	 * @throws UnknownFilterOperatorException
	 * @throws \ReflectionException
	 */
	protected static function fillStructure(array $filterItem, \ReflectionClass $dtoReflection, ?self $structure = null): self
	{
		$structure ??= new self();
		$structure->rawData = $filterItem;

		if (isset($filterItem['logic']))
		{
			try
			{
				$structure->logic(Logic::from($filterItem['logic']));
			}
			catch (\Throwable)
			{
				throw new InvalidFilterException(['logic' => $filterItem['logic']]);
			}
		}

		if (!isset($filterItem['type']) && !isset($filterItem['logic']) && !isset($filterItem['conditions']) && !isset($filterItem['negative']))
		{
			return self::handleSimpleCondition($filterItem, $dtoReflection, $structure);
		}

		if (isset($filterItem['negative']))
		{
			if (!is_bool($filterItem['negative']))
			{
				throw new InvalidFilterException(['negative' => $filterItem['negative']]);
			}
			$structure->negative($filterItem['negative']);
		}

		if (!isset($filterItem['conditions']))
		{
			throw new InvalidFilterException($filterItem);
		}

		foreach ($filterItem['conditions'] as $conditionItem)
		{
			if (!isset($conditionItem['type']) && !isset($conditionItem['logic']) && !isset($conditionItem['conditions']) && !isset($conditionItem['negative']))
			{
				$newItem = static::fillStructure($conditionItem, $dtoReflection);
				$structure->addCondition($newItem);

				continue;
			}

			if (!isset($conditionItem['type']))
			{
				throw new InvalidFilterException($conditionItem);
			}

			$newItem = match ($conditionItem['type'])
			{
				'filter' => static::fillStructure($conditionItem, $dtoReflection),
				'condition' => self::createStaticCondition($conditionItem, $dtoReflection),
				default => throw new InvalidFilterException($conditionItem),
			};

			$structure->addCondition($newItem);
		}

		return $structure;
	}

	/**
	 * @throws UnknownDtoPropertyException
	 * @throws DtoFieldRequiredAttributeException
	 * @throws UnknownFilterOperatorException
	 * @throws InvalidFilterException
	 * @throws \ReflectionException
	 */
	private static function handleSimpleCondition(array $filterItem, \ReflectionClass $dtoReflection, self $structure): static
	{
		if (is_array($filterItem[0]) && !isset($filterItem[0]['expression']))
		{
			return self::handleArrayCondition($filterItem, $dtoReflection, $structure);
		}

		$condition = match (count($filterItem))
		{
			2 => self::createConditionWithOperatorAndValue($dtoReflection, $filterItem[0], is_array($filterItem[1]) ? Operator::In : Operator::Equal, $filterItem[1]),
			3 => self::createConditionWithOperatorAndValue($dtoReflection, $filterItem[0], $filterItem[1], $filterItem[2]),
			default => throw new InvalidFilterException('Unknown filter condition'),
		};

		FilterValidator::validateOperands($condition, $dtoReflection);
		$structure->addCondition($condition);

		return $structure;
	}

	/**
	 * @throws DtoFieldRequiredAttributeException
	 * @throws InvalidFilterException
	 * @throws UnknownDtoPropertyException
	 * @throws UnknownFilterOperatorException
	 * @throws \ReflectionException
	 */
	private static function handleArrayCondition(array $filterItem, \ReflectionClass $dtoReflection, self $structure): static
	{
		foreach ($filterItem as $item)
		{
			$newItem = static::fillStructure($item, $dtoReflection);
			$structure->addCondition($newItem);
		}

		return $structure;
	}

	/**
	 * @throws InvalidFilterException
	 * @throws UnknownFilterOperatorException
	 */
	private static function createConditionWithOperatorAndValue(\ReflectionClass $dtoReflection, mixed $field, Operator|string $operator, mixed $value): Condition
	{
		$operator = $operator instanceof Operator ? $operator : FilterValidator::validateOperator($operator);

		$fieldType = is_scalar($field) && $dtoReflection->hasProperty($field) ? $dtoReflection->getProperty($field)->getType()->getName() : null;

		if (is_array($value))
		{
			foreach ($value as &$valueItem)
			{
				$valueItem = FieldsConverter::convertValueByType($fieldType, $valueItem);
			}
		}
		else
		{
			$value = FieldsConverter::convertValueByType($fieldType, $value);
		}

		return new Condition($field, $operator, $value);
	}

	/**
	 * @throws DtoFieldRequiredAttributeException
	 * @throws UnknownDtoPropertyException
	 * @throws UnknownFilterOperatorException
	 * @throws \ReflectionException
	 * @throws InvalidFilterException
	 */
	private static function createStaticCondition(array $conditionItem, \ReflectionClass $dtoReflection): Condition
	{
		FilterValidator::validateOperator($conditionItem['operator']);
		$newItem = new Condition($conditionItem['leftOperand'], $conditionItem['operator'], $conditionItem['rightOperand']);
		FilterValidator::validateOperands($newItem, $dtoReflection);

		return $newItem;
	}

	public function getList(): array
	{
		return $this->rawData;
	}

	public function getFields(): array
	{
		$fields = [];
		foreach ($this->conditions as $condition)
		{
			if (!$condition instanceof Expression)
			{
				$fields[] = $condition->getLeftOperand();
			}
		}

		return $fields;
	}
}
