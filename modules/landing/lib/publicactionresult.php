<?php
namespace Bitrix\Landing;

class PublicActionResult
{
	/**
	 * Instance of Error.
	 * @var \Bitrix\Landing\Error
	 */
	protected $error = null;

	/**
	 * Result of Public Action.
	 * @var mixed
	 */
	protected $result = null;

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		$this->error = new Error;
	}

	/**
	 * Result is success.
	 * @return boolean
	 */
	public function isSuccess()
	{
		return $this->error->isEmpty();
	}

	/**
	 * Set Error of Public Action.
	 * @param \Bitrix\Landing\Error $error Error.
	 * @return void
	 */
	public function setError(\Bitrix\Landing\Error $error)
	{
		$this->error->copyError($error);
	}

	/**
	 * Get error collection
	 * @return \Bitrix\Landing\Error
	 */
	public function getError()
	{
		return $this->error;
	}

	/**
	 * Set some result of Public Action.
	 * @param mixed $result Some result.
	 * @return void
	 */
	public function setResult($result)
	{
		$this->result = $result;
	}

	/**
	 * Get result of Public Action.
	 * @return mixed
	 */
	public function getResult()
	{
		return $this->result;
	}

	/**
	 * Sanitizes data's keys, if data is array.
	 * @param mixed $data Some data.
	 * @param bool $sanitizeValue Sanitize value of key too.
	 * @return mixed
	 */
	public function sanitizeKeys($data, $sanitizeValue = false)
	{
		if (is_array($data))
		{
			foreach ($data as $key => $value)
			{
				if (
					mb_strpos($key, '.') !== false ||
					mb_strtolower($key) == 'runtime' ||
					$sanitizeValue && mb_strpos($value, '.') !== false
				)
				{
					unset($data[$key]);
				}
				else
				{
					$data[$key] = $this->sanitizeKeys(
						$value,
						mb_strtolower($key) == 'select'
					);
				}
			}
		}
		return $data;
	}
}
