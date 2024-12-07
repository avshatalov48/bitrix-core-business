<?php

namespace Bitrix\Socialnetwork\Permission\Trait;

use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;

trait AccessErrorTrait
{
	protected array $errorCollection = [];

	public function getErrors(): array
	{
		return $this->errorCollection;
	}

	public function addError(string $class, string $message = ''): void
	{
		$this->errorCollection[] = $class .': '. $message;
	}

	public function getErrorCollection(): ErrorCollection
	{
		$collection = new ErrorCollection();
		foreach ($this->errorCollection as $errorMessage)
		{
			$collection->setError(new Error($errorMessage));
		}

		return $collection;
	}
}