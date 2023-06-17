<?php

namespace Bitrix\Bizproc\Activity\Operator;

use Bitrix\Bizproc\FieldType;
use Bitrix\Main\Localization\Loc;

class InOperator extends BaseOperator
{
	public static function getCode(): string
	{
		return 'in';
	}

	public static function getTitle(): string
	{
		return Loc::getMessage('BIZPROC_ACTIVITY_CONDITION_OPERATORS_IN_OPERATOR_TITLE') ?? '';
	}

	public function __construct($toCheck, $value, FieldType $fieldType)
	{
		parent::__construct($toCheck, $value, $fieldType);

		$this->toCheck = is_array($this->toCheck) ? $this->toCheck : [$this->toCheck];
	}

	public function check(): bool
	{
		$toCheck = $this->toCheck;
		$value = $this->value;

		$result = false;
		foreach (\CBPHelper::flatten($toCheck) as $f)
		{
			if (is_array($value))
			{
				$result = in_array($f, $value, false);
			}
			elseif (
				\CBPHelper::hasStringRepresentation($value)
				&& \CBPHelper::hasStringRepresentation($f)
				&& (string)$f !== ''
			)
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
}