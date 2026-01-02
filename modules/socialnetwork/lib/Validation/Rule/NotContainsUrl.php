<?php

declare(strict_types=1);

namespace Bitrix\SocialNetwork\Validation\Rule;

use Attribute;
use Bitrix\Main\Validation\Rule\AbstractPropertyValidationAttribute;
use Bitrix\SocialNetwork\Validation\Validator\NotContainsUrlValidator;

#[Attribute(Attribute::TARGET_PROPERTY)]
class NotContainsUrl extends AbstractPropertyValidationAttribute
{
	protected function getValidators(): array
	{
		return [
			new NotContainsUrlValidator(),
		];
	}
}