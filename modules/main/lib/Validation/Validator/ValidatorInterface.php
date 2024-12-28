<?php

declare(strict_types=1);

namespace Bitrix\Main\Validation\Validator;

use Bitrix\Main\Validation\ValidationResult;

interface ValidatorInterface
{
	public function validate(mixed $value): ValidationResult;
}