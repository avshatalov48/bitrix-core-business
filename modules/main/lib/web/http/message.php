<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2022 Bitrix
 */

namespace Bitrix\Main\Web\Http;

use Bitrix\Main\Web\HttpHeaders;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

class Message implements MessageInterface
{
	protected string $protocolVersion = '1.1';
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
			$this->protocolVersion = $version;
		}

		$this->body = $body ?? new Stream('php://temp', 'r+');
	}

	/**
	 * @inheritdoc
	 */
	public function getProtocolVersion()
	{
		return $this->protocolVersion;
	}

	/**
	 * @inheritdoc
	 */
	public function withProtocolVersion($version)
	{
		if ($this->protocolVersion == $version)
		{
			return $this;
		}

		$new = clone $this;
		$new->protocolVersion = $version;

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
	public function getHeaders()
	{
		return $this->headers->getHeaders();
	}

	/**
	 * @inheritdoc
	 */
	public function hasHeader($name)
	{
		return $this->headers->has($name);
	}

	/**
	 * @inheritdoc
	 */
	public function getHeader($name)
	{
		return $this->headers->get($name, true) ?? [];
	}

	/**
	 * @inheritdoc
	 */
	public function getHeaderLine($name)
	{
		return implode(',', $this->getHeader($name));
	}

	/**
	 * @inheritdoc
	 */
	public function withHeader($name, $value)
	{
		$new = clone $this;
		$new->headers->set($name, $value);

		return $new;
	}

	/**
	 * @inheritdoc
	 */
	public function withAddedHeader($name, $value)
	{
		$new = clone $this;
		$new->headers->add($name, $value);

		return $new;
	}

	/**
	 * @inheritdoc
	 */
	public function withoutHeader($name)
	{
		$new = clone $this;
		$new->headers->delete($name);

		return $new;
	}

	/**
	 * @inheritdoc
	 */
	public function getBody()
	{
		return $this->body;
	}

	/**
	 * @inheritdoc
	 */
	public function withBody(StreamInterface $body)
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
