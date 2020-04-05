<?php
namespace Bitrix\Main;

use Bitrix\Main\Config;
use Bitrix\Main\Web;

class HttpResponse extends Response
{
	const STORE_COOKIE_NAME = "STORE_COOKIES";

	/** @var \Bitrix\Main\Web\Cookie[] */
	protected $cookies = array();

	/** @var array */
	protected $headers = array();

	/** @var \Bitrix\Main\Type\DateTime */
	protected $lastModified;

	public function flush($text = '')
	{
		$this->writeHeaders();
		$this->writeBody($text);
	}

	/**
	 *	Adds a HTTP header field to the response.
	 *
	 * @param string $name Header field name
	 * @param string $value Header field value
	 * @return $this
	 * @throws ArgumentNullException
	 * @throws ArgumentOutOfRangeException
	 */
	public function addHeader($name, $value = '')
	{
		if (empty($name))
			throw new ArgumentNullException("name");

		if (preg_match("/%0D|%0A|\r|\n/i", $name))
			throw new ArgumentOutOfRangeException("name");
		if (preg_match("/%0D|%0A|\r|\n/i", $value))
			throw new ArgumentOutOfRangeException("value");

		if ($value == "")
			$this->headers[] = $name;
		else
			$this->headers[] = array($name, $value);

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

	protected function writeHeaders()
	{
		if($this->lastModified !== null)
		{
			$this->setHeader(array("Last-Modified", gmdate("D, d M Y H:i:s", $this->lastModified->getTimestamp())." GMT"));
		}
		foreach ($this->headers as $header)
		{
			$this->setHeader($header);
		}
		foreach ($this->cookies as $cookie)
		{
			$this->setCookie($cookie);
		}
	}

	protected function setHeader($header)
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
			$server = Context::getCurrent()->getServer();
			$this->addHeader($server->get("SERVER_PROTOCOL")." ".$status);
		}

		return $this;
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
}
