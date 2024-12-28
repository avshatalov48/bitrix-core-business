<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2015 Bitrix
 */

namespace Bitrix\Main;

use Bitrix\Main\DB\SqlExpression;

class Result
{
	/** @var bool */
	protected $isSuccess = true;

	/** @var ErrorCollection */
	protected $errors;

	/** @var  array */
	protected $data = array();

	public function __construct()
	{
		$this->errors = new ErrorCollection();
	}

	public function __clone()
	{
		$this->errors = clone $this->errors;
	}

	/**
	 * Returns the result status.
	 *
	 * @return bool
	 */
	public function isSuccess()
	{
		return $this->isSuccess;
	}

	/**
	 * Adds the error.
	 *
	 * @param Error $error
	 * @return $this
	 */
	public function addError(Error $error)
	{
		$this->isSuccess = false;
		$this->errors[] = $error;
		return $this;
	}

	/**
	 * Returns the Error object.
	 *
	 * @return Error|null
	 */
	public function getError(): ?Error
	{
		foreach ($this->errors as $error)
		{
			return $error;
		}

		return null;
	}

	/**
	 * Returns an array of Error objects.
	 *
	 * @return Error[]
	 */
	public function getErrors()
	{
		return $this->errors->toArray();
	}

	/**
	 * Returns the error collection.
	 *
	 * @return ErrorCollection
	 */
	public function getErrorCollection()
	{
		return $this->errors;
	}

	/**
	 * Returns array of strings with error messages
	 *
	 * @return string[]
	 */
	public function getErrorMessages()
	{
		$messages = array();

		foreach($this->getErrors() as $error)
			$messages[] = $error->getMessage();

		return $messages;
	}

	/**
	 * Adds array of Error objects
	 *
	 * @param Error[] $errors
	 * @return $this
	 */
	public function addErrors(array $errors)
	{
		if ($errors)
		{
			$this->isSuccess = false;
			$this->errors->add($errors);
		}
		return $this;
	}

	/**
	 * Sets data of the result.
	 * @param array $data
	 * @return $this
	 */
	public function setData(array $data)
	{
		// do not save sql expressions
		foreach ($data as $k => $v)
		{
			if ($v instanceof SqlExpression)
			{
				unset($data[$k]);
			}
		}

		$this->data = $data;

		return $this;
	}

	/**
	 * Returns data array saved into the result.
	 * @return array
	 */
	public function getData()
	{
		return $this->data;
	}
}
