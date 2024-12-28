<?php

declare(strict_types=1);

namespace Bitrix\Main\Validation\Rule;

use Bitrix\Main\Validation\ValidationResult;

interface ClassValidationAttributeInterface
{
	public function validateObject(object $object): ValidationResult;
}