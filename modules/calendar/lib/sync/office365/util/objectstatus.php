<?php
namespace Bitrix\Calendar\Sync\Office365\Util;

/**
 * Object for saving statuses of other objects/
 * See also ObjectStatusTrait.
 */
class ObjectStatus
{
	/** @var array  */
	private array $errors = [];

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
		if(!$this->hasErrors())
		{
			return [];
		}

		return array_filter($this->errors, static function($error) use ($code)
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
		if ($filteredErrors = $this->getErrorsByCode($code))
		{
			return end($filteredErrors);
		}

		return [];
	}
}
