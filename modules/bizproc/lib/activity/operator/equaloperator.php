<?php

namespace Bitrix\Bizproc\Activity\Operator;

use Bitrix\Bizproc\FieldType;
use Bitrix\Main\Localization\Loc;

class EqualOperator extends BaseOperator
{
	public static function getCode(): string
	{
		return '=';
	}

	public static function getTitle(): string
	{
		return Loc::getMessage('BIZPROC_ACTIVITY_CONDITION_OPERATORS_EQUAL_OPERATOR_TITLE') ?? '';
	}

	protected function compare($toCheck, $value): bool
	{
		$typeClass = $this->fieldType->getTypeClass();

		return $typeClass::compareValues($toCheck, $value) === 0;
	}
}
