<?php
namespace Bitrix\Bizproc\Activity;

use Bitrix\Bizproc\FieldType;
use Bitrix\Main\Localization\Loc;

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
		$operator = $this->getOperator();

		if ($operator === 'empty')
		{
			return \CBPHelper::isEmptyValue($valueToCheck);
		}
		elseif ($operator === '!empty')
		{
			return !\CBPHelper::isEmptyValue($valueToCheck);
		}

		$result = false;
		$value = $this->getValue();

		$baseType = $fieldType->getBaseType();

		if ($baseType === 'user')
		{
			$valueToCheck = \CBPHelper::extractUsers($valueToCheck, $documentId);
			$value = \CBPHelper::extractUsers($value, $documentId);
		}
		elseif ($baseType === 'select')
		{
			if (is_array($valueToCheck) && \CBPHelper::isAssociativeArray($valueToCheck))
			{
				$valueToCheck = array_keys($valueToCheck);
			}
		}

		if (!is_array($valueToCheck))
		{
			$valueToCheck = [$valueToCheck];
		}

		if ($operator === 'in' || $operator === '!in')
		{
			$result = $this->checkInOperation($valueToCheck, $value, $baseType);
			return $operator === 'in' ? $result : !$result;
		}

		if (!is_array($value))
		{
			$value = [$value];
		}

		if ($operator === 'contain' || $operator === '!contain')
		{
			$result = $this->checkContainOperation($valueToCheck, $value, $baseType);
			return $operator === 'contain' ? $result : !$result;
		}

		if (\CBPHelper::isAssociativeArray($valueToCheck))
		{
			$valueToCheck = array_keys($valueToCheck);
		}
		if (\CBPHelper::isAssociativeArray($value))
		{
			$value = array_keys($value);
		}

		if (count($valueToCheck) === 0)
		{
			$valueToCheck = [null];
		}

		if (count($value) === 0)
		{
			$value = [null];
		}

		$i = 0;
		$fieldCount = count($valueToCheck);
		$valueCount = count($value);
		$iMax = max($fieldCount, $valueCount);
		while ($i < $iMax)
		{
			$f1 = ($fieldCount > $i) ? $valueToCheck[$i] : $valueToCheck[$fieldCount - 1];
			$v1 = ($valueCount > $i) ? $value[$i] : $value[$valueCount - 1];

			if ($baseType === 'datetime' || $baseType === 'date')
			{
				$f1 = \CBPHelper::makeTimestamp($f1);
				$v1 = \CBPHelper::makeTimestamp($v1);
			}

			if ($baseType === 'bool')
			{
				$f1 = \CBPHelper::getBool($f1);
				$v1 = \CBPHelper::getBool($v1);
			}

			//normalize "0" == "" comparing
			if ($v1 === '' && $f1 === '0' || $f1 === '' && $v1 === '0')
			{
				$f1 = $v1 = null;
			}

			/** @var \Bitrix\Bizproc\BaseType\Base $classType */
			$classType = \Bitrix\Bizproc\BaseType\Base::class;
			if ($fieldType)
			{
				$classType = $fieldType->getTypeClass();
			}
			$compareResult = $classType::compareValues($f1, $v1);

			switch ($operator)
			{
				case '>':
					$result = ($compareResult === 1);
					break;
				case '>=':
					$result = ($compareResult >= 0);
					break;
				case '<':
					$result = ($compareResult === -1);
					break;
				case '<=':
					$result = ($compareResult <= 0);
					break;
				case '!=':
					$result = ($compareResult !== 0);
					break;
				default:
					$result = ($compareResult === 0);
			}

			if (!$result)
			{
				break;
			}

			$i++;
		}

		return $result;
	}

	private function checkInOperation($toCheck, $base, $baseType): bool
	{
		$result = false;

		foreach ($toCheck as $f)
		{
			if (is_array($base))
			{
				$result = in_array($f, $base);
			}
			else
			{
				$result = (mb_strpos($base, $f) !== false);
			}

			if (!$result)
			{
				break;
			}
		}

		return $result;
	}

	private function checkContainOperation($toCheck, $base, $baseType): bool
	{
		$result = false;

		if ($baseType === 'user')
		{
			return count(array_diff($base, $toCheck)) === 0;
		}

		foreach ($base as $v)
		{
			foreach ($toCheck as $f)
			{
				if (is_array($f))
				{
					$result = in_array($v, $f);
				}
				else
				{
					$result = (mb_strpos($f, $v) !== false);
				}

				if ($result)
				{
					break;
				}
			}

			if (!$result)
			{
				break;
			}
		}

		return $result;
	}

	/**
	 * @return array Array presentation of condition.
	 */
	public function toArray()
	{
		return array(
			'operator' => $this->getOperator(),
			'value' => $this->getValue(),
		);
	}

	public static function getOperatorList(): array
	{
		return [
			"=" => Loc::getMessage("BIZPROC_ACTIVITY_CONDITION_OPERATOR_EQ"),
			"!=" => Loc::getMessage("BIZPROC_ACTIVITY_CONDITION_OPERATOR_NE"),

			">" => Loc::getMessage("BIZPROC_ACTIVITY_CONDITION_OPERATOR_GT"),
			">=" => Loc::getMessage("BIZPROC_ACTIVITY_CONDITION_OPERATOR_GE"),

			"<" => Loc::getMessage("BIZPROC_ACTIVITY_CONDITION_OPERATOR_LT"),
			"<=" => Loc::getMessage("BIZPROC_ACTIVITY_CONDITION_OPERATOR_LE"),

			"in" => Loc::getMessage("BIZPROC_ACTIVITY_CONDITION_OPERATOR_IN"),
			"!in" => Loc::getMessage("BIZPROC_ACTIVITY_CONDITION_OPERATOR_NOT_IN"),

			"contain" => Loc::getMessage("BIZPROC_ACTIVITY_CONDITION_OPERATOR_CONTAIN"),
			"!contain" => Loc::getMessage("BIZPROC_ACTIVITY_CONDITION_OPERATOR_NOT_CONTAIN"),

			"!empty" => Loc::getMessage("BIZPROC_ACTIVITY_CONDITION_OPERATOR_NOT_EMPTY"),
			"empty" => Loc::getMessage("BIZPROC_ACTIVITY_CONDITION_OPERATOR_EMPTY"),
		];
	}
}