<?php
namespace Bitrix\Bizproc\Automation\Engine;

use Bitrix\Bizproc\Automation\Target\BaseTarget;
use Bitrix\Bizproc\FieldType;

class Condition
{
	private $field;
	private $operator;
	private $value;

	public function __construct(array $params = null)
	{
		if ($params)
		{
			if (isset($params['field']))
			{
				$this->setField($params['field']);
			}
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
	 * @param string $field The field name.
	 * @return Condition
	 */
	public function setField($field)
	{
		$this->field = (string)$field;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getField()
	{
		return $this->field;
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
	 * @param mixed $needle The field value to check.
	 * @param string $fieldType Type of the field.
	 * @param BaseTarget $target Automation target.
	 * @param null|FieldType $fieldTypeObject
	 * @return bool
	 */
	public function check($needle, $fieldType, BaseTarget $target, FieldType $fieldTypeObject = null)
	{
		$operator = $this->getOperator();

		if ($operator === 'empty')
		{
			return \CBPHelper::isEmptyValue($needle);
		}
		elseif ($operator === '!empty')
		{
			return !\CBPHelper::isEmptyValue($needle);
		}

		$result = false;
		$value = $this->getValue();

		$documentId = $target->getDocumentType();
		$documentId[2] = $target->getDocumentId();

		if ($fieldType === 'user')
		{
			$needle = \CBPHelper::extractUsers($needle, $documentId);
			$value = \CBPHelper::extractUsers($value, $documentId);
		}
		elseif ($fieldType === 'select')
		{
			if (is_array($needle) && \CBPHelper::isAssociativeArray($needle))
			{
				$needle = array_keys($needle);
			}
		}

		if (!is_array($needle))
		{
			$needle = array($needle);
		}

		if ($operator === 'in')
		{
			foreach ($needle as $f)
			{
				if (is_array($value))
				{
					$result = in_array($f, $value);
				}
				else
				{
					$result = (strpos($value, $f) !== false);
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
				foreach ($needle as $f)
				{
					if (is_array($f))
					{
						$result = in_array($v, $f);
					}
					else
					{
						$result = (strpos($f, $v) !== false);
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
			$value = array($value);
		}

		if (\CBPHelper::isAssociativeArray($needle))
		{
			$needle = array_keys($needle);
		}
		if (\CBPHelper::isAssociativeArray($value))
		{
			$value = array_keys($value);
		}

		if (count($needle) === 0)
		{
			$needle = array(null);
		}

		if (count($value) === 0)
		{
			$value = array(null);
		}

		$i = 0;
		$fieldCount = count($needle);
		$valueCount = count($value);
		$iMax = max($fieldCount, $valueCount);
		while ($i < $iMax)
		{
			$f1 = ($fieldCount > $i) ? $needle[$i] : $needle[$fieldCount - 1];
			$v1 = ($valueCount > $i) ? $value[$i] : $value[$valueCount - 1];

			if ($fieldType === 'datetime' || $fieldType === 'date')
			{
				if (($f1Tmp = \MakeTimeStamp($f1, \FORMAT_DATETIME)) === false)
				{
					if (($f1Tmp = \MakeTimeStamp($f1, \FORMAT_DATE)) === false)
					{
						if (($f1Tmp = \MakeTimeStamp($f1, "YYYY-MM-DD HH:MI:SS")) === false)
						{
							if (($f1Tmp = \MakeTimeStamp($f1, "YYYY-MM-DD")) === false)
								$f1Tmp = 0;
						}
					}
				}
				$f1 = $f1Tmp;

				if (($v1Tmp = \MakeTimeStamp($v1, \FORMAT_DATETIME)) === false)
				{
					if (($v1Tmp = \MakeTimeStamp($v1, \FORMAT_DATE)) === false)
					{
						if (($v1Tmp = \MakeTimeStamp($v1, "YYYY-MM-DD HH:MI:SS")) === false)
						{
							if (($v1Tmp = \MakeTimeStamp($v1, "YYYY-MM-DD")) === false)
								$v1Tmp = 0;
						}
					}
				}
				$v1 = $v1Tmp;
			}

			if ($fieldType === 'bool')
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
			if ($fieldTypeObject)
			{
				$classType = $fieldTypeObject->getTypeClass();
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
			'field' => $this->getField(),
			'operator' => $this->getOperator(),
			'value' => $this->getValue(),
		);
	}
}