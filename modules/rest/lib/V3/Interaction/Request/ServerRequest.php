<?php

namespace Bitrix\Rest\V3\Interaction\Request;

use Bitrix\Main\HttpRequest;

final class ServerRequest
{
	protected ?string $scope = null;

	protected ?string $token = null;

	public function __construct(private string $method, private array $query = [], private HttpRequest $httpRequest)
	{
		if (isset($this->query['token']) && !empty($this->query['token']))
		{
			$this->token = $this->query['token'];
		}
	}

	public function getMethod(): string
	{
		return $this->method;
	}

	public function getQuery(): array
	{
		return $this->query;
	}

	public function setQuery(array $query): static
	{
		$this->query = $query;

		return $this;
	}

	public function getScope(): ?string
	{
		return $this->scope;
	}

	public function setScope(?string $scope): self
	{
		$this->scope = $scope;

		return $this;
	}

	public function getToken(): ?string
	{
		return $this->token;
	}

	public function getHttpRequest(): HttpRequest
	{
		return $this->httpRequest;
	}
}
