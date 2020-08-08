<?php
namespace Bitrix\Bizproc\Activity;

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

		if ($operator === 'in')
		{
			foreach ($valueToCheck as $f)
			{
				if (is_array($value))
				{
					$result = in_array($f, $value);
				}
				else
				{
					$result = (mb_strpos($value, $f) !== false);
				}

				if (!$result)
				{
					break;
				}
			}

			return $result;
		}

		if ($operator === 'contain')
		{
			if (!is_array($value))
			{
				$value = array($value);
			}
			foreach ($value as $v)
			{
				foreach ($valueToCheck as $f)
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

		if (!is_array($value))
		{
			$value = [$value];
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
}