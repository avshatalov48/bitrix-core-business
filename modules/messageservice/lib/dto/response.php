<?php

namespace Bitrix\MessageService\DTO;

class Response
{
	public $statusCode = 0;
	public $headers = [];
	public $body = "";
	public $error = "";

	public function __construct(array $fields = null)
	{
		if ($fields !== null)
		{
			$this->hydrate($fields);
		}
	}

	public function hydrate(array $fields)
	{
		$this->statusCode = isset($fields['statusCode']) ? (int)$fields['statusCode'] : $this->statusCode;
		$this->headers = $fields['headers'] ?? $this->headers;
		$this->body = $fields['body'] ?? $this->body;
		$this->error = $fields['error'] ?? $this->error;
	}
}