<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2025 Bitrix
 */

namespace Bitrix\Main\Web\Http\Curl;

use Bitrix\Main\Web\Http;
use Bitrix\Main\Web\IpAddress;
use Psr\Http\Message\RequestInterface;
use Bitrix\Main\Web\HttpDebug;
use Bitrix\Main\Web\Uri;

class Handler extends Http\Handler
{
	protected static ?\CurlHandle $sharedHandle = null;

	protected \CurlHandle $handle;
	protected $logFileHandle;

	/**
	 * @param RequestInterface $request
	 * @param Http\ResponseBuilderInterface $responseBuilder
	 * @param array $options
	 */
	public function __construct(RequestInterface $request, Http\ResponseBuilderInterface $responseBuilder, array $options = [])
	{
		parent::__construct($request, $responseBuilder, $options);

		if ($this->async)
		{
			// multihandle
			$this->handle = curl_init();
		}
		else
		{
			// one shared handle for sync requests
			if (static::$sharedHandle === null)
			{
				static::$sharedHandle = curl_init();
			}
			$this->handle = static::$sharedHandle;
		}

		$this->setOptions($options);
	}

	public function __destruct()
	{
		if ($this->async)
		{
			curl_close($this->handle);
		}

		if (is_resource($this->logFileHandle))
		{
			fclose($this->logFileHandle);
		}
	}

	protected function setOptions(array $options): void
	{
		$request = $this->request;
		$uri = $request->getUri();

		$curlOptions = [
			CURLOPT_URL => (string)$uri,
			CURLOPT_HEADER => false,
			CURLOPT_RETURNTRANSFER => false,
			CURLOPT_FOLLOWLOCATION => false,
			CURLOPT_HTTP_VERSION => ($request->getProtocolVersion() === '1.1' ? CURL_HTTP_VERSION_1_1 : CURL_HTTP_VERSION_1_0),
			CURLOPT_CONNECTTIMEOUT => (int)($options['socketTimeout'] ?? 30),
			CURLOPT_LOW_SPEED_TIME => (int)($options['streamTimeout'] ?? 60),
			CURLOPT_LOW_SPEED_LIMIT => 1, // bytes/sec
			CURLOPT_HTTPHEADER => $this->buildHeaders(),
		];

		if (isset($options['contextOptions']['ssl']['verify_peer']))
		{
			$curlOptions[CURLOPT_SSL_VERIFYPEER] = (bool)$options['contextOptions']['ssl']['verify_peer'];
		}
		if (isset($options['contextOptions']['ssl']['verify_peer_name']))
		{
			$curlOptions[CURLOPT_SSL_VERIFYHOST] = $options['contextOptions']['ssl']['verify_peer_name'] ? 2 : 0;
		}

		$method = $request->getMethod();
		if ($method === 'HEAD')
		{
			$curlOptions[CURLOPT_NOBODY] = true;
		}
		else
		{
			$curlOptions[CURLOPT_CUSTOMREQUEST] = $method;
		}

		if (isset($options['effectiveIp']) && $options['effectiveIp'] instanceof IpAddress)
		{
			//resolved in HttpClient if private IPs were disabled
			$curlOptions[CURLOPT_RESOLVE] = [$uri->getHost() . ':' . $uri->getPort() . ':' . $options['effectiveIp']];
		}

		if (isset($options['proxyHost']))
		{
			$curlOptions[CURLOPT_PROXY] = (string)$options['proxyHost'];

			if (isset($options['proxyPort']))
			{
				$curlOptions[CURLOPT_PROXYPORT] = (int)$options['proxyPort'];
			}
		}

		if ($method != 'GET' && $method != 'HEAD' && $method != 'TRACE')
		{
			$body = $request->getBody();
			$size = $body->getSize();

			if ($size !== 0)
			{
				if ($body->isSeekable())
				{
					$body->rewind();
				}

				$curlOptions[CURLOPT_UPLOAD] = true;

				if ($size !== null)
				{
					$curlOptions[CURLOPT_INFILESIZE] = $size;
				}

				$curlOptions[CURLOPT_READFUNCTION] = [$this, 'readRequestBody'];
			}
		}

		$curlOptions[CURLOPT_HEADERFUNCTION] = [$this, 'receiveHeaders'];

		$curlOptions[CURLOPT_WRITEFUNCTION] = [$this, 'receiveBody'];

		if (!empty($options['curlLogFile']))
		{
			$this->logFileHandle = fopen($options['curlLogFile'], 'a+');
			$curlOptions[CURLOPT_STDERR] = $this->logFileHandle;
			$curlOptions[CURLOPT_VERBOSE] = true;
		}

		if (!$this->async)
		{
			// shared handle
			curl_reset($this->handle);
		}
		curl_setopt_array($this->handle, $curlOptions);
	}

	/**
	 * @internal Callback
	 */
	public function readRequestBody($handle, $resource, $length)
	{
		$part = $this->request->getBody()->read($length);

		$this->log($part, HttpDebug::REQUEST_BODY);

		return $part;
	}

	/**
	 * @internal Callback
	 */
	public function receiveHeaders($handle, $data)
	{
		if ($data === "\r\n")
		{
			// got all headers
			$this->log("\n<<<RESPONSE\n{headers}\n", HttpDebug::RESPONSE_HEADERS, ['headers' => $this->responseHeaders]);

			// build the response for the next stage
			$this->response = $this->responseBuilder->createFromString($this->responseHeaders);

			$fetchBody = $this->waitResponse;

			if ($this->shouldFetchBody !== null)
			{
				$fetchBody = call_user_func($this->shouldFetchBody, $this->response, $this->request);
			}

			if (!$fetchBody)
			{
				// this is not an error really
				throw new SkipBodyException();
			}
		}
		else
		{
			$this->responseHeaders .= $data;
		}

		return strlen($data);
	}

	/**
	 * @internal Callback
	 */
	public function receiveBody($handle, $data)
	{
		$body = $this->response->getBody();

		try
		{
			$result = $body->write($data);
		}
		catch (\RuntimeException)
		{
			return false;
		}

		if ($this->bodyLengthMax > 0 && $body->getSize() > $this->bodyLengthMax)
		{
			return false;
		}

		return $result;
	}

	protected function buildHeaders(): array
	{
		$headers = [];

		foreach ($this->request->getHeaders() as $name => $values)
		{
			foreach ($values as $value)
			{
				$headers[] = $name . ': ' . $value;
			}
		}

		if ($this->getLogger() && $this->debugLevel)
		{
			$logUri = new Uri((string)$this->request->getUri());
			$logUri->convertToUnicode();

			$this->log("***CONNECT to {uri}\n", HttpDebug::CONNECT, ['uri' => $logUri]);

			$request = $this->request->getMethod() . ' ' . $this->request->getRequestTarget() . ' HTTP/' . $this->request->getProtocolVersion() . "\n"
				. implode("\n", $headers) . "\n";

			$this->log(">>>REQUEST\n{request}", HttpDebug::REQUEST_HEADERS, ['request' => $request]);
		}

		return $headers;
	}

	/**
	 * @return \CurlHandle
	 */
	public function getHandle(): \CurlHandle
	{
		return $this->handle;
	}

	protected function getDiagnostics(): array
	{
		$stat = curl_getinfo($this->handle);

		$handshake = 0.0;
		if ($this->request->getUri()->getScheme() === 'https')
		{
			$handshake = round((float)curl_getinfo($this->handle, CURLINFO_APPCONNECT_TIME), 6);
		}

		return [
			'connect' => round($stat['connect_time'] ?? 0.0, 6),
			'handshake' => $handshake,
			'request' => round($stat['pretransfer_time'] ?? 0.0, 6),
			'total' => round($stat['total_time'] ?? 0.0, 6),
		];
	}

	public function execute(): Http\Response
	{
		$fetchBody = true;
		try
		{
			$status = curl_exec($this->handle);
		}
		catch (SkipBodyException)
		{
			$fetchBody = false;
		}

		if ($status !== false)
		{
			if ($fetchBody)
			{
				if ($this->debugLevel & HttpDebug::RESPONSE_BODY)
				{
					$this->log($this->response->getBody(), HttpDebug::RESPONSE_BODY);
				}

				// need to ajust the response headers (PSR-18)
				$this->response->adjustHeaders();
			}

			$this->logDiagnostics();

			return $this->response;
		}
		else
		{
			$error = curl_error($this->handle);

			$this->getLogger()?->error($error . "\n");

			throw new Http\NetworkException($this->request, $error);
		}
	}
}
