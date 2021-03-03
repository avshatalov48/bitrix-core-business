<?php

namespace Bitrix\Seo\BusinessSuite\Exception;


class ServiceLoadException extends ConfigException
{
	const EXCEPTION_TYPE = ConfigException::EXCEPTION_TYPE_SERVICE_LOAD;

	protected $engineCode;

	/**
	 * ServiceLoadException constructor.
	 *
	 * @param string $message
	 * @param int $code
	 * @param string $file
	 * @param int $line
	 * @param \Exception|null $previous
	 * @param string $engineCode
	 */
	public function __construct(
		$message = "",
		$code = 0,
		$file = "",
		$line = 0,
		\Exception $previous = null,
		$engineCode = ''
	)
	{
		parent::__construct($message, $code, $file, $line, $previous);
		$this->engineCode = $engineCode;
	}

	/**
	 * @return array
	 */
	public function getCustomData(): array
	{
		return parent::getCustomData() + [
				'engineCode' => $this->engineCode,
			];
	}
}