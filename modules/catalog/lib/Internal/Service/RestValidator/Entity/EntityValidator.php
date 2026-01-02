<?php

namespace Bitrix\Catalog\Internal\Service\RestValidator\Entity;

use Bitrix\Catalog\Internal\Service\RestValidator\Trait;
use Bitrix\Iblock\Public\Service\RestValidator\Format\BaseValidator;
use Bitrix\Main\Result;
use Bitrix\Rest\Integration\View\Base;

abstract class EntityValidator implements EntityValidatorInterface
{
	use Trait\CanonicalNameTrait;

	protected Base $view;

	public function __construct()
	{
		/** @var Base $className */
		$className = $this->getViewClassName();
		$this->view = new $className;

		$this->fillCanonicalNames();
	}

	abstract public function getViewClassName(): string;

	public function getView(): Base
	{
		return $this->view;
	}

	protected function fillCanonicalNames(): void
	{
		$this->setCanonicalNames($this->getView()->getCanonicalNames());
	}

	protected function runValidator(BaseValidator $validator, array $rawData): Result
	{
		if (empty($rawData))
		{
			return new Result();
		}

		if (!$validator->isExistsFieldAliases())
		{
			$validator->setFieldAliases($this->getFieldAliases($validator));
		}

		return $validator->run($rawData);
	}

	public function run(array $rawData): Result
	{
		$result = new Result();
		if (empty($rawData))
		{
			return $result;
		}

		foreach ($this->getFormatValidators() as $validator)
		{
			$validatorResult = $this->runValidator($validator, $rawData);
			if (!$validatorResult->isSuccess())
			{
				$result->addErrors($validatorResult->getErrors());
			}
		}

		return $result;
	}

	/**
	 * @return BaseValidator[]
	 */
	abstract public function getFormatValidators(): array;
}
