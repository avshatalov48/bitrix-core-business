<?php

namespace Bitrix\Main;

use Bitrix\Main\Type\ParameterDictionary;
use Bitrix\Main\Text\Encoding;

/**
 * Represents server.
 */
class Server extends ParameterDictionary
{
	/**
	 * Creates server object.
	 *
	 * @param array $arServer
	 */
	public function __construct(array $arServer)
	{
		if (isset($arServer["DOCUMENT_ROOT"]))
			$arServer["DOCUMENT_ROOT"] = rtrim($arServer["DOCUMENT_ROOT"], "/\\");

		parent::__construct($arServer);
	}

	public function addFilter(Type\IRequestFilter $filter)
	{
		$filteredValues = $filter->filter($this->values);

		if ($filteredValues != null)
			$this->setValuesNoDemand($filteredValues);
	}

	/**
	 * Returns server document root.
	 *
	 * @return string | null
	 */
	public function getDocumentRoot()
	{
		return $this->get("DOCUMENT_ROOT");
	}

	/**
	 * Returns custom root folder.
	 * Server variable BX_PERSONAL_ROOT is used. If empty - returns /bitrix.
	 *
	 * @return string | null
	 */
	public function getPersonalRoot()
	{
		$r = $this->get("BX_PERSONAL_ROOT");
		if ($r == null || $r == "")
			$r = "/bitrix";

		return $r;
	}

	/**
	 * Returns server http host.
	 *
	 * @return string | null
	 */
	public function getHttpHost()
	{
		return $this->get("HTTP_HOST");
	}

	/**
	 * Returns server name.
	 *
	 * @return string | null
	 */
	public function getServerName()
	{
		return $this->get("SERVER_NAME");
	}

	/**
	 * Returns server address.
	 *
	 * @return string | null
	 */
	public function getServerAddr()
	{
		return $this->get("SERVER_ADDR");
	}

	/**
	 * Returns remote address.
	 * @return string|null
	 */
	public function getRemoteAddr()
	{
		return $this->get("REMOTE_ADDR");
	}

	/**
	 * Returns user agent.
	 * @return string|null
	 */
	public function getUserAgent()
	{
		return $this->get("HTTP_USER_AGENT");
	}

	/**
	 * Returns server port.
	 *
	 * @return string | null
	 */
	public function getServerPort()
	{
		return $this->get("SERVER_PORT");
	}

	public function getRequestScheme()
	{
		return $this->get("REQUEST_SCHEME");
	}

	/**
	 * Returns requested uri.
	 * /index.php/test1/test2?login=yes&back_url_admin=/
	 *
	 * @return string | null
	 */
	public function getRequestUri()
	{
		return $this->get("REQUEST_URI");
	}

	/**
	 * Returns requested method.
	 *
	 * @return string | null
	 */
	public function getRequestMethod()
	{
		return $this->get("REQUEST_METHOD");
	}

	/**
	 * Returns PHP_SELF.
	 * /index.php/test1/test2
	 *
	 * @return string | null
	 */
	public function getPhpSelf()
	{
		return $this->get("PHP_SELF");
	}

	/**
	 * Returns SCRIPT_NAME.
	 * /index.php
	 *
	 * @return string | null
	 */
	public function getScriptName()
	{
		return $this->get("SCRIPT_NAME");
	}

	public function rewriteUri($url, $queryString, $redirectStatus = null)
	{
		$this->values["REQUEST_URI"] = $url;
		$this->values["QUERY_STRING"] = $queryString;
		if ($redirectStatus != null)
			$this->values["REDIRECT_STATUS"] = $redirectStatus;
	}

	public function transferUri($url, $queryString = "")
	{
		$this->values["REAL_FILE_PATH"] = $url;
		if ($queryString != "")
		{
			if (!isset($this->values["QUERY_STRING"]))
				$this->values["QUERY_STRING"] = "";
			if (isset($this->values["QUERY_STRING"]) && ($this->values["QUERY_STRING"] != ""))
				$this->values["QUERY_STRING"] .= "&";
			$this->values["QUERY_STRING"] .= $queryString;
		}
	}

	/**
	 * @return array|false
	 */
	public function parseAuthRequest()
	{
		$digest = '';

		if ($this['PHP_AUTH_USER'] != '')
		{
			// Basic Authorization PHP module
			return [
				'basic' => [
					'username' => Encoding::convertEncodingToCurrent($this['PHP_AUTH_USER']),
					'password' => Encoding::convertEncodingToCurrent($this['PHP_AUTH_PW']),
				]
			];
		}
		elseif ($this['PHP_AUTH_DIGEST'] != '')
		{
			// Digest Authorization PHP module
			$digest = $this['PHP_AUTH_DIGEST'];
		}
		else
		{
			if ($this['REDIRECT_REMOTE_USER'] !== null || $this['REMOTE_USER'] !== null)
			{
				$res = $this['REDIRECT_REMOTE_USER'] ?? $this['REMOTE_USER'];
				if ($res != '')
				{
					if(preg_match('/^\x20*Basic\x20+([a-zA-Z0-9+\/=]+)\s*$/D', $res, $matches))
					{
						// Basic Authorization PHP FastCGI (CGI)
						$res = trim($matches[1]);
						$res = base64_decode($res);
						$res = Encoding::convertEncodingToCurrent($res);
						[$user, $pass] = explode(':', $res, 2);
						if (mb_strpos($user, $this['HTTP_HOST']."\\") === 0)
						{
							$user = str_replace($this['HTTP_HOST']."\\", "", $user);
						}
						elseif (mb_strpos($user, $this['SERVER_NAME']."\\") === 0)
						{
							$user = str_replace($this['SERVER_NAME']."\\", "", $user);
						}

						return [
							'basic' => [
								'username' => $user,
								'password' => $pass,
							]
						];
					}
					elseif (preg_match('/^\x20*Digest\x20+(.*)$/sD', $res, $matches))
					{
						// Digest Authorization PHP FastCGI (CGI)
						$digest = trim($matches[1]);
					}
				}
			}
		}

		if($digest <> '' && ($data = static::parseDigest($digest)))
		{
			return ['digest' => $data];
		}

		return false;
	}

	protected static function parseDigest($digest)
	{
		$data = [];
		$parts = ['nonce' => 1, 'username' => 1, 'uri' => 1, 'response' => 1];
		$keys = implode('|', array_keys($parts));

		//from php help
		preg_match_all('@('.$keys.')=(?:([\'"])([^\2]+?)\2|([^\s,]+))@', $digest, $matches, PREG_SET_ORDER);

		foreach ($matches as $m)
		{
			$data[$m[1]] = ($m[3] ?: $m[4]);
			unset($parts[$m[1]]);
		}

		return ($parts? false : $data);
	}
}
