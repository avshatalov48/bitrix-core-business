<?php
namespace Bitrix\Sale\Helpers\Order\Builder;

use Bitrix\Main\Error;
use Bitrix\Sale\Result;

final class ErrorsContainer extends Result
{
	protected $acceptableErrorCodes = [];

	/**
	 * Adds the error.
	 * @param Error $error
	 */
	public function addError(Error $error)
	{
		if(!$this->isErrorAcceptable($error))
		{
			parent::addError($error);
		}
	}

	/**
	 * Adds array of Error objects
	 *
	 * @param Error[] $errors
	 * @return $this
	 */
	public function addErrors(array $errors)
	{
		if(!empty($this->acceptableErrorCodes))
		{
			$errorsToAdd = array();

			foreach($errors as $error)
			{
				if(!$this->isErrorAcceptable($error))
				{
					$errorsToAdd[] = $error;
				}
			}

			$this->errors->add($errorsToAdd);
		}
		else
		{
			$this->errors->add($errors);
		}
		return $this;
	}

	public function setAcceptableErrorCodes(array $errorCodes)
	{
		$this->acceptableErrorCodes = $errorCodes;
	}

	private function isErrorAcceptable(Error $error)
	{
		if(empty($this->acceptableErrorCodes))
		{
			return false;
		}

		$code = $error->getCode();

		if(empty($code))
		{
			return false;
		}

		return in_array($code, $this->acceptableErrorCodes);
	}
}