<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2024 Bitrix
 */

namespace Bitrix\Main\Web\Http;

use Bitrix\Main\Web\HttpHeaders;

class ResponseBuilder implements ResponseBuilderInterface
{
	/**
	 * @inheritdoc
	 */
	public function createFromString(string $response): Response
	{
		$headers = HttpHeaders::createFromString($response);
		$body = $this->createBody();

		if (strtolower($headers->get('Transfer-Encoding') ?? '') == 'chunked')
		{
			$body = new DechunkStream($body);
		}

		if (strtolower($headers->get('Content-Encoding') ?? '') == 'gzip')
		{
			$body = new InflateStream($body);
		}

		return new Response($headers->getStatus(), $headers->getHeaders(), $body, $headers->getVersion(), $headers->getReasonPhrase());
	}

	protected function createBody(): Stream
	{
		return new Stream('php://temp', 'r+');
	}
}
