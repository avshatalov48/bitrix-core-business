<?php

namespace Bitrix\Translate;

use Bitrix\Main;

/**
 * Warning collection.
 */
trait Warning
{
	/** @var Main\ErrorCollection */
	protected $warningCollection;

	/**
	 * Adds warning to collection.
	 *
	 * @param Main\Error $error Error.
	 *
	 * @return $this
	 */
	final public function addWarning(Main\Error $error): self
	{
		if (!$this->warningCollection instanceof Main\ErrorCollection)
		{
			$this->warningCollection = new Main\ErrorCollection;
		}

		$this->warningCollection[] = $error;

		return $this;
	}

	/**
	 * Getting array of warnings.
	 *
	 * @return Main\Error[]
	 */
	final public function getWarnings(): array
	{
		if (!$this->warningCollection instanceof Main\ErrorCollection)
		{
			return array();
		}

		return $this->warningCollection->toArray();
	}

	/**
	 * Checks if warning occurred.
	 *
	 * @return boolean
	 */
	final public function hasWarnings(): bool
	{
		if (!$this->warningCollection instanceof Main\ErrorCollection)
		{
			return false;
		}

		return !$this->warningCollection->isEmpty();
	}

	/**
	 * Returns last warning from list.
	 *
	 * @return Main\Error|null
	 */
	final public function getLastWarning(): ?Main\Error
	{
		if (!$this->warningCollection instanceof Main\ErrorCollection)
		{
			return null;
		}
		if (!$this->hasWarnings())
		{
			return null;
		}

		$offset = $this->warningCollection->count() - 1;

		return $this->warningCollection->offsetGet($offset);
	}

	/**
	 * Returns first warning from list.
	 *
	 * @return Main\Error|null
	 */
	final public function getFirstWarning(): ?Main\Error
	{
		if (!$this->warningCollection instanceof Main\ErrorCollection)
		{
			return null;
		}
		if (!$this->hasWarnings())
		{
			return null;
		}

		return $this->warningCollection->offsetGet(0);
	}
}
