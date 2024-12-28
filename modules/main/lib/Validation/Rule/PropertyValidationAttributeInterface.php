<?php

declare(strict_types=1);

namespace Bitrix\Main\Validation\Rule;

use Bitrix\Main\Validation\ValidationResult;

interface PropertyValidationAttributeInterface
{
	public function validateProperty(mixed $propertyValue): ValidationResult;
}