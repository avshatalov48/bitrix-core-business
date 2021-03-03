<?php

namespace Bitrix\Seo\BusinessSuite\Exception;

class InvalidFieldValue extends ConfigException
{
	const EXCEPTION_TYPE = ConfigException::EXCEPTION_TYPE_INVALID_VALUE;

	protected $fieldCode;
	protected $value;

	/**
	 * InvalidFieldValue constructor.
	 *
	 * @param string $message
	 * @param int $code
	 * @param string $file
	 * @param int $line
	 * @param \Exception|null $previous
	 * @param string $fieldCode
	 * @param string $value
	 */
	public function __construct(
		$message = "",
		$code = 0,
		$file = "",
		$line = 0,
		\Exception $previous = null,
		$fieldCode = '',
		$value = ''
	)
	{
		parent::__construct($message, $code, $file, $line, $previous);
		$this->fieldCode = $fieldCode;
		$this->value = $value;
	}

	/**
	 * @return array
	 */
	public function getCustomData(): array
	{
		return parent::getCustomData() + [
			'code' => $this->fieldCode,
			'value' => $this->value
		];
	}
}