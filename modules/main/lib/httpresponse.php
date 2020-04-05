<?php
namespace Bitrix\Main;

use Bitrix\Main\Config;
use Bitrix\Main\Web;

class HttpResponse extends Response
{
	const STORE_COOKIE_NAME = "STORE_COOKIES";

	/** @var \Bitrix\Main\Web\Cookie[] */
	protected $cookies = array();

	/** @var Web\HttpHeaders */
	protected $headers;

	/** @var \Bitrix\Main\Type\DateTime */
	protected $lastModified;

	protected $backgroundJobs = [];

	public function __construct()
	{
		parent::__construct();

		$this->initializeHeaders();
	}

	protected function initializeHeaders()
	{
		if ($this->headers === null)
		{
			$this->setHeaders(new Web\HttpHeaders());
		}

		return $this;
	}

	public function flush($text = '')
	{
		if (empty($this->backgroundJobs))
		{
			$this->writeHeaders();
			$this->writeBody($text);
		}
		else
		{
			$this->closeConnection($text);
			$this->runBackgroundJobs();
		}
	}

	/**
	 *	Adds a HTTP header field to the response.
	 *
	 * @param string $name Header field name
	 * @param string $value Header field value
	 * @return $this
	 * @throws ArgumentNullException
	 */
	public function addHeader($name, $value = '')
	{
		if (empty($name))
			throw new ArgumentNullException("name");

		$this->getHeaders()->add($name, $value);

		return $this;
	}

	/**
	 * Sets a collection of HTTP headers.
	 * @param Web\HttpHeaders $headers Headers collection.
	 *
	 * @return $this
	 */
	public function setHeaders(Web\HttpHeaders $headers)
	{
		$this->headers = $headers;

		return $this;
	}

	/**
	 * Adds a cookie to the response.
	 *
	 * @param Web\Cookie $cookie The cookie.
	 * @param bool $replace Replace an existing cookie or not.
	 * @param bool $checkExpires Check expires value of the cookie or not.
	 * @return $this
	 */
	public function addCookie(Web\Cookie $cookie, $replace = true, $checkExpires = true)
	{
		$key = $cookie->getName().".".$cookie->getDomain().".".$cookie->getPath();
		if($replace || !isset($this->cookies[$key]))
		{
			if($checkExpires && $cookie->getExpires() > 0)
			{
				//it's a persistent cookie; we should check if we're allowed to store persistent cookies
				static $askToStore = null;
				if($askToStore === null)
				{
					$askToStore = Config\Option::get("main", "ask_to_store_cookies");
				}
				if($askToStore === "Y")
				{
					if(Context::getCurrent()->getRequest()->getCookiesMode() !== "Y")
					{
						//user declined to store cookies - we're using session cookies only
						$cookie->setExpires(0);
					}
				}
			}

			$this->cookies[$key] = $cookie;
		}
		return $this;
	}

	/**
	 * Remembers user's choice about storing persistent cookies.
	 * @param bool $mode
	 */
	public function allowPersistentCookies($mode)
	{
		if($mode === true)
		{
			//persistent cookie to remember
			$cookie = new Web\Cookie(self::STORE_COOKIE_NAME, "Y");
		}
		else
		{
			//session cookie: we're not allowed to store persistent cookies
			$cookie = new Web\Cookie(self::STORE_COOKIE_NAME, "N", 0);
		}
		$this->addCookie($cookie, true, false);
	}

	/**
	 * @return Web\Cookie[]
	 */
	public function getCookies()
	{
		return $this->cookies;
	}

	/**
	 * @return Web\HttpHeaders
	 */
	public function getHeaders()
	{
		$this->initializeHeaders();

		return $this->headers;
	}

	protected function writeHeaders()
	{
		if($this->lastModified !== null)
		{
			$this->flushHeader(array("Last-Modified", gmdate("D, d M Y H:i:s", $this->lastModified->getTimestamp()) . " GMT"));
		}

		foreach ($this->getHeaders() as $name => $values)
		{
			if (is_array($values))
			{
				foreach ($values as $value)
				{
					$this->flushHeader([$name, $value]);
				}
			}
			elseif($values !== '')
			{
				$this->flushHeader([$name, $values]);
			}
			else
			{
				$this->flushHeader($name);
			}
		}

		foreach ($this->cookies as $cookie)
		{
			$this->setCookie($cookie);
		}
	}

	protected function flushHeader($header)
	{
		if (is_array($header))
			header(sprintf("%s: %s", $header[0], $header[1]));
		else
			header($header);

		return $this;
	}

	protected function setCookie(Web\Cookie $cookie)
	{
		if ($cookie->getSpread() & Web\Cookie::SPREAD_DOMAIN)
		{
			setcookie(
				$cookie->getName(),
				$cookie->getValue(),
				$cookie->getExpires(),
				$cookie->getPath(),
				$cookie->getDomain(),
				$cookie->getSecure(),
				$cookie->getHttpOnly()
			);
		}

		return $this;
	}

	/**
	 * Sets the HTTP status of the response.
	 *
	 * @param string $status
	 * @return $this
	 * @throws ArgumentNullException
	 * @throws ArgumentOutOfRangeException
	 */
	public function setStatus($status)
	{
		$httpStatus = Config\Configuration::getValue("http_status");

		$cgiMode = (stristr(php_sapi_name(), "cgi") !== false);
		if ($cgiMode && (($httpStatus == null) || ($httpStatus == false)))
		{
			$this->addHeader("Status", $status);
		}
		else
		{
			$httpHeaders = $this->getHeaders();
			$httpHeaders->delete($this->getStatus());

			$server = Context::getCurrent()->getServer();
			$this->addHeader($server->get("SERVER_PROTOCOL")." ".$status);
		}

		return $this;
	}

	/**
	 * Returns the HTTP status of the response.
	 * @return int|string|null
	 */
	public function getStatus()
	{
		$cgiStatus = $this->getHeaders()->get('Status');
		if ($cgiStatus)
		{
			return $cgiStatus;
		}

		$prefixStatus = strtolower(Context::getCurrent()->getServer()->get("SERVER_PROTOCOL") . ' ');
		$prefixStatusLength = strlen($prefixStatus);
		foreach ($this->getHeaders() as $name => $value)
		{
			if (substr(strtolower($name), 0, $prefixStatusLength) === $prefixStatus)
			{
				return $name;
			}
		}

		return null;
	}

	/**
	 * Sets the latest time for the Last-Modified header field.
	 *
	 * @param Type\DateTime $time
	 * @return $this
	 */
	public function setLastModified(Type\DateTime $time)
	{
		if($this->lastModified === null || $time->getTimestamp() > $this->lastModified->getTimestamp())
		{
			$this->lastModified = $time;
		}

		return $this;
	}

	public function addBackgroundJob(callable $job, array $args = [])
	{
		$this->backgroundJobs[] = [$job, $args];

		return $this;
	}

	protected function runBackgroundJobs()
	{
		$lastException = null;

		foreach ($this->backgroundJobs as $job)
		{
			try
			{
				call_user_func_array($job[0], $job[1]);
			}
			catch (\Exception $exception)
			{
				$lastException = $exception;
			}
		}

		if ($lastException !== null)
		{
			throw $lastException;
		}
	}

	private function closeConnection($content = "")
	{
		while (@ob_end_clean());

		ob_start();

		echo $content;

		$size = ob_get_length();

		$this
			->addHeader('Connection', 'close')
			->addHeader('Content-Encoding', 'none')
			->addHeader('Content-Length', $size)
		;

		$this->writeHeaders();

		ob_end_flush();
		@ob_flush();
		flush();

		if (function_exists("fastcgi_finish_request"))
		{
			fastcgi_finish_request();
		}
	}
}
