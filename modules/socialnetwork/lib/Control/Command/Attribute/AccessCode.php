<?php

namespace Bitrix\Socialnetwork\Control\Command\Attribute;

use ArrayAccess;
use Attribute;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Validation\Rule\PropertyValidationAttributeInterface;
use Bitrix\Main\Validation\ValidationError;
use Bitrix\Main\Validation\ValidationResult;

#[Attribute(Attribute::TARGET_PROPERTY)]
class AccessCode implements PropertyValidationAttributeInterface
{
	public function validateProperty(mixed $propertyValue): ValidationResult
	{
		$result = new ValidationResult();
		if (!is_array($propertyValue) && !is_iterable($propertyValue) && !($propertyValue instanceof ArrayAccess))
		{
			$result->addError(new ValidationError(
				Loc::getMessage('SOCIALNETWORK_VALIDATOR_ACCESS_CODE_INVALID_TYPE'),
			));

			return $result;
		}

		foreach ($propertyValue as $item)
		{
			if (!($this->isValidAccessCode($item)))
			{
				$result->addError(new ValidationError(
					Loc::getMessage('SOCIALNETWORK_VALIDATOR_ACCESS_CODE_INVALID_VALUE'),
				));

				return $result;
			}
		}

		return $result;
	}

	private function isValidAccessCode(mixed $code): bool
	{
		return \Bitrix\Main\Access\AccessCode::isValid($code);
	}
}