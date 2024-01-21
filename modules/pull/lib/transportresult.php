<?php

namespace Bitrix\Pull;

class TransportResult extends \Bitrix\Main\Result
{
	protected string $remoteAddress = '';

	public function getRemoteAddress(): string
	{
		return $this->remoteAddress;
	}

	public function withRemoteAddress(string $remoteAddress): TransportResult
	{
		$this->remoteAddress = $remoteAddress;
		return $this;
	}
}