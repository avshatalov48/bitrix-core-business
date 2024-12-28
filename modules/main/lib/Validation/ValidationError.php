<?php

declare(strict_types=1);

namespace Bitrix\Main\Validation;

use Bitrix\Main\Error;
use Bitrix\Main\Validation\Validator\ValidatorInterface;

class ValidationError extends Error
{
	protected ?ValidatorInterface $failedValidator;

	public function __construct($message, $code = 0, $customData = null, ?ValidatorInterface $failedValidator = null)
	{
		parent::__construct($message, $code, $customData);
		$this->failedValidator = $failedValidator;
	}

	public function getFailedValidator(): ?ValidatorInterface
	{
		return $this->failedValidator;
	}

	public function setFailedValidator(ValidatorInterface $failedValidator): static
	{
		$this->failedValidator = $failedValidator;

		return $this;
	}

	public function setCode(int|string $code): static
	{
		$this->code = $code;

		return $this;
	}

	public function hasCode(): bool
	{
		return !empty($this->code);
	}
}