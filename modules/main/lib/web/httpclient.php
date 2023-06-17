<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2023 Bitrix
 */

namespace Bitrix\Main\Web;

use Bitrix\Main\IO;
use Bitrix\Main\Config\Configuration;
use Bitrix\Main\ArgumentException;
use Psr\Log;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\NetworkExceptionInterface;
use Http\Promise\Promise as PromiseInterface;

class HttpClient implements Log\LoggerAwareInterface, ClientInterface, Http\DebugInterface
{
	use Log\LoggerAwareTrait;
	use Http\DebugInterfaceTrait;

	const HTTP_1_0 = '1.0';
	const HTTP_1_1 = '1.1';

	const HTTP_GET = 'GET';
	const HTTP_POST = 'POST';
	const HTTP_PUT = 'PUT';
	const HTTP_HEAD = 'HEAD';
	const HTTP_PATCH = 'PATCH';
	const HTTP_DELETE = 'DELETE';
	const HTTP_OPTIONS = 'OPTIONS';

	const DEFAULT_SOCKET_TIMEOUT = 30;
	const DEFAULT_STREAM_TIMEOUT = 60;
	const DEFAULT_STREAM_TIMEOUT_NO_WAIT = 1;

	protected $proxyHost = '';
	protected $proxyPort = 80;
	protected $proxyUser = '';
	protected $proxyPassword = '';
	protected $socketTimeout = self::DEFAULT_SOCKET_TIMEOUT;
	protected $streamTimeout = self::DEFAULT_STREAM_TIMEOUT;
	protected $waitResponse = true;
	protected $redirect = true;
	protected $redirectMax = 5;
	protected $redirectCount = 0;
	protected $compress = false;
	protected $version = self::HTTP_1_1;
	protected $requestCharset = '';
	protected $sslVerify = true;
	protected $bodyLengthMax = 0;
	protected $privateIp = true;
	protected $contextOptions = [];
	protected $outputStream = null;
	protected $useCurl = false;
	protected $curlLogFile = null;
	protected $shouldFetchBody = null;

	protected HttpHeaders $headers;
	protected ?Http\Request $request = null;
	protected ?Http\Response $response = null;
	protected ?Http\Queue $queue = null;
	protected ?IpAddress $effectiveIp = null;
	protected $effectiveUrl;
	protected $error = [];

	/**
	 * @param array|null $options Optional array with options:
	 *		"redirect" bool Follow redirects (default true).
	 *		"redirectMax" int Maximum number of redirects (default 5).
	 *		"waitResponse" bool Read the body or disconnect just after reading headers (default true).
	 *		"socketTimeout" int Connection timeout in seconds (default 30).
	 *		"streamTimeout" int Stream reading timeout in seconds (default 60 for waitResponse == true and 1 for waitResponse == false).
	 *		"version" string HTTP version (HttpClient::HTTP_1_0, HttpClient::HTTP_1_1) (default "1.1").
	 *		"proxyHost" string Proxy host name/address.
	 *		"proxyPort" int Proxy port number.
	 *		"proxyUser" string Proxy username.
	 *		"proxyPassword" string Proxy password.
	 *		"compress" bool Accept gzip encoding (default false).
	 *		"charset" string Charset for body in POST and PUT.
	 *		"disableSslVerification" bool Pass true to disable ssl check.
	 *		"bodyLengthMax" int Maximum length of the body.
	 *		"privateIp" bool Enable or disable requests to private IPs (default true).
	 *		"debugLevel" int Debug level using HttpDebug::* constants.
	 * 		"cookies" array of cookies for HTTP request.
	 * 		"headers" array of headers for HTTP request.
	 * 		"useCurl" bool Enable CURL (default false).
	 *		"curlLogFile" string Full path to CURL log file.
	 * 	Almost all options can be set separately with setters.
	 */
	public function __construct(array $options = null)
	{
		$this->headers = new HttpHeaders();

		if ($options === null)
		{
			$options = [];
		}

		$defaultOptions = Configuration::getValue('http_client_options');
		if ($defaultOptions !== null)
		{
			$options += $defaultOptions;
		}

		if (!empty($options))
		{
			if (isset($options['redirect']))
			{
				$this->setRedirect($options["redirect"], $options["redirectMax"] ?? null);
			}
			if (isset($options['waitResponse']))
			{
				$this->waitResponse($options['waitResponse']);
			}
			if (isset($options['socketTimeout']))
			{
				$this->setTimeout($options['socketTimeout']);
			}
			if (isset($options['streamTimeout']))
			{
				$this->setStreamTimeout($options['streamTimeout']);
			}
			if (isset($options['version']))
			{
				$this->setVersion($options['version']);
			}
			if (isset($options['proxyHost']))
			{
				$this->setProxy($options['proxyHost'], $options['proxyPort'] ?? null, $options['proxyUser'] ?? null, $options['proxyPassword'] ?? null);
			}
			if (isset($options['compress']))
			{
				$this->setCompress($options['compress']);
			}
			if (isset($options['charset']))
			{
				$this->setCharset($options['charset']);
			}
			if (isset($options['disableSslVerification']) && $options['disableSslVerification'] === true)
			{
				$this->disableSslVerification();
			}
			if (isset($options['bodyLengthMax']))
			{
				$this->setBodyLengthMax($options['bodyLengthMax']);
			}
			if (isset($options['privateIp']))
			{
				$this->setPrivateIp($options['privateIp']);
			}
			if (isset($options['debugLevel']))
			{
				$this->setDebugLevel((int)$options['debugLevel']);
			}
			if (isset($options['cookies']))
			{
				$this->setCookies($options['cookies']);
			}
			if (isset($options['headers']))
			{
				$this->setHeaders($options['headers']);
			}
			if (isset($options['useCurl']))
			{
				$this->useCurl = (bool)$options['useCurl'];
			}
			if (isset($options['curlLogFile']))
			{
				$this->curlLogFile = $options['curlLogFile'];
			}
		}

		if ($this->useCurl && !function_exists('curl_init'))
		{
			$this->useCurl = false;
		}
	}

	/**
	 * Performs GET request.
	 *
	 * @param string $url Absolute URI eg. "http://user:pass @ host:port/path/?query".
	 * @return string|bool Response entity string or false on error. Note, it's empty string if outputStream is set.
	 */
	public function get($url)
	{
		if ($this->query(Http\Method::GET, $url))
		{
			return $this->getResult();
		}
		return false;
	}

	/**
	 * Performs HEAD request.
	 *
	 * @param string $url Absolute URI eg. "http://user:pass @ host:port/path/?query"
	 * @return HttpHeaders|bool Response headers or false on error.
	 */
	public function head($url)
	{
		if ($this->query(Http\Method::HEAD, $url))
		{
			return $this->getHeaders();
		}
		return false;
	}

	/**
	 * Performs POST request.
	 *
	 * @param string $url Absolute URI eg. "http://user:pass @ host:port/path/?query".
	 * @param array|string|resource $postData Entity of POST/PUT request. If it's resource handler then data will be read directly from the stream.
	 * @param boolean $multipart Whether to use multipart/form-data encoding. If true, method accepts file as a resource or as an array with keys 'resource' (or 'content') and optionally 'filename' and 'contentType'
	 * @return string|bool Response entity string or false on error. Note, it's empty string if outputStream is set.
	 */
	public function post($url, $postData = null, $multipart = false)
	{
		if ($multipart)
		{
			$postData = $this->prepareMultipart($postData);
			if ($postData === false)
			{
				return false;
			}
		}

		if ($this->query(Http\Method::POST, $url, $postData))
		{
			return $this->getResult();
		}
		return false;
	}

	/**
	 * Performs multipart/form-data encoding.
	 * Accepts file as a resource or as an array with keys 'resource' (or 'content') and optionally 'filename' and 'contentType'.
	 *
	 * @param array|string|resource $postData Entity of POST/PUT request
	 * @return Http\MultipartStream|bool False on error
	 */
	protected function prepareMultipart($postData)
	{
		if (is_array($postData))
		{
			try
			{
				$data = new Http\MultipartStream($postData);
				$this->setHeader('Content-type', 'multipart/form-data; boundary=' . $data->getBoundary());

				return $data;
			}
			catch (ArgumentException $e)
			{
				$this->addError('MULTIPART', $e->getMessage(), true);
				return false;
			}
		}

		return $postData;
	}

	/**
	 * Perfoms HTTP request.
	 *
	 * @param string $method HTTP method (GET, POST, etc.). Note, it must be in UPPERCASE.
	 * @param string $url Absolute URI eg. "http://user:pass @ host:port/path/?query".
	 * @param array|string|resource|Http\Stream $entityBody Entity body of the request. If it's resource handler then data will be read directly from the stream.
	 * @return bool Query result (true or false). Response entity string can be got via getResult() method. Note, it's empty string if outputStream is set.
	 */
	public function query($method, $url, $entityBody = null)
	{
		$this->effectiveUrl = $url;
		$this->effectiveIp = null;
		$this->error = [];

		if (is_array($entityBody))
		{
			$entityBody = new Http\FormStream($entityBody);
		}

		if ($entityBody instanceof Http\Stream)
		{
			$body = $entityBody;
		}
		elseif (is_resource($entityBody))
		{
			$body = new Http\Stream($entityBody);
		}
		else
		{
			$body = new Http\Stream('php://temp', 'r+');
			$body->write($entityBody ?? '');
		}

		$this->redirectCount = 0;

		while (true)
		{
			//Only absoluteURI is accepted
			//Location response-header field must be absoluteURI either
			$uri = new Uri($this->effectiveUrl);

			// make a PSR-7 request
			$request = new Http\Request($method, $uri, $this->headers->getHeaders(), $body, $this->version);

			try
			{
				// PSR-18 magic is here
				$this->sendRequest($request);
			}
			catch (ClientExceptionInterface $e)
			{
				// compatibility mode
				if ($e instanceof NetworkExceptionInterface)
				{
					$this->addError('NETWORK', $e->getMessage());
				}
				return false;
			}

			if (!$this->waitResponse)
			{
				return true;
			}

			if ($this->redirect && ($location = $this->getHeaders()->get('Location')) !== null && $location != '')
			{
				if ($this->redirectCount < $this->redirectMax)
				{
					// there can be different host in Location
					$this->headers->delete('Host');
					$this->effectiveUrl = $location;

					$status = $this->getStatus();
					if ($status == 302 || $status == 303)
					{
						$method = Http\Method::GET;
					}

					$this->redirectCount++;
				}
				else
				{
					$this->addError('REDIRECT', "Maximum number of redirects ({$this->redirectMax}) has been reached at URL {$url}", true);
					return false;
				}
			}
			else
			{
				return true;
			}
		}
	}

	/**
	 * Sets an HTTP request header.
	 *
	 * @param string $name Name of the header field.
	 * @param string $value Value of the field.
	 * @param bool $replace Replace existing header field with the same name or add one more.
	 * @return $this
	 */
	public function setHeader($name, $value, $replace = true)
	{
		if ($replace || !$this->headers->has($name))
		{
			$this->headers->set($name, $value);
		}
		return $this;
	}

	/**
	 * Sets an array of headers for HTTP request. Clears all previously set headers.
	 *
	 * @param array $headers Array of header_name => value pairs.
	 * @return $this
	 */
	public function setHeaders(array $headers)
	{
		$this->headers = new HttpHeaders($headers);

		return $this;
	}

	/**
	 * Returns HTTP request headers.
	 *
	 * @return HttpHeaders
	 */
	public function getRequestHeaders(): HttpHeaders
	{
		if ($this->request)
		{
			return $this->request->getHeadersCollection();
		}
		return $this->headers;
	}

	/**
	 * Clears all HTTP request header fields.
	 */
	public function clearHeaders()
	{
		$this->headers->clear();
	}

	/**
	 * Sets an array of cookies for HTTP request. Warning! Replaces 'Cookie' header.
	 *
	 * @param array $cookies Array of cookie_name => value pairs.
	 * @return $this
	 */
	public function setCookies(array $cookies)
	{
		if (!empty($cookies))
		{
			$this->setHeader('Cookie', (new HttpCookies($cookies))->implode());
		}

		return $this;
	}

	/**
	 * Sets Basic Authorization request header field.
	 *
	 * @param string $user Username.
	 * @param string $pass Password.
	 * @return $this
	 */
	public function setAuthorization($user, $pass)
	{
		$this->setHeader('Authorization', 'Basic ' . base64_encode($user . ':' . $pass));
		return $this;
	}

	/**
	 * Sets redirect options.
	 *
	 * @param bool $value If true, do redirect (default true).
	 * @param null|int $max Maximum allowed redirect count.
	 * @return $this
	 */
	public function setRedirect($value, $max = null)
	{
		$this->redirect = (bool)$value;
		if ($max !== null)
		{
			$this->redirectMax = intval($max);
		}
		return $this;
	}

	/**
	 * Sets response body waiting option.
	 *
	 * @param bool $value If true, wait for response body. If false, disconnect just after reading headers (default true).
	 * @return $this
	 */
	public function waitResponse($value)
	{
		$this->waitResponse = (bool)$value;
		if (!$this->waitResponse)
		{
			$this->setStreamTimeout(self::DEFAULT_STREAM_TIMEOUT_NO_WAIT);
		}

		return $this;
	}

	/**
	 * Sets connection timeout.
	 *
	 * @param int $value Connection timeout in seconds (default 30).
	 * @return $this
	 */
	public function setTimeout($value)
	{
		$this->socketTimeout = intval($value);
		return $this;
	}

	/**
	 * Sets socket stream reading timeout.
	 *
	 * @param int $value Stream reading timeout in seconds; "0" means no timeout (default 60).
	 * @return $this
	 */
	public function setStreamTimeout($value)
	{
		$this->streamTimeout = intval($value);
		return $this;
	}

	/**
	 * Sets HTTP protocol version. In version 1.1 chunked response is possible.
	 *
	 * @param string $value Version "1.0" or "1.1" (default "1.0").
	 * @return $this
	 */
	public function setVersion($value)
	{
		$this->version = $value;
		return $this;
	}

	/**
	 * Sets compression option.
	 * Consider not to use the "compress" option with the output stream if a content can be large.
	 * Note, that compressed response is processed anyway if Content-Encoding response header field is set
	 *
	 * @param bool $value If true, "Accept-Encoding: gzip" will be sent.
	 * @return $this
	 */
	public function setCompress($value)
	{
		$this->compress = (bool)$value;
		return $this;
	}

	/**
	 * Sets charset for the entity-body (used in the Content-Type request header field for POST and PUT).
	 *
	 * @param string $value Charset.
	 * @return $this
	 */
	public function setCharset($value)
	{
		$this->requestCharset = $value;
		return $this;
	}

	/**
	 * Disables ssl certificate verification.
	 *
	 * @return $this
	 */
	public function disableSslVerification()
	{
		$this->sslVerify = false;
		return $this;
	}

	/**
	 * Enables or disables requests to private IPs.
	 *
	 * @param bool $value
	 * @return $this
	 */
	public function setPrivateIp($value)
	{
		$this->privateIp = (bool)$value;
		return $this;
	}

	/**
	 * Sets HTTP proxy for request.
	 *
	 * @param string $proxyHost Proxy host name or address (without "http://").
	 * @param null|int $proxyPort Proxy port number.
	 * @param null|string $proxyUser Proxy username.
	 * @param null|string $proxyPassword Proxy password.
	 * @return $this
	 */
	public function setProxy($proxyHost, $proxyPort = null, $proxyUser = null, $proxyPassword = null)
	{
		$this->proxyHost = $proxyHost;
		$proxyPort = (int)$proxyPort;
		if ($proxyPort > 0)
		{
			$this->proxyPort = $proxyPort;
		}
		$this->proxyUser = $proxyUser ?? '';
		$this->proxyPassword = $proxyPassword ?? '';

		return $this;
	}

	/**
	 * Sets the response output to the stream instead of the string result. Useful for large responses.
	 * Note, the stream must be readable/writable to support a compressed response.
	 * Note, in this mode the result string is empty.
	 *
	 * @param resource $handler File or stream handler.
	 * @return $this
	 */
	public function setOutputStream($handler)
	{
		$this->outputStream = $handler;
		return $this;
	}

	/**
	 * Sets the maximum body length that will be received in $this->readBody().
	 *
	 * @param int $bodyLengthMax
	 * @return $this
	 */
	public function setBodyLengthMax($bodyLengthMax)
	{
		$this->bodyLengthMax = intval($bodyLengthMax);
		return $this;
	}

	/**
	 * Downloads and saves a file.
	 *
	 * @param string $url URI to download.
	 * @param string $filePath Absolute file path.
	 * @return bool
	 */
	public function download($url, $filePath)
	{
		$dir = IO\Path::getDirectory($filePath);
		IO\Directory::createDirectory($dir);

		$result = $this->query(Http\Method::GET, $url);

		if ($result && ($status = $this->getStatus()) >= 200 && $status < 300)
		{
			$file = new IO\File($filePath);
			$handler = $file->open('w+');

			$this->setOutputStream($handler);

			$this->getResult();

			$file->close();

			return true;
		}

		return false;
	}

	/**
	 * Returns URL of the last redirect if request was redirected, or initial URL if request was not redirected.
	 *
	 * @return string
	 */
	public function getEffectiveUrl()
	{
		return $this->effectiveUrl;
	}

	/**
	 * Sets context options and parameters.
	 *
	 * @param array $options Context options and parameters
	 * @return $this
	 */
	public function setContextOptions(array $options)
	{
		$this->contextOptions = array_replace_recursive($this->contextOptions, $options);
		return $this;
	}

	/**
	 * Returns parsed HTTP response headers.
	 *
	 * @return HttpHeaders
	 */
	public function getHeaders(): HttpHeaders
	{
		if ($this->response)
		{
			return $this->response->getHeadersCollection();
		}
		return new HttpHeaders();
	}

	/**
	 * Returns parsed HTTP response cookies.
	 *
	 * @return HttpCookies
	 */
	public function getCookies(): HttpCookies
	{
		return $this->getHeaders()->getCookies();
	}

	/**
	 * Returns HTTP response status code.
	 *
	 * @return int
	 */
	public function getStatus()
	{
		if ($this->response)
		{
			return $this->response->getStatusCode();
		}
		return 0;
	}

	/**
	 * Returns HTTP response entity string. Note, if outputStream is set, the result will be the empty string.
	 *
	 * @return string
	 */
	public function getResult()
	{
		$result = '';
		if ($this->response)
		{
			$body = $this->response->getBody();

			if ($this->outputStream === null)
			{
				$result = (string)$body;
			}
			else
			{
				$body->copyTo($this->outputStream);
			}
		}
		return $result;
	}

	/**
	 * Returns PSR-7 response.
	 *
	 * @return Http\Response|null
	 */
	public function getResponse()
	{
		return $this->response;
	}

	/**
	 * Returns array of errors on failure.
	 *
	 * @return array Array with "error_code" => "error_message" pair
	 */
	public function getError()
	{
		return $this->error;
	}

	/**
	 * Returns response content type.
	 *
	 * @return string
	 */
	public function getContentType()
	{
		return $this->getHeaders()->getContentType();
	}

	/**
	 * Returns response content encoding.
	 *
	 * @return string
	 */
	public function getCharset()
	{
		return $this->getHeaders()->getCharset();
	}

	/**
	 * Returns remote peer ip address (only if privateIp = false).
	 *
	 * @return string|false
	 */
	public function getPeerAddress()
	{
		if ($this->effectiveIp)
		{
			return (string)$this->effectiveIp;
		}
		return false;
	}

	protected function addError($code, $message, $triggerWarning = false)
	{
		$this->error[$code] = $message;

		if ($triggerWarning)
		{
			trigger_error($message, E_USER_WARNING);
		}
	}

	protected function buildRequest(RequestInterface $request): RequestInterface
	{
		$method = $request->getMethod();
		$uri = $request->getUri();
		$body = $request->getBody();

		$punyUri = new Uri('http://' . $uri->getHost());
		if (($punyHost = $punyUri->convertToPunycode()) != $uri->getHost())
		{
			$uri = $uri->withHost($punyHost);
			$request = $request->withUri($uri);
		}

		if (!$request->hasHeader('Host'))
		{
			$request = $request->withHeader('Host', $uri->getHost());
		}
		if (!$request->hasHeader('Connection'))
		{
			$request = $request->withHeader('Connection', 'close');
		}
		if (!$request->hasHeader('Accept'))
		{
			$request = $request->withHeader('Accept', '*/*');
		}
		if (!$request->hasHeader('Accept-Language'))
		{
			$request = $request->withHeader('Accept-Language', 'en');
		}
		if ($this->compress)
		{
			$request = $request->withHeader('Accept-Encoding', 'gzip');
		}
		if (($userInfo = $uri->getUserInfo()) != '')
		{
			$request = $request->withHeader('Authorization', 'Basic ' . base64_encode($userInfo));
		}
		if ($this->proxyHost != '' && $this->proxyUser != '')
		{
			$request = $request->withHeader('Proxy-Authorization', 'Basic ' . base64_encode($this->proxyUser . ':' . $this->proxyPassword));
		}

		// the client doesn't support "Expect-Continue", set empty value for cURL
		if ($this->useCurl)
		{
			$request = $request->withHeader('Expect', '');
		}

		if ($method == Http\Method::POST)
		{
			//special processing for POST requests
			if (!$request->hasHeader('Content-Type'))
			{
				$contentType = 'application/x-www-form-urlencoded';
				if ($this->requestCharset != '')
				{
					$contentType .= '; charset=' . $this->requestCharset;
				}
				$request = $request->withHeader('Content-Type', $contentType);
			}
		}

		$size = $body->getSize();

		if ($size > 0 || $method == Http\Method::POST || $method == Http\Method::PUT)
		{
			// A valid Content-Length field value is required on all HTTP/1.0 request messages containing an entity body.
			if (!$request->hasHeader('Content-Length'))
			{
				$request = $request->withHeader('Content-Length', $size ?? strlen((string)$body));
			}
		}

		return $request;
	}

	protected function checkRequest(RequestInterface $request): bool
	{
		$uri = $request->getUri();

		$scheme = $uri->getScheme();
		if ($scheme !== 'http' && $scheme !== 'https')
		{
			$this->addError('URI_SCHEME', 'Only http and https shemes are supported.');
			return false;
		}

		if ($uri->getHost() == '')
		{
			$this->addError('URI_HOST', 'Incorrect host in URI.');
			return false;
		}

		$punyUri = new Uri('http://' . $uri->getHost());
		$error = $punyUri->convertToPunycode();
		if ($error instanceof \Bitrix\Main\Error)
		{
			$this->addError('URI_PUNICODE', "Error converting hostname to punycode: {$error->getMessage()}");
			return false;
		}

		if (!$this->privateIp)
		{
			$ip = IpAddress::createByUri($uri);
			if ($ip->isPrivate())
			{
				$this->addError('PRIVATE_IP', "Resolved IP is incorrect or private: {$ip->get()}");
				return false;
			}
			$this->effectiveIp = $ip;
		}

		return true;
	}

	/**
	 * @inheritdoc
	 */
	public function sendRequest(RequestInterface $request): ResponseInterface
	{
		if (!$this->checkRequest($request))
		{
			throw new Http\RequestException($request, reset($this->error));
		}

		$this->request = $this->buildRequest($request);

		$queue = $this->createQueue(false);

		$promise = $this->createPromise($this->request, $queue);

		$queue->add($promise);

		$this->response = $promise->wait();

		return $this->response;
	}

	/**
	 * @param RequestInterface $request
	 * @return PromiseInterface
	 * @throws Http\ClientException
	 */
	public function sendAsyncRequest(RequestInterface $request): PromiseInterface
	{
		if (!$this->checkRequest($request))
		{
			throw new Http\RequestException($request, reset($this->error));
		}

		$this->request = $this->buildRequest($request);

		if ($this->queue === null)
		{
			$this->queue = $this->createQueue();
		}

		$promise = $this->createPromise($this->request, $this->queue);

		$this->queue->add($promise);

		return $promise;
	}

	/**
	 * @param RequestInterface $request
	 * @return Http\Curl\Handler | Http\Socket\Handler
	 */
	protected function createHandler(RequestInterface $request)
	{
		if ($this->sslVerify === false)
		{
			$this->contextOptions['ssl']['verify_peer_name'] = false;
			$this->contextOptions['ssl']['verify_peer'] = false;
			$this->contextOptions['ssl']['allow_self_signed'] = true;
		}

		$handlerOptions = [
			'waitResponse' => $this->waitResponse,
			'bodyLengthMax' => $this->bodyLengthMax,
			'proxyHost' => $this->proxyHost,
			'proxyPort' => $this->proxyPort,
			'effectiveIp' => $this->effectiveIp,
			'contextOptions' => $this->contextOptions,
			'socketTimeout' => $this->socketTimeout,
			'streamTimeout' => $this->streamTimeout,
			'curlLogFile' => $this->curlLogFile,
		];

		$responseBuilder = new Http\ResponseBuilder();

		if ($this->useCurl)
		{
			$handler = new Http\Curl\Handler($request, $responseBuilder, $handlerOptions);
		}
		else
		{
			$handler = new Http\Socket\Handler($request, $responseBuilder, $handlerOptions);
		}

		if ($this->logger !== null)
		{
			$handler->setLogger($this->logger);
			$handler->setDebugLevel($this->debugLevel);
		}

		if ($this->shouldFetchBody !== null)
		{
			$handler->shouldFetchBody($this->shouldFetchBody);
		}

		return $handler;
	}

	/**
	 * @param RequestInterface $request
	 * @param Http\Queue $queue
	 * @return Http\Curl\Promise | Http\Socket\Promise
	 */
	protected function createPromise(RequestInterface $request, Http\Queue $queue)
	{
		$handler = $this->createHandler($request);

		if ($this->useCurl)
		{
			return new Http\Curl\Promise($handler, $queue);
		}
		return new Http\Socket\Promise($handler, $queue);
	}

	/**
	 * @param bool $backgroundJob
	 * @return Http\Curl\Queue|Http\Socket\Queue
	 */
	protected function createQueue(bool $backgroundJob = true)
	{
		if ($this->useCurl)
		{
			return new Http\Curl\Queue($backgroundJob);
		}
		return new Http\Socket\Queue($backgroundJob);
	}

	/**
	 * Waits for async promises and returns responses from processed promises.
	 *
	 * @return ResponseInterface[]
	 */
	public function wait(): array
	{
		$responses = [];

		if ($this->queue)
		{
			foreach ($this->queue->wait() as $promise)
			{
				$responses[$promise->getId()] = $promise->wait();
			}
		}

		return $responses;
	}

	/**
	 * Sets a callback called before fetching a message body.
	 *
	 * @param callable $callback
	 * @return void
	 */
	public function shouldFetchBody(callable $callback): void
	{
		$this->shouldFetchBody = $callback;
	}
}
