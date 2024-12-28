<?php

namespace Bitrix\Socialnetwork\Permission\Trait;

use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;

trait AccessErrorTrait
{
	protected array $errorCollection = [];

	/** @return Error[] */
	public function getErrors(): array
	{
		return $this->errorCollection;
	}

	public function addError(string $class, string $message = ''): void
	{
		$this->errorCollection[] = new Error($message, $class);
	}

	public function getErrorCollection(): ErrorCollection
	{
		$collection = new ErrorCollection();
		foreach ($this->errorCollection as $error)
		{
			$collection->setError($error);
		}

		return $collection;
	}
}