<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2025 Bitrix
 */

namespace Bitrix\Main\Web\Http\Socket;

use Bitrix\Main\Web;
use Bitrix\Main\Web\Http;
use Bitrix\Main\Web\IpAddress;
use Bitrix\Main\Web\HttpDebug;
use Bitrix\Main\Diag\StopWatch;
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

	protected ?StopWatch $connectTimer = null;
	protected ?StopWatch $handshakeTimer = null;
	protected ?StopWatch $requestTimer = null;
	protected ?StopWatch $totalTimer = null;

	/**
	 * @param RequestInterface $request
	 * @param Http\ResponseBuilderInterface $responseBuilder
	 * @param array $options
	 */
	public function __construct(RequestInterface $request, Http\ResponseBuilderInterface $responseBuilder, array $options = [])
	{
		parent::__construct($request, $responseBuilder, $options);

		if (!empty($options['proxyHost']))
		{
			$this->useProxy = true;
		}

		$this->socket = $this->createSocket($options);
	}

	/**
	 * Processes the given promise. The promise can be left in the pending state, fulfilled or rejected.
	 *
	 * @param Http\Promise | null $promise
	 * @return void
	 */
	public function process(?Http\Promise $promise = null)
	{
		$request = $this->request;
		$uri = $request->getUri();
		$fetchBody = true;

		try
		{
			switch ($this->state)
			{
				case self::PENDING:
					$this->initTimers();

					$logUri = new Web\Uri((string)$uri);
					$logUri->convertToUnicode();

					$this->log("***CONNECT to {address} for URI {uri}\n", Web\HttpDebug::CONNECT, ['address' => $this->socket->getAddress(), 'uri' => $logUri]);

					// this is a new job - should connect asynchronously
					try
					{
						$this->socket->connect();
					}
					catch (\RuntimeException $e)
					{
						throw new Http\NetworkException($request, $e->getMessage());
					}

					$this->connectTimer?->stop();

					$this->state = self::CONNECTED;

					break;

				case self::CONNECTED:
				case self::CONNECT_RECEIVED:
					if ($this->state === self::CONNECTED && $this->useProxy && $uri->getScheme() === 'https')
					{
						// implement CONNECT method for https connections via proxy
						$this->sendConnect();

						$this->state = self::CONNECT_SENT;
					}
					else
					{
						// enable ssl before sending request headers
						if ($uri->getScheme() === 'https')
						{
							if ($this->async)
							{
								$this->socket->setBlocking();
							}

							if ($this->socket->enableCrypto() === false)
							{
								throw new Http\NetworkException($request, 'Error establishing an SSL connection.');
							}

							$this->handshakeTimer?->stop();
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
						$this->log("<<<CONNECT\n{headers}\n", Web\HttpDebug::REQUEST_HEADERS, ['headers' => $this->responseHeaders]);

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
						$this->requestTimer?->stop();

						$this->state = self::BODY_SENT;
					}

					break;

				case self::BODY_SENT:
					// request is sent now - switching to reading
					if ($this->receiveHeaders())
					{
						// all headers received
						$this->log("\n<<<RESPONSE\n{headers}\n", Web\HttpDebug::RESPONSE_HEADERS, ['headers' => $this->responseHeaders]);

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
							// we don't want a body, just fulfil a promise with response headers
							$this->state = self::BODY_RECEIVED;
						}
					}

					break;

				case self::HEADERS_RECEIVED:
					// receiving a response body
					if ($this->receiveBody())
					{
						// have read all the body
						$this->state = self::BODY_RECEIVED;
					}

					break;
			}

			if ($this->state == self::BODY_RECEIVED)
			{
				$this->socket->close();

				if ($fetchBody)
				{
					if ($this->debugLevel & Web\HttpDebug::RESPONSE_BODY)
					{
						$this->log($this->response->getBody(), Web\HttpDebug::RESPONSE_BODY);
					}

					// need to ajust the response headers (PSR-18)
					$this->response->adjustHeaders();
				}

				$this->totalTimer?->stop();

				$this->logDiagnostics();

				// we have a result!
				$promise?->fulfill($this->response);
			}
		}
		catch (Http\ClientException $exception)
		{
			$this->socket->close();

			$promise?->reject($exception);

			$this->getLogger()?->error($exception->getMessage() . "\n");

			if ($promise === null)
			{
				throw $exception;
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
			throw new Http\NetworkException($this->request, $error . ' ' . $e->getMessage());
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

		$this->log(">>>CONNECT\n{headers}", Web\HttpDebug::REQUEST_HEADERS, ['headers' => $requestHeaders]);

		if ($this->async)
		{
			// blocking is critical for headers
			$this->socket->setBlocking();
		}

		$this->write($requestHeaders, 'Error sending CONNECT to proxy.');

		if ($this->async)
		{
			$this->socket->setBlocking(false);
		}
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

		$this->log(">>>REQUEST\n{headers}", Web\HttpDebug::REQUEST_HEADERS, ['headers' => $requestHeaders]);

		if ($this->async)
		{
			// blocking is critical for headers
			$this->socket->setBlocking();
		}

		$this->write($requestHeaders, 'Error sending the message headers.');

		if ($this->async)
		{
			$this->socket->setBlocking(false);
		}
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
			try
			{
				$line = $this->socket->gets();
			}
			catch (\RuntimeException $e)
			{
				throw new Http\NetworkException($this->request, $e->getMessage());
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

		if ($length !== null && $length <= 0)
		{
			// nothing to read
			return true;
		}

		while (!$this->socket->eof())
		{
			if ($length !== null)
			{
				$bufLength = min($length, self::BUF_READ_LEN);
			}
			else
			{
				$bufLength = self::BUF_READ_LEN;
			}

			try
			{
				$buf = $this->socket->read($bufLength);
			}
			catch (\RuntimeException)
			{
				throw new Http\NetworkException($request, 'Stream reading error.');
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
			catch (\RuntimeException)
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
				'async' => $options['async'] ?? null,
			],
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

	protected function initTimers(): void
	{
		// $this->debugLevel can be set from Diag\Logger::create()
		if ($this->getLogger())
		{
			if ($this->debugLevel & HttpDebug::DIAGNOSTICS)
			{
				$this->connectTimer = (new StopWatch())->start();
				if ($this->request->getUri()->getScheme() === 'https')
				{
					$this->handshakeTimer = (new StopWatch())->start();
				}
				$this->requestTimer = (new StopWatch())->start();
				$this->totalTimer = (new StopWatch())->start();
			}
		}
	}

	protected function getDiagnostics(): array
	{
		return [
			'connect' => $this->connectTimer?->get() ?? 0.0,
			'handshake' => $this->handshakeTimer?->get() ?? 0.0,
			'request' => $this->requestTimer?->get() ?? 0.0,
			'total' => $this->totalTimer?->get() ?? 0.0,
		];
	}

	public function execute(): Http\Response
	{
		while ($this->state != self::BODY_RECEIVED)
		{
			// request in a blocking way
			$this->process();
		}

		return $this->response;
	}
}
