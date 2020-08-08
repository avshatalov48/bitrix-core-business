<?php declare(strict_types = 1);

namespace Bitrix\Translate;

use Bitrix\Main;

/**
 * @implements Translate\IErrorable
 */
trait Error
{
	/** @var Main\ErrorCollection */
	protected $errorCollection;

	/**
	 * Adds error to error collection.
	 *
	 * @param Main\Error $error Error.
	 *
	 * @return $this
	 */
	final public function addError(Main\Error $error): self
	{
		if (!$this->errorCollection instanceof Main\ErrorCollection)
		{
			$this->errorCollection = new Main\ErrorCollection;
		}

		$this->errorCollection[] = $error;

		return $this;
	}

	/**
	 * Adds list of errors to error collection.
	 *
	 * @param Main\Error[] $errors Errors.
	 *
	 * @return $this
	 */
	final public function addErrors(array $errors): self
	{
		if (!$this->errorCollection instanceof Main\ErrorCollection)
		{
			$this->errorCollection = new Main\ErrorCollection;
		}

		$this->errorCollection->add($errors);

		return $this;
	}

	/**
	 * Getting array of errors.
	 *
	 * @return Main\Error[]
	 */
	final public function getErrors(): array
	{
		if (!$this->errorCollection instanceof Main\ErrorCollection)
		{
			return array();
		}

		return $this->errorCollection->toArray();
	}

	/**
	 * Getting once error with the necessary code.
	 *
	 * @param string|int $code Code of error.
	 *
	 * @return Main\Error|null
	 */
	final public function getErrorByCode($code): ?Main\Error
	{
		if (!$this->errorCollection instanceof Main\ErrorCollection)
		{
			return null;
		}

		return $this->errorCollection->getErrorByCode($code);
	}

	/**
	 * Returns last error from list.
	 *
	 * @return Main\Error|null
	 */
	final public function getLastError(): ?Main\Error
	{
		if (!$this->errorCollection instanceof Main\ErrorCollection)
		{
			return null;
		}
		if (!$this->hasErrors())
		{
			return null;
		}

		$offset = $this->errorCollection->count() - 1;

		return $this->errorCollection->offsetGet($offset);
	}

	/**
	 * Returns first error from list.
	 *
	 * @return Main\Error|null
	 */
	final public function getFirstError(): ?Main\Error
	{
		if (!$this->errorCollection instanceof Main\ErrorCollection)
		{
			return null;
		}
		if (!$this->hasErrors())
		{
			return null;
		}

		return $this->errorCollection->offsetGet(0);
	}

	/**
	 * Checks if error occurred.
	 *
	 * @return boolean
	 */
	final public function hasErrors(): bool
	{
		if (!$this->errorCollection instanceof Main\ErrorCollection)
		{
			return false;
		}

		return !$this->errorCollection->isEmpty();
	}

	/**
	 * Returns an error with the necessary code.
	 * @param string|int $code The code of the error.
	 *
	 * @return boolean
	 */
	final public function hasError($code): bool
	{
		if (
			!$this->errorCollection instanceof Main\ErrorCollection ||
			$this->errorCollection->isEmpty()
		)
		{
			return false;
		}

		$err = $this->errorCollection->getErrorByCode($code);

		return ($err instanceof Main\Error);
	}
}
