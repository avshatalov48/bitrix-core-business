<?php

namespace Bitrix\MessageService\DTO;

class Request
{
	public $method;
	public $uri;
	public $headers = [];
	public $body;

	public function __construct(array $fields = null)
	{
		if ($fields !== null)
		{
			$this->hydrate($fields);
		}
	}

	public function hydrate(array $fields)
	{
		$this->method = $fields['method'] ?? $this->method;
		$this->uri = $fields['uri'] ?? $this->uri;
		$this->headers = $fields['headers'] ?? $this->headers;
		$this->body = $fields['body'] ?? $this->body;
	}
}