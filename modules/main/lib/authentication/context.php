<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2025 Bitrix
 */

namespace Bitrix\Main\Authentication;

class Context implements \JsonSerializable
{
	protected $userId;
	protected $previousUserId;
	protected $applicationId;
	protected $applicationPasswordId;
	protected $storedAuthId;
	protected $storedAuthHash;
	protected $hitAuthId;
	protected $requestId;
	protected Method $method = Method::Unspecified;

	public function getUserId(): int
	{
		return (int)$this->userId;
	}

	public function setUserId(?int $userId): static
	{
		$this->userId = $userId;
		return $this;
	}

	public function getPreviousUserId(): int
	{
		return (int)$this->previousUserId;
	}

	public function setPreviousUserId(?int $userId): static
	{
		$this->previousUserId = $userId;
		return $this;
	}

	public function getApplicationId(): ?string
	{
		return $this->applicationId;
	}

	public function setApplicationId(?string $applicationId): static
	{
		$this->applicationId = $applicationId;
		return $this;
	}

	public function getApplicationPasswordId(): ?int
	{
		return $this->applicationPasswordId;
	}

	public function setApplicationPasswordId(?int $applicationPasswordId): static
	{
		$this->applicationPasswordId = $applicationPasswordId;
		return $this;
	}

	public function getStoredAuthId(): ?int
	{
		return $this->storedAuthId;
	}

	public function setStoredAuthId(?int $storedAuthId): static
	{
		$this->storedAuthId = $storedAuthId;
		return $this;
	}

	public function getHitAuthId(): ?int
	{
		return $this->hitAuthId;
	}

	public function setHitAuthId(?int $hitAuthId): static
	{
		$this->hitAuthId = $hitAuthId;
		return $this;
	}

	public function getStoredAuthHash(): ?string
	{
		return $this->storedAuthHash;
	}

	public function setStoredAuthHash(?string $storedAuthHash): static
	{
		$this->storedAuthHash = $storedAuthHash;
		return $this;
	}

	public function getMethod(): Method
	{
		return $this->method;
	}

	public function setMethod(Method $method): static
	{
		$this->method = $method;
		return $this;
	}

	public function getRequestId(): ?string
	{
		return $this->requestId;
	}

	public function setRequestId(?string $requestId): static
	{
		$this->requestId = $requestId;
		return $this;
	}

	public function jsonSerialize(): array
	{
		$data = [];

		foreach ($this as $property => $dummy)
		{
			$data[$property] = $this->{$property};
		}

		return $data;
	}

	public static function jsonDecode(string $json): static
	{
		$obj = new static();

		if ($json != '')
		{
			$data = json_decode($json, true);

			foreach ($obj as $property => $dummy)
			{
				if (isset($data[$property]))
				{
					if ($obj->{$property} instanceof Method)
					{
						$obj->{$property} = Method::tryFrom($data[$property]) ?? Method::Unspecified;
					}
					else
					{
						$obj->{$property} = $data[$property];
					}
				}
			}
		}

		return $obj;
	}

	public function prepareForLog(): static
	{
		$context = clone $this;

		unset($context->storedAuthHash);

		foreach ($this as $property => $dummy)
		{
			if (empty($this->{$property}))
			{
				unset($context->{$property});
			}
		}

		return $context;
	}
}
