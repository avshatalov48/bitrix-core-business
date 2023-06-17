<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2023 Bitrix
 */

namespace Bitrix\Main\Web\Http\Socket;

use Bitrix\Main\Web;
use Bitrix\Main\Web\Http;
use Bitrix\Main\Web\IpAddress;
use Psr\Http\Message\RequestInterface;

class Handler extends Http\Handler
{
	protected const BUF_BODY_LEN = 131072;
	protected const BUF_READ_LEN = 32768;

	public const PENDING = 0;
	public const CONNECTED = 1;
	public const HEADERS_SENT = 2;
	public const BODY_SENT = 3;
	public const HEADERS_RECEIVED = 4;
	public const BODY_RECEIVED = 5;
	public const CONNECT_SENT = 6;
	public const CONNECT_RECEIVED = 7;

	protected Stream $socket;
	protected bool $useProxy = false;
	protected int $state = self::PENDING;
	protected string $requestBodyPart = '';

	/**
	 * @param RequestInterface $request
	 * @param Http\ResponseBuilder $responseBuilder
	 * @param array $options
	 */
	public function __construct(RequestInterface $request, Http\ResponseBuilder $responseBuilder, array $options = [])
	{
		Http\Handler::__construct($request, $responseBuilder, $options);

		if (isset($options['proxyHost']) && $options['proxyHost'] != '')
		{
			$this->useProxy = true;
		}

		$this->socket = $this->createSocket($options);
	}

	/**
	 * Processes the given promise. The promise can be left in the pending state, fulfilled or rejected.
	 *
	 * @param Http\Promise $promise
	 * @return void
	 */
	public function process(Http\Promise $promise)
	{
		$request = $this->request;

		try
		{
			switch ($this->state)
			{
				case self::PENDING:
					// this is a new job - should connect asynchronously
					try
					{
						$this->socket->connect();
					}
					catch (\RuntimeException $e)
					{
						throw new Http\NetworkException($request, $e->getMessage());
					}

					$this->state = self::CONNECTED;
					break;

				case self::CONNECTED:
				case self::CONNECT_RECEIVED:
					if ($this->state === self::CONNECTED && $this->useProxy && $request->getUri()->getScheme() === 'https')
					{
						// implement CONNECT method for https connections via proxy
						$this->sendConnect();

						$this->state = self::CONNECT_SENT;
					}
					else
					{
						// enable ssl before sending request headers
						if ($request->getUri()->getScheme() === 'https')
						{
							$this->socket->setBlocking();

							if ($this->socket->enableCrypto() === false)
							{
								throw new Http\NetworkException($request, 'Error establishing an SSL connection.');
							}
						}

						// the socket is ready - can write headers
						$this->sendHeaders();

						// prepare the body for sending
						$body = $request->getBody();
						if ($body->isSeekable())
						{
							$body->rewind();
						}

						$this->state = self::HEADERS_SENT;
					}
					break;

				case self::CONNECT_SENT:
					if ($this->receiveHeaders())
					{
						$this->log("<<<CONNECT\n" . $this->responseHeaders . "\n", Web\HttpDebug::REQUEST_HEADERS);

						// response to CONNECT from the proxy
						$headers = Web\HttpHeaders::createFromString($this->responseHeaders);

						if (($status = $headers->getStatus()) >= 200 && $status < 300)
						{
							$this->responseHeaders = '';

							$this->state = self::CONNECT_RECEIVED;
						}
						else
						{
							throw new Http\NetworkException($request, 'Error receiving the CONNECT response from the proxy: ' . $headers->getStatus() . ' ' . $headers->getReasonPhrase());
						}
					}
					break;

				case self::HEADERS_SENT:
					// it's time to send the request body asynchronously
					if ($this->sendBody())
					{
						// sent all the body
						$this->state = self::BODY_SENT;
					}
					break;

				case self::BODY_SENT:
					// request is sent now - switching to reading
					if ($this->receiveHeaders())
					{
						// all headers received
						$this->log("\n<<<RESPONSE\n" . $this->responseHeaders . "\n", Web\HttpDebug::RESPONSE_HEADERS);

						// build the response for the next stage
						$this->response = $this->responseBuilder->createFromString($this->responseHeaders);

						$fetchBody = $this->waitResponse;

						if ($this->shouldFetchBody !== null)
						{
							$fetchBody = call_user_func($this->shouldFetchBody, $this->response, $request);
						}

						if ($fetchBody)
						{
							$this->state = self::HEADERS_RECEIVED;
						}
						else
						{
							$this->socket->close();

							// we don't want a body, just fulfil a promise with response headers
							$promise->fulfill($this->response);

							$this->state = self::BODY_RECEIVED;
						}
					}
					break;

				case self::HEADERS_RECEIVED:
					// receiving a response body
					if ($this->receiveBody())
					{
						// have read all the body
						$this->socket->close();

						if ($this->debugLevel & Web\HttpDebug::RESPONSE_BODY)
						{
							$this->log($this->response->getBody(), Web\HttpDebug::RESPONSE_BODY);
						}

						// need to ajust the response headers (PSR-18)
						$this->response->adjustHeaders();

						// we have a result!
						$promise->fulfill($this->response);

						$this->state = self::BODY_RECEIVED;
					}
					break;
			}
		}
		catch (Http\ClientException $exception)
		{
			$this->socket->close();

			$promise->reject($exception);

			if ($logger = $this->getLogger())
			{
				$logger->error($exception->getMessage());
			}
		}
	}

	protected function write(string $data, string $error)
	{
		try
		{
			$result = $this->socket->write($data);
		}
		catch (\RuntimeException $e)
		{
			throw new Http\NetworkException($this->request, $error);
		}

		if ($this->socket->timedOut())
		{
			throw new Http\NetworkException($this->request, 'Stream writing timeout has been reached.');
		}

		return $result;
	}

	protected function sendConnect(): void
	{
		$request = $this->request;
		$uri = $request->getUri();
		$host = $uri->getHost();

		$requestHeaders = 'CONNECT ' . $host . ':' . $uri->getPort() . ' HTTP/1.1' . "\r\n"
			. 'Host: ' . $host . "\r\n"
		;

		if ($request->hasHeader('Proxy-Authorization'))
		{
			$requestHeaders .= 'Proxy-Authorization' . ': ' . $request->getHeaderLine('Proxy-Authorization') . "\r\n";
			$this->request = $request->withoutHeader('Proxy-Authorization');
		}

		$requestHeaders .= "\r\n";

		$this->log(">>>CONNECT\n" . $requestHeaders, Web\HttpDebug::REQUEST_HEADERS);

		// blocking is critical for headers
		$this->socket->setBlocking();
		$this->write($requestHeaders, 'Error sending CONNECT to proxy.');
		$this->socket->setBlocking(false);
	}

	protected function sendHeaders(): void
	{
		$request = $this->request;
		$uri = $request->getUri();

		// Full URI for HTTP proxies
		$target = ($this->useProxy && $uri->getScheme() === 'http' ? (string)$uri : $request->getRequestTarget());

		$requestHeaders = $request->getMethod() . ' ' . $target . ' HTTP/' . $request->getProtocolVersion() . "\r\n";

		foreach ($request->getHeaders() as $name => $values)
		{
			foreach ($values as $value)
			{
				$requestHeaders .= $name . ': ' . $value . "\r\n";
			}
		}

		$requestHeaders .= "\r\n";

		$this->log(">>>REQUEST\n" . $requestHeaders, Web\HttpDebug::REQUEST_HEADERS);

		// blocking is critical for headers
		$this->socket->setBlocking();

		$this->write($requestHeaders, 'Error sending the message headers.');

		$this->socket->setBlocking(false);
	}

	protected function sendBody(): bool
	{
		$request = $this->request;
		$body = $request->getBody();

		if (!$body->eof() || $this->requestBodyPart !== '')
		{
			if (!$body->eof() && strlen($this->requestBodyPart) < self::BUF_BODY_LEN)
			{
				$part = $body->read(self::BUF_BODY_LEN);
				$this->requestBodyPart .= $part;
				$this->log($part, Web\HttpDebug::REQUEST_BODY);
			}

			$result = $this->write($this->requestBodyPart, 'Error sending the message body.');

			$this->requestBodyPart = substr($this->requestBodyPart, $result);
		}

		return ($body->eof() && $this->requestBodyPart === '');
	}

	protected function receiveHeaders(): bool
	{
		while (!$this->socket->eof())
		{
			$line = $this->socket->gets();

			if ($this->socket->timedOut())
			{
				throw new Http\NetworkException($this->request, 'Stream reading timeout has been reached.');
			}

			if ($line === false)
			{
				// no data in the socket or error(?)
				return false;
			}

			if ($line === "\r\n")
			{
				// got all headers
				return true;
			}

			$this->responseHeaders .= $line;
		}

		if ($this->responseHeaders === '')
		{
			throw new Http\NetworkException($this->request, 'Empty response from the server.');
		}

		return true;
	}

	protected function receiveBody(): bool
	{
		$request = $this->request;
		$headers = $this->response->getHeadersCollection();
		$body = $this->response->getBody();

		$length = $headers->get('Content-Length');

		while (!$this->socket->eof())
		{
			try
			{
				$buf = $this->socket->read(self::BUF_READ_LEN);
			}
			catch (\RuntimeException $e)
			{
				throw new Http\NetworkException($request, 'Stream reading error.');
			}

			if ($this->socket->timedOut())
			{
				throw new Http\NetworkException($request, 'Stream reading timeout has been reached.');
			}

			if ($buf === '')
			{
				// no data in the stream yet
				return false;
			}

			try
			{
				$body->write($buf);
			}
			catch (\RuntimeException $e)
			{
				throw new Http\NetworkException($request, 'Error writing to response body stream.');
			}

			if ($this->bodyLengthMax > 0 && $body->getSize() > $this->bodyLengthMax)
			{
				throw new Http\NetworkException($request, 'Maximum content length has been reached. Breaking reading.');
			}

			if ($length !== null)
			{
				$length -= strlen($buf);
				if ($length <= 0)
				{
					// have read all the body
					return true;
				}
			}
		}

		return true;
	}

	protected function createSocket(array $options): Stream
	{
		$proxyHost = (string)($options['proxyHost'] ?? '');
		$proxyPort = (int)($options['proxyPort'] ?? 80);
		$contextOptions = $options['contextOptions'] ?? [];

		$uri = $this->request->getUri();

		if ($proxyHost != '')
		{
			$host = $proxyHost;
			$port = $proxyPort;

			// set original host to match a sertificate for proxy tunneling
			$contextOptions['ssl']['peer_name'] = $uri->getHost();
		}
		else
		{
			$host = $uri->getHost();
			$port = $uri->getPort();

			if (isset($options['effectiveIp']) && $options['effectiveIp'] instanceof IpAddress)
			{
				// set original host to match a sertificate
				$contextOptions['ssl']['peer_name'] = $host;

				// resolved in HttpClient if private IPs were disabled
				$host = $options['effectiveIp']->get();
			}
		}

		$socket = new Stream(
			'tcp://' . $host . ':' . $port,
			[
				'socketTimeout' => $options['socketTimeout'] ?? null,
				'streamTimeout' => $options['streamTimeout'] ?? null,
				'contextOptions' => $contextOptions,
			]
		);

		return $socket;
	}

	public function getState(): int
	{
		return $this->state;
	}

	/**
	 * Returns the associated socket.
	 *
	 * @return Stream
	 */
	public function getSocket(): Stream
	{
		return $this->socket;
	}
}
