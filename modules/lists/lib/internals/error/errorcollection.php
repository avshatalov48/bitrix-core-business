<?php

namespace Bitrix\Lists\Internals\Error;

use Bitrix\Main\Entity\Result;
use Bitrix\Main;

final class ErrorCollection extends Main\ErrorCollection
{
	/**
	 * Adds one error to collection.
	 * @param Error $error Error object.
	 * @return void
	 */
	public function addOne(Error $error)
	{
		$this[] = $error;
	}

	/**
	 * Adds errors from Main\Entity\Result.
	 * @param Result $result Result after action in Entity.
	 * @return void
	 */
	public function addFromResult(Result $result)
	{
		$errors = array();
		foreach ($result->getErrorMessages() as $message)
		{
			$errors[] = new Error($message);
		}
		unset($message);

		$this->add($errors);
	}

	/**
	 * Returns true if collection has errors.
	 * @return bool
	 */
	public function hasErrors()
	{
		return (bool)count($this);
	}

	/**
	 * Returns array of errors with the necessary code.
	 * @param string $code Code of error.
	 * @return Error[]
	 */
	public function getErrorsByCode($code)
	{
		$needle = array();
		foreach($this->values as $error)
		{
			/** @var Error $error */
			if($error->getCode() == $code)
			{
				$needle[] = $error;
			}
		}
		unset($error);

		return $needle;
	}
}