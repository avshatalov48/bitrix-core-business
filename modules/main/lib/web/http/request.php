<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2022 Bitrix
 */

namespace Bitrix\Main\Web\Http;

use Psr\Http\Message\UriInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;

class Request extends Message implements RequestInterface
{
	protected $requestTarget;
	protected UriInterface $uri;
	protected $method;

	public function __construct(string $method, UriInterface $uri, array $headers = null, StreamInterface $body = null, string $version = null)
	{
		parent::__construct($headers, $body, $version);

		$this->method = $method;
		$this->uri = $uri;

		// PSR-7: During construction, implementations MUST attempt to set the Host header from a provided URI if no Host header is provided.
		if ($uri->getHost() != '' && !$this->hasHeader('Host'))
		{
			$this->headers->set('Host', $uri->getHost());
		}
	}

	/**
	 * @inheritdoc
	 */
	public function getRequestTarget()
	{
		if ($this->requestTarget !== null)
		{
			return $this->requestTarget;
		}

		$target = $this->uri->getPath();

		if ($target == '')
		{
			$target = '/';
		}

		$query = $this->uri->getQuery();

		if ($query != '')
		{
			$target .= '?' . $query;
		}

		return $target;
	}

	/**
	 * @inheritdoc
	 */
	public function withRequestTarget($requestTarget)
	{
		$new = clone $this;
		$new->requestTarget = $requestTarget;

		return $new;
	}

	/**
	 * @inheritdoc
	 */
	public function getMethod()
	{
		return $this->method;
	}

	/**
	 * @inheritdoc
	 */
	public function withMethod($method)
	{
		$new = clone $this;
		$new->method = $method;

		return $new;
	}

	/**
	 * @inheritdoc
	 */
	public function getUri()
	{
		return $this->uri;
	}

	/**
	 * @inheritdoc
	 */
	public function withUri(UriInterface $uri, $preserveHost = false)
	{
		$new = clone $this;
		$new->uri = $uri;

		$newHost = $uri->getHost();

		if ($newHost != '')
		{
			if (!$preserveHost || !$new->hasHeader('Host'))
			{
				$new->headers->set('Host', $newHost);
			}
		}

		return $new;
	}

	public function __clone()
	{
		$this->uri = clone $this->uri;
		parent::__clone();
	}
}
