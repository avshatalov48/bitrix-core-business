<?php

namespace Bitrix\Bizproc\Activity;

use Bitrix\Bizproc\Activity\Operator\BaseOperator;
use Bitrix\Bizproc\Activity\Operator\BetweenOperator;
use Bitrix\Bizproc\Activity\Operator\ContainOperator;
use Bitrix\Bizproc\Activity\Operator\EmptyOperator;
use Bitrix\Bizproc\Activity\Operator\EqualOperator;
use Bitrix\Bizproc\Activity\Operator\GreaterThenOperator;
use Bitrix\Bizproc\Activity\Operator\GreaterThenOrEqualOperator;
use Bitrix\Bizproc\Activity\Operator\InOperator;
use Bitrix\Bizproc\Activity\Operator\LessThenOperator;
use Bitrix\Bizproc\Activity\Operator\LessThenOrEqualOperator;
use Bitrix\Bizproc\Activity\Operator\NotContainOperator;
use Bitrix\Bizproc\Activity\Operator\NotEmptyOperator;
use Bitrix\Bizproc\Activity\Operator\NotEqualOperator;
use Bitrix\Bizproc\Activity\Operator\NotInOperator;
use Bitrix\Bizproc\FieldType;

class Condition
{
	protected $operator;
	protected $value;

	public function __construct(array $params = null)
	{
		if ($params)
		{
			if (isset($params['operator']))
			{
				$this->setOperator($params['operator']);
			}
			if (isset($params['value']))
			{
				$this->setValue($params['value']);
			}
		}
	}

	/**
	 * @param string $operator Operator like `=`, `<`, `>` etc.
	 * @return Condition
	 */
	public function setOperator($operator)
	{
		$this->operator = (string)$operator;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getOperator()
	{
		return $this->operator;
	}

	/**
	 * @param mixed $value Target condition value.
	 * @return Condition
	 */
	public function setValue($value)
	{
		$this->value = $value;
		return $this;
	}

	/**
	 * @return mixed
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * @param mixed $valueToCheck The field value to check.
	 * @param FieldType $fieldType
	 * @param array $documentId Document Id.
	 * @return bool
	 */
	public function checkValue($valueToCheck, FieldType $fieldType, array $documentId)
	{
		$fieldType = clone($fieldType);
		$fieldType->setDocumentId($documentId);

		switch ($this->getOperator())
		{
			case EmptyOperator::getCode():
				$operator = new EmptyOperator($valueToCheck, $this->getValue(), $fieldType);
				break;
			case NotEmptyOperator::getCode():
				$operator = new NotEmptyOperator($valueToCheck, $this->getValue(), $fieldType);
				break;
			case ContainOperator::getCode():
				$operator = new ContainOperator($valueToCheck, $this->getValue(), $fieldType);
				break;
			case NotContainOperator::getCode():
				$operator = new NotContainOperator($valueToCheck, $this->getValue(), $fieldType);
				break;
			case InOperator::getCode():
				$operator = new InOperator($valueToCheck, $this->getValue(), $fieldType);
				break;
			case NotInOperator::getCode():
				$operator = new NotInOperator($valueToCheck, $this->getValue(), $fieldType);
				break;
			case BetweenOperator::getCode():
				$operator = new BetweenOperator($valueToCheck, $this->getValue(), $fieldType);
				break;
			case GreaterThenOperator::getCode():
				$operator = new GreaterThenOperator($valueToCheck, $this->getValue(), $fieldType);
				break;
			case GreaterThenOrEqualOperator::getCode():
				$operator = new GreaterThenOrEqualOperator($valueToCheck, $this->getValue(), $fieldType);
				break;
			case LessThenOperator::getCode():
				$operator = new LessThenOperator($valueToCheck, $this->getValue(), $fieldType);
				break;
			case LessThenOrEqualOperator::getCode():
				$operator = new LessThenOrEqualOperator($valueToCheck, $this->getValue(), $fieldType);
				break;
			case EqualOperator::getCode():
				$operator = new EqualOperator($valueToCheck, $this->getValue(), $fieldType);
				break;
			case NotEqualOperator::getCode():
				$operator = new NotEqualOperator($valueToCheck, $this->getValue(), $fieldType);
				break;
			default:
				$operator = new BaseOperator($valueToCheck, $this->getValue(), $fieldType);
		}

		return $operator->check();
	}

	/**
	 * @return array Array presentation of condition.
	 */
	public function toArray()
	{
		return [
			'operator' => $this->getOperator(),
			'value' => $this->getValue(),
		];
	}

	public static function getOperatorList(): array
	{
		$operators = [
			Operator\EqualOperator::class,
			Operator\NotEqualOperator::class,

			Operator\GreaterThenOperator::class,
			Operator\GreaterThenOrEqualOperator::class,

			Operator\LessThenOperator::class,
			Operator\LessThenOrEqualOperator::class,

			Operator\InOperator::class,
			Operator\NotInOperator::class,

			Operator\ContainOperator::class,
			Operator\NotContainOperator::class,

			Operator\NotEmptyOperator::class,
			Operator\EmptyOperator::class,

			Operator\BetweenOperator::class,
		];

		$operatorList = [];

		/** @var $operator BaseOperator */
		foreach ($operators as $operator)
		{
			$operatorList[$operator::getCode()] = $operator::getTitle();
		}

		return $operatorList;
	}
}