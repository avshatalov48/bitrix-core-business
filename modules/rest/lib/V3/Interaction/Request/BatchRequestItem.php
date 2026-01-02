<?php

namespace Bitrix\Rest\V3\Interaction\Request;

use Bitrix\Rest\V3\Interaction\Response\Response;

class BatchRequestItem
{
	private Response $response;

	public function __construct(private string $method, private array $query, private ?string $alias = null, private bool $parallel = false)
	{
	}

	public function getMethod(): string
	{
		return $this->method;
	}

	public function getQuery(): array
	{
		return $this->query;
	}

	public function getAlias(): ?string
	{
		return $this->alias;
	}

	public function isParallel(): bool
	{
		return $this->parallel;
	}

	public function getResponse(): Response
	{
		return $this->response;
	}

	public function setResponse(Response $response): BatchRequestItem
	{
		$this->response = $response;
		return $this;
	}
}