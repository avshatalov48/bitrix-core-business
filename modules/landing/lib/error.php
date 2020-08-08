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
	 * Returns first error frm errors stack.
	 * @return \Bitrix\Main\Error|null
	 */
	public function getFirstError()
	{
		if ($this->errors)
		{
			$errors = array_values($this->errors);
			return array_shift($errors);
		}
		return null;
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
	 * @param \Bitrix\Main\Result $result Result.
	 * @return void
	 */
	public function addFromResult($result)
	{
		if (
			(
			$result instanceof \Bitrix\Main\Result ||
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