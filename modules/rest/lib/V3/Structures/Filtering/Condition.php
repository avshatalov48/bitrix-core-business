<?php

namespace Bitrix\Rest\V3\Structures\Filtering;

use Bitrix\Rest\V3\Exceptions\InvalidFilterException;
use Bitrix\Rest\V3\Structures\Filtering\Expressions\Factory;

class Condition
{
	protected mixed $leftOperand;

	protected mixed $rightOperand;

	/**
	 * @param $left
	 * @param Operator $operator
	 * @param $right
	 * @throws InvalidFilterException
	 */
	public function __construct($left, protected Operator $operator, $right)
	{
		$operands = ['left' => $left, 'right' => $right];

		foreach ($operands as $k => $operand)
		{
			if (is_array($operand) && isset($operand['expression']))
			{
				$expression = Factory::createFromArray($operand);

				$operands[$k] = $expression;
			}
		}

		$this->leftOperand = $operands['left'];
		$this->rightOperand = $operands['right'];
	}

	/**
	 * @return mixed
	 */
	public function getLeftOperand(): mixed
	{
		return $this->leftOperand;
	}

	/**
	 * @return Operator
	 */
	public function getOperator(): Operator
	{
		return $this->operator;
	}

	/**
	 * @return mixed
	 */
	public function getRightOperand(): mixed
	{
		return $this->rightOperand;
	}

	public function getOperands(): array
	{
		return [$this->leftOperand, $this->rightOperand];
	}
}
