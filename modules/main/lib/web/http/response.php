<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2022 Bitrix
 */

namespace Bitrix\Main\Web\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class Response extends Message implements ResponseInterface
{
	protected $statusCode = 0;
	protected $reasonPhrase = '';

	public function __construct(int $statusCode, array $headers = null, StreamInterface $body = null, string $version = null, string $reasonPhrase = '')
	{
		parent::__construct($headers, $body, $version);

		$this->statusCode = $statusCode;
		$this->reasonPhrase = $reasonPhrase;
	}

	/**
	 * @inheritdoc
	 */
	public function getStatusCode()
	{
		return $this->statusCode;
	}

	/**
	 * @inheritdoc
	 */
	public function withStatus($code, $reasonPhrase = '')
	{
		$new = clone $this;
		$new->statusCode = (int)$code;
		$new->reasonPhrase = $reasonPhrase;

		return $new;
	}

	/**
	 * @inheritdoc
	 */
	public function getReasonPhrase()
	{
		return $this->reasonPhrase;
	}

	/**
	 * Adjusts the response headers after dechunking and decompressing the body.
	 * @return void
	 */
	public function adjustHeaders(): void
	{
		// If a Client chooses to decompress the message body then it MUST also remove the Content-Encoding header and adjust the Content-Length header
		if (strtolower($this->headers->get('Content-Encoding') ?? '') == 'gzip')
		{
			$this->headers->delete('Content-Encoding');

			if ($this->headers->has('Content-Length'))
			{
				$size = $this->body->getSize();
				if ($size !== null)
				{
					$this->headers->set('Content-Length', $size);
				}
				else
				{
					$this->headers->delete('Content-Length');
				}
			}
		}

		// Already dechunked
		if (strtolower($this->headers->get('Transfer-Encoding') ?? '') == 'chunked')
		{
			$this->headers->delete('Transfer-Encoding');
		}
	}
}
