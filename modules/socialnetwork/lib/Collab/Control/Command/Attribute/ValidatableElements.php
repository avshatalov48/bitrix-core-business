<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Control\Command\Attribute;

use ArrayAccess;
use Attribute;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Validation\Rule\PropertyValidationAttributeInterface;
use Bitrix\Main\Validation\ValidationError;
use Bitrix\Main\Validation\ValidationResult;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ValidatableElements implements PropertyValidationAttributeInterface
{
	public function validateProperty(mixed $propertyValue): ValidationResult
	{
		$result = new ValidationResult();
		if (!is_array($propertyValue) && !is_iterable($propertyValue) && !($propertyValue instanceof ArrayAccess))
		{
			$result->addError(
				new ValidationError(
					Loc::getMessage('SOCIALNETWORK_COLLAB_VALIDATOR_VALIDATABLE_ELEMENTS_INVALID_TYPE'),
				),
			);

			return $result;
		}

		$validationService = ServiceLocator::getInstance()->get('main.validation.service');
		foreach ($propertyValue as $item)
		{
			if (!is_object($item))
			{
				$result->addError(
					new ValidationError(
						Loc::getMessage('SOCIALNETWORK_COLLAB_VALIDATOR_VALIDATABLE_ELEMENTS_INVALID_VALUE'),
					),
				);

				return $result;
			}

			$validateElementResult = $validationService->validate($item);

			if (!$validateElementResult->isSuccess())
			{
				$result->addErrors($validateElementResult->getErrors());

				return $result;
			}
		}

		return $result;
	}
}
