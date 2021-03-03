<?php

namespace Bitrix\Seo\BusinessSuite\Exception;


class UnresolvedDependencyException extends ConfigException
{

	const EXCEPTION_TYPE = ConfigException::EXCEPTION_TYPE_UNRESOLVED_DEPENDENCY;

	protected $fieldCode;
	protected $depends;

	/**
	 * UnresolvedDependencyException constructor.
	 *
	 * @param string $message
	 * @param int $code
	 * @param string $file
	 * @param int $line
	 * @param \Exception|null $previous
	 * @param string $fieldCode
	 * @param array $depends
	 */
	public function __construct(
		$message = "",
		$code = 0,
		$file = "",
		$line = 0,
		\Exception $previous = null,
		$fieldCode = '',
		$depends = []
	)
	{
		parent::__construct($message, $code, $file, $line, $previous);
		$this->fieldCode = $fieldCode;
		$this->depends = $depends;
	}

	/**
	 * @return array
	 */
	public function getCustomData(): array
	{
		return parent::getCustomData() + [
				'code' => $this->fieldCode,
				'dependencies' => $this->depends
			];
	}
}