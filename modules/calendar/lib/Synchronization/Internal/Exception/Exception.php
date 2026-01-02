<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Exception;

use Bitrix\Main\SystemException;

class Exception extends SystemException implements ExceptionInterface
{
	private bool $isPublic = false;

	public function __construct(
		string $message = '',
		int $code = 0,
		\Throwable $previous = null,
		string $file = '',
		int $line = 0
	)
	{
		parent::__construct($message, $code, $file, $line, $previous);
	}

	public function setIsPublic(bool $isPublic): self
	{
		$this->isPublic = $isPublic;

		return $this;
	}

	public function isPublic(): bool
	{
		return $this->isPublic;
	}
}
