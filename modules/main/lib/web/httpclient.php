<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2014 Bitrix
 */
namespace Bitrix\Main\Web;

use Bitrix\Main\IO;
use Bitrix\Main\Config\Configuration;
use Psr\Log;
use Bitrix\Main\Diag;

class HttpClient implements Log\LoggerAwareInterface
{
	use Log\LoggerAwareTrait;

	const HTTP_1_0 = "1.0";
	const HTTP_1_1 = "1.1";
	const HTTP_GET = "GET";
	const HTTP_POST = "POST";
	const HTTP_PUT = "PUT";
	const HTTP_HEAD = "HEAD";
	const HTTP_PATCH = "PATCH";
	const HTTP_DELETE = "DELETE";
	const HTTP_OPTIONS = "OPTIONS";

	const DEFAULT_SOCKET_TIMEOUT = 30;
	const DEFAULT_STREAM_TIMEOUT = 60;
	const DEFAULT_STREAM_TIMEOUT_NO_WAIT = 1;

	const BUF_READ_LEN = 16384;
	const BUF_POST_LEN = 131072;

	protected $proxyHost;
	protected $proxyPort;
	protected $proxyUser;
	protected $proxyPassword;

	protected $resource;
	protected $socketTimeout = self::DEFAULT_SOCKET_TIMEOUT;
	protected $streamTimeout = self::DEFAULT_STREAM_TIMEOUT;
	protected $error = [];
	protected $peerSocketName;

	/** @var HttpHeaders */
	protected $requestHeaders;
	/** @var HttpCookies  */
	protected $requestCookies;
	protected $waitResponse = true;
	protected $redirect = true;
	protected $redirectMax = 5;
	protected $redirectCount = 0;
	protected $compress = false;
	protected $version = self::HTTP_1_0;
	protected $requestCharset = '';
	protected $sslVerify = true;
	protected $bodyLengthMax = 0;
	protected $privateIp = true;

	protected $status = 0;
	/** @var HttpHeaders */
	protected $responseHeaders;
	/** @var HttpCookies  */
	protected $responseCookies;
	protected $result = '';
	protected $outputStream;

	/** @var IpAddress */
	protected $effectiveIp;
	protected $effectiveUrl;
	protected $queryMethod;
	protected $receivedBytesLength = 0;

	protected $contextOptions = [];
	protected $debugLevel = HttpDebug::REQUEST_HEADERS | HttpDebug::RESPONSE_HEADERS;

	/**
	 * @param array|null $options Optional array with options:
	 *		"redirect" bool Follow redirects (default true).
	 *		"redirectMax" int Maximum number of redirects (default 5).
	 *		"waitResponse" bool Read the body or disconnect just after reading headers (default true).
	 *		"socketTimeout" int Connection timeout in seconds (default 30).
	 *		"streamTimeout" int Stream reading timeout in seconds (default 60 for waitResponse == true and 1 for waitResponse == false).
	 *		"version" string HTTP version (HttpClient::HTTP_1_0, HttpClient::HTTP_1_1) (default "1.0").
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
	 * 	All the options can be set separately with setters.
	 */
	public function __construct(array $options = null)
	{
		$this->requestHeaders = new HttpHeaders();
		$this->responseHeaders = new HttpHeaders();
		$this->requestCookies = new HttpCookies();
		$this->responseCookies = new HttpCookies();

		if($options === null)
		{
			$options = array();
		}

		$defaultOptions = Configuration::getValue("http_client_options");
		if($defaultOptions !== null)
		{
			$options += $defaultOptions;
		}

		if(!empty($options))
		{
			if(isset($options["redirect"]))
			{
				$this->setRedirect($options["redirect"], $options["redirectMax"]);
			}
			if(isset($options["waitResponse"]))
			{
				$this->waitResponse($options["waitResponse"]);
			}
			if(isset($options["socketTimeout"]))
			{
				$this->setTimeout($options["socketTimeout"]);
			}
			if(isset($options["streamTimeout"]))
			{
				$this->setStreamTimeout($options["streamTimeout"]);
			}
			if(isset($options["version"]))
			{
				$this->setVersion($options["version"]);
			}
			if(isset($options["proxyHost"]))
			{
				$this->setProxy($options["proxyHost"], $options["proxyPort"], $options["proxyUser"], $options["proxyPassword"]);
			}
			if(isset($options["compress"]))
			{
				$this->setCompress($options["compress"]);
			}
			if(isset($options["charset"]))
			{
				$this->setCharset($options["charset"]);
			}
			if(isset($options["disableSslVerification"]) && $options["disableSslVerification"] === true)
			{
				$this->disableSslVerification();
			}
			if(isset($options["bodyLengthMax"]))
			{
				$this->setBodyLengthMax($options["bodyLengthMax"]);
			}
			if(isset($options["privateIp"]))
			{
				$this->setPrivateIp($options["privateIp"]);
			}
			if(isset($options["debugLevel"]))
			{
				$this->setDebugLevel((int)$options["debugLevel"]);
			}
			if(isset($options["cookies"]))
			{
				$this->setCookies($options["cookies"]);
			}
			if(isset($options["headers"]))
			{
				$this->setHeaders($options["headers"]);
			}
		}
	}

	/**
	 * Closes the connection on the object destruction.
	 */
	public function __destruct()
	{
		$this->disconnect();
	}

	/**
	 * Performs GET request.
	 *
	 * @param string $url Absolute URI eg. "http://user:pass @ host:port/path/?query".
	 * @return string|bool Response entity string or false on error. Note, it's empty string if outputStream is set.
	 */
	public function get($url)
	{
		if($this->query(self::HTTP_GET, $url))
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
		if($this->query(self::HTTP_HEAD, $url))
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
			if($postData === false)
			{
				return false;
			}
		}

		if($this->query(self::HTTP_POST, $url, $postData))
		{
			return $this->getResult();
		}
		return false;
	}

	/**
	 * Performs multipart/form-data encoding.
	 * Accepts file as a resource or as an array with keys 'resource' (or 'content') and optionally 'filename' and 'contentType'
	 *
	 * @param array|string|resource $postData Entity of POST/PUT request
	 * @return string|bool False on error
	 */
	protected function prepareMultipart($postData)
	{
		if (is_array($postData))
		{
			$boundary = 'BXC'.md5(rand().time());

			$data = '';

			foreach ($postData as $k => $v)
			{
				$data .= '--'.$boundary."\r\n";

				if ((is_resource($v) && get_resource_type($v) === 'stream') || is_array($v))
				{
					$filename = $k;
					$contentType = 'application/octet-stream';

					if (is_array($v))
					{
						if (isset($v['resource']) && is_resource($v['resource']) && get_resource_type($v['resource']) === 'stream')
						{
							$resource = $v['resource'];
							$content = stream_get_contents($resource);
						}
						else
						{
							if (isset($v['content']))
							{
								$content = $v['content'];
							}
							else
							{
								$this->addError('MULTIPART', "File `{$k}` not found for multipart upload.", true);
								return false;
							}
						}

						if (isset($v['filename']))
						{
							$filename = $v['filename'];
						}

						if (isset($v['contentType']))
						{
							$contentType = $v['contentType'];
						}
					}
					else
					{
						$content = stream_get_contents($v);
					}

					$data .= 'Content-Disposition: form-data; name="'.$k.'"; filename="'.$filename.'"'."\r\n";
					$data .= 'Content-Type: '.$contentType."\r\n\r\n";
					$data .= $content."\r\n";
				}
				else
				{
					$data .= 'Content-Disposition: form-data; name="'.$k.'"'."\r\n\r\n";
					$data .= $v."\r\n";
				}
			}

			$data .= '--'.$boundary."--\r\n";
			$postData = $data;

			$this->setHeader('Content-type', 'multipart/form-data; boundary='.$boundary);
		}

		return $postData;
	}

	/**
	 * Perfoms HTTP request.
	 *
	 * @param string $method HTTP method (GET, POST, etc.). Note, it must be in UPPERCASE.
	 * @param string $url Absolute URI eg. "http://user:pass @ host:port/path/?query".
	 * @param array|string|resource $entityBody Entity body of the request. If it's resource handler then data will be read directly from the stream.
	 * @return bool Query result (true or false). Response entity string can be got via getResult() method. Note, it's empty string if outputStream is set.
	 */
	public function query($method, $url, $entityBody = null)
	{
		$this->queryMethod = $method;
		$this->effectiveUrl = $url;
		$this->effectiveIp = null;
		$this->error = [];

		if(is_array($entityBody))
		{
			$entityBody = http_build_query($entityBody, "", "&");
		}

		$this->redirectCount = 0;

		while(true)
		{
			//Only absoluteURI is accepted
			//Location response-header field must be absoluteURI either
			$parsedUrl = new Uri($this->effectiveUrl);
			if($parsedUrl->getHost() == '')
			{
				$this->addError('URI', "Incorrect URI: {$this->effectiveUrl}");
				return false;
			}

			$error = $parsedUrl->convertToPunycode();
			if($error instanceof \Bitrix\Main\Error)
			{
				$this->addError('URI', "Error converting hostname to punycode: {$error->getMessage()}");
				return false;
			}

			if($this->privateIp == false)
			{
				$ip = IpAddress::createByUri($parsedUrl);
				if($ip->isPrivate())
				{
					$this->addError('PRIVATE_IP', "Resolved IP is incorrect or private: {$ip->get()}");
					return false;
				}
				$this->effectiveIp = $ip;
			}

			//just in case of serial queries
			$this->disconnect();

			if($this->connect($parsedUrl) === false)
			{
				return false;
			}

			$this->sendRequest($this->queryMethod, $parsedUrl, $entityBody);

			if(!$this->readHeaders())
			{
				$this->disconnect();
				return false;
			}

			if(!$this->waitResponse)
			{
				$this->disconnect();
				return true;
			}

			if($this->redirect && ($location = $this->responseHeaders->get("Location")) !== null && $location <> '')
			{
				//we don't need a body on redirect
				$this->disconnect();

				if($this->redirectCount < $this->redirectMax)
				{
					$this->effectiveUrl = $location;
					if($this->status == 302 || $this->status == 303)
					{
						$this->queryMethod = self::HTTP_GET;
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
				//the connection is still active to read the response body
				break;
			}
		}
		return true;
	}

	/**
	 * Sets an HTTP request header field.
	 *
	 * @param string $name Name of the header field.
	 * @param string $value Value of the field.
	 * @param bool $replace Replace existing header field with the same name or add one more.
	 * @return $this
	 */
	public function setHeader($name, $value, $replace = true)
	{
		if($replace == true || $this->requestHeaders->get($name) === null)
		{
			$this->requestHeaders->set($name, $value);
		}
		return $this;
	}

	/**
	 * Sets an array of headers for HTTP request.
	 *
	 * @param array $headers Array of header_name => value pairs.
	 * @return $this
	 */
	public function setHeaders(array $headers)
	{
		foreach ($headers as $name => $value)
		{
			$this->setHeader($name, $value);
		}
		return $this;
	}

	/**
	 * Clears all HTTP request header fields.
	 */
	public function clearHeaders()
	{
		$this->requestHeaders->clear();
	}

	/**
	 * Sets an array of cookies for HTTP request.
	 *
	 * @param array $cookies Array of cookie_name => value pairs.
	 * @return $this
	 */
	public function setCookies(array $cookies)
	{
		$this->requestCookies->set($cookies);
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
		$this->setHeader("Authorization", "Basic ".base64_encode($user.":".$pass));
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
		if($max !== null)
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
		if(!$this->waitResponse)
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
		$this->proxyPort = intval($proxyPort);
		if($this->proxyPort <= 0)
		{
			$this->proxyPort = 80;
		}
		$this->proxyUser = $proxyUser;
		$this->proxyPassword = $proxyPassword;

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

		$file = new IO\File($filePath);
		$handler = $file->open("w+");

		$this->setOutputStream($handler);

		$res = $this->query(self::HTTP_GET, $url);
		if($res)
		{
			$res = $this->readBody();
		}
		$this->disconnect();

		$file->close();
		return $res;
	}

	/**
	 * Returns URL of the last redirect if request was redirected, or initial URL if request was not redirected.
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

	protected function connect(Uri $url)
	{
		if($this->proxyHost <> '')
		{
			$proto = "";
			$host = $this->proxyHost;
			$port = $this->proxyPort;
		}
		else
		{
			$proto = ($url->getScheme() == "https"? "ssl://" : "");
			$host = $url->getHost();
			$port = $url->getPort();

			if($this->effectiveIp !== null)
			{
				//set original host to match a sertificate
				$this->setContextOptions(["ssl" => ["peer_name" => $host]]);

				//resolved in query() if private IPs were disabled
				$host = $this->effectiveIp->get();
			}
		}

		$context = $this->createContext();

		//$context can be FALSE
		if($context)
		{
			$res = stream_socket_client($proto.$host.":".$port, $errno, $errstr, $this->socketTimeout, STREAM_CLIENT_CONNECT, $context);
		}
		else
		{
			$res = stream_socket_client($proto.$host.":".$port, $errno, $errstr, $this->socketTimeout);
		}

		if(is_resource($res))
		{
			$this->resource = $res;
			$this->peerSocketName = stream_socket_get_name($this->resource, true);

			if($this->streamTimeout > 0)
			{
				stream_set_timeout($this->resource, $this->streamTimeout);
			}

			return true;
		}

		if(intval($errno) > 0)
		{
			$this->addError('CONNECTION', "[{$errno}] {$errstr}");
		}
		else
		{
			$this->addError('SOCKET', 'Socket connection error.');
		}

		return false;
	}

	protected function createContext()
	{
		if ($this->sslVerify === false)
		{
			$this->contextOptions["ssl"]["verify_peer_name"] = false;
			$this->contextOptions["ssl"]["verify_peer"] = false;
			$this->contextOptions["ssl"]["allow_self_signed"] = true;
		}

		return stream_context_create($this->contextOptions);
	}

	protected function disconnect()
	{
		if($this->resource)
		{
			fclose($this->resource);
			$this->resource = null;
		}
	}

	protected function send($data)
	{
		return fwrite($this->resource, $data);
	}

	protected function receive($bufLength = null)
	{
		if($bufLength === null)
		{
			$bufLength = self::BUF_READ_LEN;
		}

		$buf = stream_get_contents($this->resource, $bufLength);
		if($buf !== false)
		{
			if(is_resource($this->outputStream))
			{
				//we can write response directly to stream (file, etc.) to minimize memory usage
				fwrite($this->outputStream, $buf);
				fflush($this->outputStream);
			}
			else
			{
				$this->result .= $buf;
			}
		}

		return $buf;
	}

	protected function sendRequest($method, Uri $url, $entityBody = null)
	{
		$this->status = 0;
		$this->result = '';
		$this->responseHeaders->clear();
		$this->responseCookies->clear();
		$this->receivedBytesLength = 0;
		$addedContentType = false;
		$addedContentLength = false;

		if($this->proxyHost <> '')
		{
			$path = $url->getLocator();
			if($this->proxyUser <> '')
			{
				$this->setHeader("Proxy-Authorization", "Basic ".base64_encode($this->proxyUser.":".$this->proxyPassword));
			}
		}
		else
		{
			$path = $url->getPathQuery();
		}

		$request = $method." ".$path." HTTP/".$this->version."\r\n";

		$this->setHeader("Host", $url->getHost());
		$this->setHeader("Connection", "close", false);
		$this->setHeader("Accept", "*/*", false);
		$this->setHeader("Accept-Language", "en", false);

		if(($user = $url->getUser()) <> '')
		{
			$this->setAuthorization($user, $url->getPass());
		}

		$cookies = $this->requestCookies->toString();
		if($cookies <> '')
		{
			$this->setHeader("Cookie", $cookies);
		}

		if($this->compress)
		{
			$this->setHeader("Accept-Encoding", "gzip");
		}

		if(!is_resource($entityBody))
		{
			if($method == self::HTTP_POST)
			{
				//special processing for POST requests
				if($this->requestHeaders->get("Content-Type") === null)
				{
					$contentType = "application/x-www-form-urlencoded";
					if($this->requestCharset <> '')
					{
						$contentType .= "; charset=".$this->requestCharset;
					}
					$this->setHeader("Content-Type", $contentType);
					$addedContentType = true;
				}
			}

			if($entityBody <> '' || $method == self::HTTP_POST || $method == self::HTTP_PUT)
			{
				// A valid Content-Length field value is required on all HTTP/1.0 request messages containing an entity body.
				if($this->requestHeaders->get("Content-Length") === null)
				{
					$this->setHeader("Content-Length", strlen($entityBody));
					$addedContentLength = true;
				}
			}
		}

		$request .= $this->requestHeaders->toString();
		$request .= "\r\n";

		if ($logger = $this->getLogger())
		{
			if ($this->debugLevel)
			{
				$logger->debug("{date} - {host}\n{trace}", ['trace' => Diag\Helper::getBackTrace(6, DEBUG_BACKTRACE_IGNORE_ARGS, 3)]);
			}
			if ($this->debugLevel & HttpDebug::REQUEST_HEADERS)
			{
				$logger->debug("REQUEST>>>\n" . $request);
			}
		}

		$this->send($request);

		// clear automatically added headers
		if ($addedContentType)
		{
			$this->requestHeaders->delete('Content-Type');
		}
		if ($addedContentLength)
		{
			$this->requestHeaders->delete('Content-Length');
		}

		if(is_resource($entityBody))
		{
			//PUT data can be a file resource
			while(!feof($entityBody))
			{
				$this->send(fread($entityBody, self::BUF_POST_LEN));
			}
		}
		elseif($entityBody <> '')
		{
			if ($logger && ($this->debugLevel & HttpDebug::REQUEST_BODY))
			{
				$logger->debug($entityBody);
			}

			$this->send($entityBody);
		}
	}

	protected function readHeaders()
	{
		$headers = "";
		while(!feof($this->resource))
		{
			$line = fgets($this->resource, self::BUF_READ_LEN);

			if($line == "\r\n")
			{
				break;
			}
			if(!$this->checkErrors($line))
			{
				return false;
			}

			$headers .= $line;
		}

		if ($logger = $this->getLogger())
		{
			if ($this->debugLevel & HttpDebug::RESPONSE_HEADERS)
			{
				$logger->debug("\nRESPONSE<<<\n" . $headers);
			}
		}

		$this->parseHeaders($headers);

		return true;
	}

	protected function readBody()
	{
		if($this->responseHeaders->get("Transfer-Encoding") == "chunked")
		{
			while(!feof($this->resource))
			{
				/*
				chunk = chunk-size [ chunk-extension ] CRLF
						chunk-data CRLF
				chunk-size = 1*HEX
				chunk-extension = *( ";" chunk-ext-name [ "=" chunk-ext-val ] )
				*/
				$line = fgets($this->resource, self::BUF_READ_LEN);

				if($line == "\r\n")
				{
					continue;
				}
				if(($pos = mb_strpos($line, ";")) !== false)
				{
					$line = mb_substr($line, 0, $pos);
				}

				$length = hexdec($line);

				if(!$this->receiveBytes($length))
				{
					return false;
				}
			}
		}
		elseif(($length = $this->responseHeaders->get("Content-Length")) !== null)
		{
			//we'll read exact length of the content
			if(!$this->receiveBytes($length))
			{
				return false;
			}
		}
		else
		{
			//we don't know the length of the content - hope we'll reach the stream's end
			while(!feof($this->resource))
			{
				$buf = $this->receive();

				$this->receivedBytesLength += strlen($buf);

				if(!$this->checkErrors($buf))
				{
					return false;
				}
			}
		}

		if($this->responseHeaders->get("Content-Encoding") == "gzip")
		{
			$this->decompress();
		}

		if ($logger = $this->getLogger())
		{
			if ($this->debugLevel & HttpDebug::RESPONSE_BODY)
			{
				$logger->debug("\n");
				$logger->debug($this->result);
			}
			if ($this->debugLevel)
			{
				$logger->debug("\n{delimiter}\n");
			}
		}

		return true;
	}

	protected function receiveBytes($length)
	{
		while($length > 0 && !feof($this->resource))
		{
			$count = ($length > self::BUF_READ_LEN? self::BUF_READ_LEN : $length);

			$buf = $this->receive($count);

			$receivedBytesLength = strlen($buf);
			$this->receivedBytesLength += $receivedBytesLength;

			if(!$this->checkErrors($buf))
			{
				return false;
			}

			$length -= $receivedBytesLength;
		}

		return true;
	}

	protected function checkErrors($buf)
	{
		if($this->streamTimeout > 0)
		{
			$info = stream_get_meta_data($this->resource);
			if($info['timed_out'])
			{
				$this->addError('STREAM_TIMEOUT', "Stream reading timeout of {$this->streamTimeout} second(s) has been reached.");
				return false;
			}
		}

		if($buf === false)
		{
			$this->addError('STREAM_READING', 'Stream reading error.');
			return false;
		}

		if($this->bodyLengthMax > 0 && $this->receivedBytesLength > $this->bodyLengthMax)
		{
			$this->addError('STREAM_LENGTH', 'Maximum content length has been reached. Breaking reading.');
			return false;
		}

		return true;
	}

	protected function decompress()
	{
		if(is_resource($this->outputStream))
		{
			$compressed = stream_get_contents($this->outputStream, -1, 10);
			$compressed = substr($compressed, 0, -8);
			if($compressed <> '')
			{
				$uncompressed = gzinflate($compressed);

				rewind($this->outputStream);
				$len = fwrite($this->outputStream, $uncompressed);
				ftruncate($this->outputStream, $len);
			}
		}
		else
		{
			$compressed = substr($this->result, 10, -8);
			if($compressed <> '')
			{
				$this->result = gzinflate($compressed);
			}
		}
	}

	protected function parseHeaders($headers)
	{
		foreach (explode("\n", $headers) as $k => $header)
		{
			if($k == 0)
			{
				if(preg_match('#HTTP\S+ (\d+)#', $header, $find))
				{
					$this->status = intval($find[1]);
				}
			}
			elseif(mb_strpos($header, ':') !== false)
			{
				list($headerName, $headerValue) = explode(':', $header, 2);
				if(mb_strtolower($headerName) == 'set-cookie')
				{
					$this->responseCookies->addFromString($headerValue);
				}
				$this->responseHeaders->add($headerName, trim($headerValue));
			}
		}
	}

	/**
	 * Returns parsed HTTP response headers
	 *
	 * @return HttpHeaders
	 */
	public function getHeaders()
	{
		return $this->responseHeaders;
	}

	/**
	 * Returns parsed HTTP response cookies
	 *
	 * @return HttpCookies
	 */
	public function getCookies()
	{
		return $this->responseCookies;
	}

	/**
	 * Returns HTTP response status code
	 *
	 * @return int
	 */
	public function getStatus()
	{
		return $this->status;
	}

	/**
	 * Returns HTTP response entity string. Note, if outputStream is set, the result will be empty string.
	 *
	 * @return string
	 */
	public function getResult()
	{
		if($this->waitResponse && $this->resource)
		{
			$this->readBody();
			$this->disconnect();
		}
		return $this->result;
	}

	/**
	 * Returns array of errors on failure
	 *
	 * @return array Array with "error_code" => "error_message" pair
	 */
	public function getError()
	{
		return $this->error;
	}

	/**
	 * Returns response content type
	 *
	 * @return string
	 */
	public function getContentType()
	{
		return $this->responseHeaders->getContentType();
	}

	/**
	 * Returns response content encoding
	 *
	 * @return string
	 */
	public function getCharset()
	{
		return $this->responseHeaders->getCharset();
	}

	/**
	 * Returns remote peer socket name (usually in form ip:port)
	 *
	 * @return string
	 */
	public function getPeerSocketName()
	{
		return $this->peerSocketName ?: '';
	}

	/**
	 * Returns remote peer ip address.
	 * @return string|false
	 */
	public function getPeerAddress()
	{
		if(!preg_match('/^(\d+)\.(\d+)\.(\d+)\.(\d+):(\d+)$/', $this->peerSocketName, $matches))
		{
			return false;
		}

		return sprintf('%d.%d.%d.%d', $matches[1], $matches[2], $matches[3], $matches[4]);
	}

	/**
	 * Returns remote peer ip address.
	 * @return int|false
	 */
	public function getPeerPort()
	{
		if(!preg_match('/^(\d+)\.(\d+)\.(\d+)\.(\d+):(\d+)$/', $this->peerSocketName, $matches))
		{
			return false;
		}

		return (int)$matches[5];
	}

	protected function addError($code, $message, $triggerWarning = false)
	{
		$this->error[$code] = $message;

		if ($triggerWarning)
		{
			trigger_error($message, E_USER_WARNING);
		}

		if ($logger = $this->getLogger())
		{
			$logger->error($message);
		}
	}

	protected function getLogger()
	{
		if ($this->logger === null)
		{
			$logger = Diag\Logger::create('main.HttpClient', [$this, $this->queryMethod, $this->effectiveUrl]);

			if ($logger !== null)
			{
				$this->setLogger($logger);
			}
		}

		return $this->logger;
	}

	/**
	 * Sets debug level using HttpDebug::* constants.
	 * @param int $debugLevel
	 * @return $this
	 */
	public function setDebugLevel(int $debugLevel)
	{
		$this->debugLevel = $debugLevel;
		return $this;
	}
}
