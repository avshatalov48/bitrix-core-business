<?php

namespace Bitrix\Main\Engine\Response;


use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Errorable;

class AjaxJson extends Json implements Errorable
{
	const STATUS_SUCCESS = 'success';
	const STATUS_DENIED  = 'denied';
	const STATUS_ERROR   = 'error';
	/**
	 * @var string
	 */
	private $status;
	/**
	 * @var ErrorCollection
	 */
	private $errorCollection;

	public function __construct($data = null, $status = self::STATUS_SUCCESS, ErrorCollection $errorCollection = null)
	{
		$this->status = $status?: self::STATUS_SUCCESS;
		$this->errorCollection = $errorCollection?: new ErrorCollection;

		parent::__construct($data);
	}

	final public static function createSuccess($data = null)
	{
		return new self($data, self::STATUS_SUCCESS, null);
	}

	final public static function createError(ErrorCollection $errorCollection = null, $data = null)
	{
		return new self($data, self::STATUS_ERROR, $errorCollection);
	}

	final public static function createDenied(ErrorCollection $errorCollection = null, $data = null)
	{
		return new self($data, self::STATUS_DENIED, $errorCollection);
	}

	final public function setData($data)
	{
		/**
		 * @see \Bitrix\Main\Type\Contract\Arrayable
		 * @see \Bitrix\Main\Type\Contract\Jsonable
		 * todo: we have to add in Json::encode work with Arrayable, Jsonable, to convert this data properly.
		 */
		return parent::setData(
			array(
				'status' => $this->status,
				'data' => $data,
				'errors' => $this->getErrorsToResponse(),
			)
		);
	}

	final protected function getErrorsToResponse()
	{
		$errors = array();
		foreach ($this->errorCollection as $error)
		{
			/** @var Error $error */
			$errors[] = $error;
		}

		return $errors;
	}

	/**
	 * Getting array of errors.
	 * @return Error[]
	 */
	final public function getErrors()
	{
		return $this->errorCollection->toArray();
	}

	/**
	 * Getting once error with the necessary code.
	 * @param string $code Code of error.
	 * @return Error
	 */
	final public function getErrorByCode($code)
	{
		return $this->errorCollection->getErrorByCode($code);
	}
}