<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Onboarding\Command;

use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Result;
use Bitrix\Main\Validation\ValidationResult;
use Bitrix\Main\Validation\ValidationService;

abstract class AbstractCommand
{
	private ValidationService $validationService;

	public function __construct()
	{
		$this->validationService = ServiceLocator::getInstance()->get('main.validation.service');
	}

	abstract protected function execute(): Result;

	public function run(): Result
	{
		$validationResult = $this->validate();
		if (!$validationResult->isSuccess())
		{
			return $validationResult;
		}

		return $this->execute();
	}

	protected function validate(): ValidationResult
	{
		return $this->validationService->validate($this);
	}
}