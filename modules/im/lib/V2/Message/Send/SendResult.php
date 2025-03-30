<?php

namespace Bitrix\Im\V2\Message\Send;

use Bitrix\Im\V2\Async\Promise;
use Bitrix\Im\V2\Result;

class SendResult extends Result
{
	private ?int $messageId = null;
	private ?Promise $promiseToCompleteSending = null;

	public function getMessageId(): ?int
	{
		return $this->messageId;
	}

	public function setMessageId(int $messageId): SendResult
	{
		$this->messageId = $messageId;

		return $this;
	}

	public function getPromise(): ?Promise
	{
		return $this->promiseToCompleteSending;
	}

	public function setPromise(Promise $promiseToCompleteSending): SendResult
	{
		$this->promiseToCompleteSending = $promiseToCompleteSending;

		return $this;
	}
}