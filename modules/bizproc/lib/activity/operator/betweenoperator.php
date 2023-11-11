<?php

namespace Bitrix\Bizproc\Activity\Operator;

use Bitrix\Bizproc\FieldType;
use Bitrix\Main\Localization\Loc;

class BetweenOperator extends BaseOperator
{
	public static function getCode(): string
	{
		return 'between';
	}

	public static function getTitle(): string
	{
		return Loc::getMessage('BIZPROC_ACTIVITY_CONDITION_OPERATORS_BETWEEN_OPERATOR_TITLE') ?? '';
	}

	public function __construct($toCheck, $value, FieldType $fieldType)
	{
		parent::__construct($toCheck, $value, $fieldType);

		$this->toCheck = is_array($this->toCheck) ? $this->toCheck : [$this->toCheck];
		$this->value = is_array($this->value) ? $this->value : [$this->value];
	}

	public function check(): bool
	{
		$greaterThen = is_array($this->value[0]) ? $this->value[0] : [$this->value[0]];
		$lessThen = is_array($this->value[1]) ? $this->value[1] : [$this->value[1]];
		$toCheck = $this->toCheck;

		reset($greaterThen);
		reset($lessThen);
		reset($toCheck);

		$firstGreaterThen = current($greaterThen);
		$firstLessThen = current($lessThen);
		$checkValue = current($toCheck);

		return $this->compare($checkValue, [$firstGreaterThen, $firstLessThen]);
	}

	protected function compare($toCheck, $value): bool
	{
		$classType = $this->fieldType->getTypeClass();
		[$greaterThen, $lessThen] = $value;

		// $firstGreaterThen <= $checkValue <= $firstLessThen
		return (
			$classType::compareValues($toCheck, $greaterThen) >= 0
			&& $classType::compareValues($toCheck, $lessThen) <= 0
		);
	}
}
