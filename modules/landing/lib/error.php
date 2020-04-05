<?php
namespace Bitrix\Landing;

class Error
{
	/**
	 * Current errors.
	 * @var array
	 */
	protected $errors = array();

	/**
	 * Add error to the current collection.
	 * @param string $code Error code.
	 * @param string $message Error message.
	 * @return void
	 */
	public function addError($code, $message= '')
	{
		$this->errors[] = new \Bitrix\Main\Error($message != '' ? $message : $code, $code);
	}

	/**
	 * Copy Error from one to this.
	 * @param \Bitrix\Landing\Error $error Error.
	 * @return void
	 */
	public function copyError(\Bitrix\Landing\Error $error)
	{
		foreach ($error->getErrors() as $err)
		{
			$this->errors[] = $err;
		}
	}

	/**
	 * Get current errors.
	 * @return array
	 */
	public function getErrors()
	{
		return $this->errors;
	}

	/**
	 * Exist or not errors.
	 * @return boolean
	 */
	public function isEmpty()
	{
		return empty($this->errors);
	}

	/**
	 * Collect errors from result.
	 * @param \Bitrix\Main\Entity\AddResult|UpdateResult $result Result.
	 * @return void
	 */
	public function addFromResult($result)
	{
		if (
			(
			$result instanceof \Bitrix\Main\Entity\AddResult ||
			$result instanceof \Bitrix\Main\Entity\UpdateResult ||
			$result instanceof \Bitrix\Main\Entity\DeleteResult
			) && !$result->isSuccess()
		)
		{
			foreach ($result->getErrors() as $error)
			{
				$this->addError(
					$error->getCode(),
					$error->getMessage()
				);
			}
		}
	}
}