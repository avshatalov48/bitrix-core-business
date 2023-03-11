<?php

namespace Bitrix\Bizproc\Activity\Operator;

use Bitrix\Bizproc\FieldType;
use Bitrix\Main\Localization\Loc;

class NotEqualOperator extends BaseOperator
{
	public static function getCode(): string
	{
		return '!=';
	}

	public static function getTitle(): string
	{
		return Loc::getMessage('BIZPROC_ACTIVITY_CONDITION_OPERATORS_NOT_EQUAL_OPERATOR_TITLE') ?? '';
	}

	public function __construct($toCheck, $value, FieldType $fieldType)
	{
		parent::__construct($toCheck, $value, $fieldType);

		$this->toCheck = $this->valueToArray($this->toCheck);
		$this->value = $this->valueToArray($this->value);
	}

	public function check(): bool
	{
		$toCheck = $this->toCheck;
		$value = $this->value;

		$fieldCount = count($toCheck);
		$valueCount = count($value);
		for ($i = 0; $i < max($fieldCount, $valueCount); $i++)
		{
			$fieldI = ($fieldCount > $i) ? $toCheck[$i] : $toCheck[$fieldCount - 1];
			$valueI = ($valueCount > $i) ? $value[$i] : $value[$valueCount - 1];

			[$valueI, $fieldI] = static::normalizeZeroComparing($valueI, $fieldI);

			if ($this->compare($fieldI, $valueI))
			{
				return true;
			}
		}

		return false;
	}

	protected function compare($toCheck, $value): bool
	{
		$typeClass = $this->fieldType->getTypeClass();

		return $typeClass::compareValues($toCheck, $value) !== 0;
	}
}