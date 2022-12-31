<?php

namespace Bitrix\Calendar\Core\Queue\Message;

use Bitrix\Calendar\Core;

class Message implements Core\Queue\Interfaces\Message, Core\Base\EntityInterface
{
	/** @var array $body */
	private array $body = [];
	/** @var array $headers */
	private array $headers = [];
	/** @var array $properties */
	private array $properties = [];
	/** @var int|null $id */
	private ?int $id = null;

	/**
	 * @param int|null $id
	 * @return Message
	 */
	public function setId(?int $id): Message
	{
		$this->id = $id;

		return $this;
	}

	/**
	 * @return int|null
	 */
	public function getId(): ?int
	{
		return $this->id;
	}

	/**
	 * @return array
	 */
	public function getBody(): array
	{
		return $this->body;
	}

	/**
	 * @param $body
	 * @return $this
	 */
	public function setBody($body): self
	{
		$this->body = $body;

		return $this;
	}

	/**
	 * @param array $properties
	 * @return $this
	 */
	public function setProperties(array $properties): self
	{
		$this->properties = $properties;

		return $this;
	}

	/**
	 * @param string $name
	 * @param $value
	 * @return $this
	 */
	public function setProperty(string $name, $value): self
	{
		$this->properties[$name] = $value;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getProperties(): array
	{
		return $this->properties;
	}

	/**
	 * @param string $name
	 * @param $default
	 * @return mixed|null
	 */
	public function getProperty(string $name, $default = null)
	{
		return array_key_exists($name, $this->properties)
			? $this->properties[$name]
			: $default
		;
	}

	/**
	 * @param string $name
	 * @param $value
	 * @return $this
	 */
	public function setHeader(string $name, $value): self
	{
		$this->headers[$name] = $value;

		return $this;
	}

	/**
	 * @param array $headers
	 * @return $this
	 */
	public function setHeaders(array $headers): self
	{
		$this->headers = $headers;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getHeaders(): array
	{
		return $this->headers;
	}

	/**
	 * @param string $name
	 * @param $default
	 * @return mixed|null
	 */
	public function getHeader(string $name, $default = null)
	{
		return array_key_exists($name, $this->headers)
			? $this->headers[$name]
			: $default
		;
	}

	/**
	 * @param string|null $routingKey
	 * @return $this
	 */
	public function setRoutingKey(string $routingKey = null): self
	{
		$this->setHeader('routingKey', $routingKey);

		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getRoutingKey(): ?string
	{
		return $this->getHeader('routingKey');
	}
}