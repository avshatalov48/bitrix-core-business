<?php

declare(strict_types=1);

namespace Bitrix\Main\Validation\Rule;

abstract class AbstractClassValidationAttribute implements ClassValidationAttributeInterface
{
	use ValidationErrorTrait;
}