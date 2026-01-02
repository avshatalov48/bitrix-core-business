<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Onboarding\Internals\Validation\Rule;

use Attribute;
use Bitrix\Main\Validation\Rule\AbstractPropertyValidationAttribute;
use Bitrix\Socialnetwork\Collab\Onboarding\Internals\Validation\Validator\ArrayOfJobTypesValidator;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ArrayOfJobTypes extends AbstractPropertyValidationAttribute
{
	protected function getValidators(): array
	{
		return [
			new ArrayOfJobTypesValidator(),
		];
	}
}
