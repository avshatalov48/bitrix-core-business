<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2024 Bitrix
 */

namespace Bitrix\Main;

use Bitrix\Main\Web\HttpHeaders;

/**
 * Class HttpRequest extends Request. Contains http specific request data.
 *
 * @package Bitrix\Main
 */
class HttpRequest extends Request
{
	/**
	 * @var Type\ParameterDictionary
	 */
	protected $queryString;
	/**
	 * @var Type\ParameterDictionary
	 */
	protected $postData;
	/**
	 * @var Type\ParameterDictionary
	 */
	protected $files;
	/**
	 * @var Type\ParameterDictionary
	 */
	protected $cookies;
	/**
	 * @var Type\ParameterDictionary
	 */
	protected $cookiesRaw;
	/**
	 * @var Type\ParameterDictionary
	 */
	protected $jsonData;
	/**
	 * @var HttpHeaders
	 */
	protected $headers;
	protected $httpHost;
	protected $acceptedLanguages;

	/**
	 * Creates new HttpRequest object
	 *
	 * @param Server $server
	 * @param array $queryString _GET
	 * @param array $postData _POST
	 * @param array $files _FILES
	 * @param array $cookies _COOKIE
	 */
	public function __construct(Server $server, array $queryString, array $postData, array $files, array $cookies)
	{
		$request = array_merge($queryString, $postData);
		parent::__construct($server, $request);

		$this->queryString = new Type\ParameterDictionary($queryString);
		$this->postData = new Type\ParameterDictionary($postData);
		$this->files = new Type\ParameterDictionary($files);
		$this->cookiesRaw = new Type\ParameterDictionary($cookies);
		$this->cookies = new Type\ParameterDictionary($this->prepareCookie($cookies));
		$this->headers = $this->buildHttpHeaders($server);
		$this->jsonData = new Type\ParameterDictionary();
	}

	private function buildHttpHeaders(Server $server)
	{
		$headers = new HttpHeaders();
		foreach ($this->fetchHeaders($server) as $headerName => $value)
		{
			try
			{
				$headers->add($headerName, $value);
			}
			catch (\InvalidArgumentException)
			{
				// ignore an invalid header
			}
		}

		return $headers;
	}

	/**
	 * Applies filter to the http request data. Preserve original values.
	 *
	 * @param Type\IRequestFilter $filter Filter object
	 */
	public function addFilter(Type\IRequestFilter $filter)
	{
		parent::addFilter($filter);

		$filteredValues = $filter->filter([
			'get' => $this->queryString->values,
			'post' => $this->postData->values,
			'files' => $this->files->values,
			'cookie' => $this->cookiesRaw->values,
			'json' => $this->jsonData->values,
		]);

		if (isset($filteredValues['get']))
		{
			$this->queryString->setValuesNoDemand($filteredValues['get']);
		}
		if (isset($filteredValues['post']))
		{
			$this->postData->setValuesNoDemand($filteredValues['post']);
		}
		if (isset($filteredValues['files']))
		{
			$this->files->setValuesNoDemand($filteredValues['files']);
		}
		if (isset($filteredValues['cookie']))
		{
			$this->cookiesRaw->setValuesNoDemand($filteredValues['cookie']);
			$this->cookies = new Type\ParameterDictionary($this->prepareCookie($filteredValues['cookie']));
		}
		if (isset($filteredValues['json']))
		{
			$this->jsonData->setValuesNoDemand($filteredValues['json']);
		}

		if (isset($filteredValues['get']) || isset($filteredValues['post']))
		{
			$this->setValuesNoDemand(array_merge($this->queryString->values, $this->postData->values));
		}

		// need to reinit
		$this->requestedPage = null;
		$this->requestedPageDirectory = null;
	}

	/**
	 * Returns the GET parameter of the current request.
	 *
	 * @param string $name Parameter name
	 * @return null | string | array
	 */
	public function getQuery($name)
	{
		return $this->queryString->get($name);
	}

	/**
	 * Returns the list of GET parameters of the current request.
	 *
	 * @return Type\ParameterDictionary
	 */
	public function getQueryList()
	{
		return $this->queryString;
	}

	/**
	 * Returns the POST parameter of the current request.
	 *
	 * @param $name
	 * @return string | array | null
	 */
	public function getPost($name)
	{
		return $this->postData->get($name);
	}

	/**
	 * Returns the list of POST parameters of the current request.
	 *
	 * @return Type\ParameterDictionary
	 */
	public function getPostList()
	{
		return $this->postData;
	}

	/**
	 * Returns the FILES parameter of the current request.
	 *
	 * @param $name
	 * @return string | array | null
	 */
	public function getFile($name)
	{
		return $this->files->get($name);
	}

	/**
	 * Returns the list of FILES parameters of the current request.
	 *
	 * @return Type\ParameterDictionary
	 */
	public function getFileList()
	{
		return $this->files;
	}

	/**
	 * Returns the header of the current request.
	 *
	 * @param string $name Name of header.
	 *
	 * @return null|string
	 */
	public function getHeader($name)
	{
		return $this->headers->get($name);
	}

	/**
	 * Returns the list of headers of the current request.
	 *
	 * @return HttpHeaders
	 */
	public function getHeaders()
	{
		return $this->headers;
	}

	/**
	 * Returns the COOKIES parameter of the current request.
	 *
	 * @param $name
	 * @return null|string
	 */
	public function getCookie($name)
	{
		return $this->cookies->get($name);
	}

	/**
	 * Returns the list of COOKIES parameters of the current request.
	 *
	 * @return Type\ParameterDictionary
	 */
	public function getCookieList()
	{
		return $this->cookies;
	}

	public function getCookieRaw($name)
	{
		return $this->cookiesRaw->get($name);
	}

	public function getCookieRawList()
	{
		return $this->cookiesRaw;
	}

	public function getJsonList()
	{
		return $this->jsonData;
	}

	public function getRemoteAddress()
	{
		return $this->server->get('REMOTE_ADDR');
	}

	/**
	 * Returns the User-Agent HTTP request header.
	 * @return null|string
	 */
	public function getUserAgent()
	{
		return $this->server->get('HTTP_USER_AGENT');
	}

	public function getRequestUri()
	{
		return $this->server->getRequestUri();
	}

	public function getRequestMethod()
	{
		return $this->server->getRequestMethod();
	}

	/**
	 * Returns server port.
	 *
	 * @return string | null
	 */
	public function getServerPort()
	{
		return $this->server->getServerPort();
	}

	public function isPost()
	{
		return ($this->getRequestMethod() == 'POST');
	}

	public function getAcceptedLanguages()
	{
		if ($this->acceptedLanguages === null)
		{
			$this->acceptedLanguages = [];

			$acceptedLanguages = explode(',', $this->server->get('HTTP_ACCEPT_LANGUAGE'));
			foreach ($acceptedLanguages as $language)
			{
				$lang = explode(';', $language);
				$this->acceptedLanguages[] = $lang[0];
			}
		}

		return $this->acceptedLanguages;
	}

	/**
	 * Returns the current page calculated from the request URI.
	 *
	 * @return string
	 */
	public function getRequestedPage()
	{
		if ($this->requestedPage === null)
		{
			if (($uri = $this->getRequestUri()) == '')
			{
				$this->requestedPage = parent::getRequestedPage();
			}
			else
			{
				$parsedUri = new Web\Uri("http://" . $this->server->getHttpHost() . $uri);
				$this->requestedPage = static::normalize(static::decode($parsedUri->getPath()));
			}
		}
		return $this->requestedPage;
	}

	/**
	 * Returns url-decoded and converted to the current encoding URI of the request (except the query string).
	 *
	 * @return string
	 */
	public function getDecodedUri()
	{
		$parsedUri = new Web\Uri("http://" . $this->server->getHttpHost() . $this->getRequestUri());

		$uri = static::decode($parsedUri->getPath());

		if (($query = $parsedUri->getQuery()) != '')
		{
			$uri .= "?" . $query;
		}

		return $uri;
	}

	protected static function decode($url)
	{
		return rawurldecode($url);
	}

	/**
	 * Returns the host from the server variable without a port number.
	 * @return string
	 */
	public function getHttpHost()
	{
		if ($this->httpHost === null)
		{
			//scheme can be anything, it's used only for parsing
			$url = new Web\Uri("http://" . $this->server->getHttpHost());
			$host = $url->getHost();
			$host = trim($host, "\t\r\n\0 .");

			$this->httpHost = $host;
		}

		return $this->httpHost;
	}

	public function isHttps()
	{
		if ($this->server->get("SERVER_PORT") == 443)
		{
			return true;
		}

		$https = $this->server->get("HTTPS");
		if ($https != '' && strtolower($https) != "off")
		{
			//From the PHP manual: Set to a non-empty value if the script was queried through the HTTPS protocol.
			//Note that when using ISAPI with IIS, the value will be off if the request was not made through the HTTPS protocol.
			return true;
		}

		return (Config\Configuration::getValue("https_request") === true);
	}

	public function modifyByQueryString($queryString)
	{
		if ($queryString != '')
		{
			parse_str($queryString, $vars);

			$this->values += $vars;
			$this->queryString->values += $vars;
		}
	}

	/**
	 * @param array $cookies
	 * @return array
	 */
	protected function prepareCookie(array $cookies)
	{
		static $cookiePrefix = null;
		if ($cookiePrefix === null)
		{
			$cookiePrefix = Config\Option::get("main", "cookie_name", "BITRIX_SM") . "_";
		}

		$cookiePrefixLength = mb_strlen($cookiePrefix);

		$cookiesCrypter = new Web\CookiesCrypter();
		$cookiesNew = $cookiesToDecrypt = [];
		foreach ($cookies as $name => $value)
		{
			if (!str_starts_with($name, $cookiePrefix))
			{
				continue;
			}

			$name = mb_substr($name, $cookiePrefixLength);
			if (is_string($value) && $cookiesCrypter->shouldDecrypt($name, $value))
			{
				$cookiesToDecrypt[$name] = $value;
			}
			else
			{
				$cookiesNew[$name] = $value;
			}
		}

		foreach ($cookiesToDecrypt as $name => $value)
		{
			$cookiesNew[$name] = $cookiesCrypter->decrypt($name, $value, $cookiesNew);
		}

		return $cookiesNew;
	}

	private function fetchHeaders(Server $server)
	{
		$headers = [];
		foreach ($server as $name => $value)
		{
			if (str_starts_with($name, 'HTTP_'))
			{
				$headerName = substr($name, 5);
				$headers[$headerName] = $value;
			}
			elseif (in_array($name, ['CONTENT_TYPE', 'CONTENT_LENGTH'], true))
			{
				$headers[$name] = $value;
			}
		}

		return $this->normalizeHeaders($headers);
	}

	private function normalizeHeaders(array $headers)
	{
		$normalizedHeaders = [];
		foreach ($headers as $name => $value)
		{
			$headerName = strtolower(str_replace('_', '-', $name));
			$normalizedHeaders[$headerName] = $value;
		}

		return $normalizedHeaders;
	}

	protected static function normalize($path)
	{
		if (str_ends_with($path, "/"))
		{
			$path .= "index.php";
		}

		$path = IO\Path::normalize($path);

		return $path;
	}

	/**
	 * Returns script file possibly corrected by urlrewrite.php.
	 *
	 * @return string
	 */
	public function getScriptFile()
	{
		$scriptName = $this->getScriptName();
		if ($scriptName == "/bitrix/routing_index.php" || $scriptName == "/bitrix/urlrewrite.php" || $scriptName == "/404.php")
		{
			if (($v = $this->server->get("REAL_FILE_PATH")) != null)
			{
				$scriptName = $v;
			}
		}
		return $scriptName;
	}

	/**
	 * Returns the array with predefined query parameters.
	 * @return array
	 */
	public static function getSystemParameters()
	{
		static $params = [
			"login",
			"login_form",
			"logout",
			"register",
			"forgot_password",
			"change_password",
			"confirm_registration",
			"confirm_code",
			"confirm_user_id",
			"bitrix_include_areas",
			"clear_cache",
			"show_page_exec_time",
			"show_include_exec_time",
			"show_sql_stat",
			"show_cache_stat",
			"show_link_stat",
			"sessid",
		];
		return $params;
	}

	/**
	 * Returns raw request data from php://input.
	 * @return bool|string
	 */
	public static function getInput()
	{
		return file_get_contents("php://input");
	}

	/**
	 * Returns Y if persistant cookies are enabled, N if disabled, or empty if unknown.
	 * @return null|string
	 */
	public function getCookiesMode()
	{
		return $this->getCookie(HttpResponse::STORE_COOKIE_NAME);
	}

	public function isJson(): bool
	{
		$contentType = $this->headers->getContentType();
		if (!$contentType)
		{
			return false;
		}
		if ($contentType === 'application/json')
		{
			return true;
		}

		return str_contains($contentType, '+json');
	}

	/**
	 * Decodes JSON from application/json requests.
	 */
	public function decodeJson(): void
	{
		if ($this->isJson())
		{
			try
			{
				$json = Web\Json::decode(static::getInput());
				if (is_array($json))
				{
					$this->jsonData = new Type\ParameterDictionary($json);
				}
			}
			catch (ArgumentException)
			{
			}
		}
	}
}
