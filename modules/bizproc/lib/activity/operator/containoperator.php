<?php

namespace Bitrix\Bizproc\Activity\Operator;

use Bitrix\Bizproc\FieldType;
use Bitrix\Main\Localization\Loc;

class ContainOperator extends BaseOperator
{
	public static function getCode(): string
	{
		return 'contain';
	}

	public static function getTitle(): string
	{
		return Loc::getMessage('BIZPROC_ACTIVITY_CONDITION_OPERATORS_CONTAIN_OPERATOR_TITLE') ?? '';
	}

	public function __construct($toCheck, $value, FieldType $fieldType)
	{
		parent::__construct($toCheck, $value, $fieldType);

		$this->value = is_array($this->value) ? $this->value : [$this->value];
		$this->toCheck = is_array($this->toCheck) ? $this->toCheck : [$this->toCheck];
	}

	public function check(): bool
	{
		$baseType = $this->fieldType->getBaseType();
		if ($baseType === 'user')
		{
			return count(array_diff($this->value, $this->toCheck)) === 0;
		}

		$result = false;
		foreach (\CBPHelper::flatten($this->value) as $v)
		{
			foreach ($this->toCheck as $f)
			{
				if (is_array($f))
				{
					$result = in_array($v, $f, false);
				}
				elseif (
					\CBPHelper::hasStringRepresentation($f)
					&& \CBPHelper::hasStringRepresentation($v)
					&& (string)$v !== ''
				)
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
}