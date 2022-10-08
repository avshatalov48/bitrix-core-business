<?php
namespace Bitrix\Calendar\Internals;

/**
 * Object for saving statuses of other objects/
 * See also ObjectStatusTrait.
 */
class ObjectStatus
{
	/** @var array  */
	private $errors = [];

	/**
	 * @return bool
	 */
	public function isSuccess(): bool
	{
		return empty($this->errors);
	}

	/**
	 * @return bool
	 */
	public function hasErrors(): bool
	{
		return !$this->isSuccess();
	}

	/**
	 * @param string $code
	 * @param string $message
	 * @return void
	 */
	public function addError(string $code, string $message)
	{
		$this->errors[] = [
			'code' => $code,
			'message' => $message,
		];
	}

	/**
	 * @return void
	 */
	public function resetErrors()
	{
		$this->errors = [];
	}

	/**
	 * @return array
	 */
	public function getErrors(): array
	{
		return $this->errors;
	}

	/**
	 * @param string $code
	 * @return array
	 */
	public function getErrorsByCode(string $code): array
	{
		if(!$this->hasErrors()) {
			return [];
		}

		return array_filter($this->errors, function($error) use ($code)
		{
			return $error['code'] == $code;
		});
	}

	/**
	 * @param string $code
	 * @return array
	 */
	public function getErrorByCode(string $code): array
	{
		if($filtredErrors = $this->getErrorsByCode($code))
		{
			return end($filtredErrors);
		}

		return [];
	}
}
