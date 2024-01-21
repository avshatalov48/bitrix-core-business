<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2023 Bitrix
 */

namespace Bitrix\Main\Web\Http;

use Bitrix\Main\Web\HttpHeaders;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

class Message implements MessageInterface
{
	protected HttpHeaders $headers;
	protected StreamInterface $body;

	/**
	 * @param array | null $headers
	 * @param StreamInterface | null  $body
	 * @param string | null $version
	 */
	public function __construct(array $headers = null, StreamInterface $body = null, string $version = null)
	{
		$this->headers = new HttpHeaders($headers);

		if ($version !== null)
		{
			$this->headers->setVersion($version);
		}

		$this->body = $body ?? new Stream('php://temp', 'r+');
	}

	/**
	 * @inheritdoc
	 */
	public function getProtocolVersion(): string
	{
		return $this->headers->getVersion();
	}

	/**
	 * @inheritdoc
	 */
	public function withProtocolVersion(string $version): MessageInterface
	{
		if ($this->getProtocolVersion() === $version)
		{
			return $this;
		}

		$new = clone $this;
		$new->headers->setVersion($version);

		return $new;
	}

	/**
	 * @return HttpHeaders
	 */
	public function getHeadersCollection(): HttpHeaders
	{
		return $this->headers;
	}

	/**
	 * @inheritdoc
	 */
	public function getHeaders(): array
	{
		return $this->headers->getHeaders();
	}

	/**
	 * @inheritdoc
	 */
	public function hasHeader(string $name): bool
	{
		return $this->headers->has($name);
	}

	/**
	 * @inheritdoc
	 */
	public function getHeader(string $name): array
	{
		return $this->headers->get($name, true) ?? [];
	}

	/**
	 * @inheritdoc
	 */
	public function getHeaderLine(string $name): string
	{
		return implode(',', $this->getHeader($name));
	}

	/**
	 * @inheritdoc
	 */
	public function withHeader(string $name, $value): MessageInterface
	{
		$new = clone $this;
		$new->headers->set($name, $value);

		return $new;
	}

	/**
	 * @inheritdoc
	 */
	public function withAddedHeader(string $name, $value): MessageInterface
	{
		$new = clone $this;
		$new->headers->add($name, $value);

		return $new;
	}

	/**
	 * @inheritdoc
	 */
	public function withoutHeader(string $name): MessageInterface
	{
		$new = clone $this;
		$new->headers->delete($name);

		return $new;
	}

	/**
	 * @inheritdoc
	 */
	public function getBody(): StreamInterface
	{
		return $this->body;
	}

	/**
	 * @inheritdoc
	 */
	public function withBody(StreamInterface $body): MessageInterface
	{
		$new = clone $this;
		$new->body = $body;

		return $new;
	}

	public function __clone()
	{
		$this->headers = clone $this->headers;
		$this->body = clone $this->body;
	}
}
