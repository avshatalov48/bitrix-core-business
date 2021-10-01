<?php

namespace Bitrix\Main\Engine\AutoWire;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Error;

class BinderArgumentException extends ArgumentException
{
	/** @var Error[] */
	protected $addedErrorsFromClosure;

	/**
	 * BinderArgumentException constructor.
	 *
	 * @param Error[] $addedErrorsFromClosure
	 */
	public function __construct($message = "", $parameter = "", array $addedErrorsFromClosure = [], \Exception $previous = null)
	{
		parent::__construct($message, $parameter, $previous);
		$this->addedErrorsFromClosure = $addedErrorsFromClosure;
	}

	/**
	 * @return Error[]
	 */
	public function getErrors(): array
	{
		return $this->addedErrorsFromClosure;
	}
}