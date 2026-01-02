<?php

namespace Bitrix\Im\V2\Message\Delete\Strategy;

use Bitrix\Im\V2\Result;

class InterruptedExecutionException extends \Exception
{
	protected ?Result $result = null;
	public function __construct(Result $result = new Result(), string $message = '', int $code = 0, \Throwable $previous = null)
	{
		$this->result = $result;
		parent::__construct($message, $code, $previous);
	}

	public function getResult(): Result
	{
		return $this->result ?? (new Result());
	}

	public function setResult(Result $result): self
	{
		$this->result = $result;
		return $this;
	}
}