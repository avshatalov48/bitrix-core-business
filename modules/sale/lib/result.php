<?php
namespace Bitrix\Sale;

use Bitrix\Main\Entity;
use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;

class Result extends Entity\Result
{
	/** @var  int */
	protected $id;

	protected $warnings = array();

	/** @var bool */
	protected $isSuccess = true;

	public function __construct()
	{
		$this->warnings = new ErrorCollection();
		parent::__construct();
	}

	public function setId($id)
	{
		$this->id = $id;
	}

	/**
	 * Returns id of added record
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	public function __destruct()
	{
		//just quietly die in contrast Entity\Result either checked errors or not.
	}

	public function addData(array $data)
	{
		if (is_array($this->data))
		{
			$this->data = $this->data + $data;
		}
		else
		{
			$this->data = $data;
		}
	}

	public function get($offset)
	{
		if (isset($this->data[$offset]) || array_key_exists($offset, $this->data))
		{
			return $this->data[$offset];
		}

		return null;
	}

	public function set($offset, $value)
	{
		if ($offset === null)
		{
			$this->data[] = $value;
		}
		else
		{
			$this->data[$offset] = $value;
		}
	}

	/**
	 * @param Error[] $errors
	 *
	 * @return void
	 */
	public function addWarnings(array $errors)
	{
		/** @var Error $error */
		foreach ($errors as $error)
		{
			$this->addWarning(ResultWarning::create($error));
		}
	}

	/**
	 * Adds the error.
	 *
	 * @param Error $error
	 */
	public function addWarning(Error $error)
	{
		$this->warnings[] = $error;
	}

	/**
	 * Adds the error.
	 *
	 * @param Error $error
	 * @return Result
	 */
	public function addError(Error $error)
	{
		if ($error instanceof ResultWarning)
		{
			static::addWarning($error);
		}
		else
		{
			$this->isSuccess = false;
			$this->errors[] = $error;
		}

		return $this;
	}

	/**
	 * Returns an array of Error objects.
	 *
	 * @return Error[]
	 */
	public function getWarnings()
	{
		return $this->warnings->toArray();
	}

	/**
	 * Returns array of strings with warning messages
	 *
	 * @return array
	 */
	public function getWarningMessages()
	{
		$messages = array();

		foreach($this->getWarnings() as $warning)
			$messages[] = $warning->getMessage();

		return $messages;
	}


	/**
	 * @return bool
	 */
	public function hasWarnings()
	{
		return (count($this->warnings));
	}


}

class ResultError
	extends Entity\EntityError
{
	/**
	 * @param Error $error
	 *
	 * @return static
	 */
	public static function create(Error $error)
	{
		return new static($error->getMessage(), $error->getCode());
	}
}

class ResultWarning
		extends ResultError
{

}

class ResultNotice
		extends ResultError
{

}
