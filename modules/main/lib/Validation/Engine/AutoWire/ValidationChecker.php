<?php

declare(strict_types=1);

namespace Bitrix\Main\Validation\Engine\AutoWire;

use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Engine\AutoWire\BinderArgumentException;
use ReflectionParameter;

final class ValidationChecker
{
	public function __construct(
		private readonly ReflectionParameter $parameter,
		private readonly mixed $desiredValue
	)
	{
	}

	public function check(): void
	{
		$validationService = ServiceLocator::getInstance()->get('main.validation.service');

		$result = $validationService->validateParameter($this->parameter, $this->desiredValue);

		$error = $result->getError();
		if ($error !== null)
		{
			throw new BinderArgumentException(
				"Invalid value to match parameter: [{$error->getCode()}] {$error->getMessage()}.",
				$this->parameter->getName()
			);
		}
	}
}