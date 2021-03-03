<?php

namespace Bitrix\Seo\BusinessSuite\Exception;


class UnknownFieldException extends ConfigException
{
	const EXCEPTION_TYPE = ConfigException::EXCEPTION_TYPE_UNKNOWN_FIELD;

	protected $fieldCode;

	/**
	 * UnknownFieldException constructor.
	 *
	 * @param string $message
	 * @param int $code
	 * @param string $file
	 * @param int $line
	 * @param \Exception|null $previous
	 * @param string $fieldCode
	 */
	public function __construct(
		$message = "",
		$code = 0,
		$file = "",
		$line = 0,
		\Exception $previous = null,
		$fieldCode = ''
	)
	{
		parent::__construct($message, $code, $file, $line, $previous);
		$this->fieldCode = $fieldCode;
	}

	/**
	 * @return array
	 */
	public function getCustomData(): array
	{
		return parent::getCustomData() + [
				'code' => $this->fieldCode,
			];
	}
}