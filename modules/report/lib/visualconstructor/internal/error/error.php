<?php

namespace Bitrix\Report\VisualConstructor\Internal\Error;

use Bitrix\Main;

/**
 * Class Error
 * @package Bitrix\Report\VisualConstructor\Internal\Error
 */
class Error extends Main\Error
{
	/** @var mixed */
	protected $data;

	/**
	 * Creates a new Error.
	 * @param string     $message Message of the error.
	 * @param int|string $code Code of the error.
	 * @param mixed|null $data Data.
	 */
	public function __construct($message, $code = 0, $data = null)
	{
		$this->data = $data;
		parent::__construct($message, $code);
	}

	/**
	 * @return mixed
	 */
	public function getData()
	{
		return $this->data;
	}
}
