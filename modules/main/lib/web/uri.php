<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2014 Bitrix
 */
namespace Bitrix\Main\Web;

class Uri implements \JsonSerializable
{
	protected $scheme;
	protected $host;
	protected $port;
	protected $user;
	protected $pass;
	protected $path;
	protected $query;
	protected $fragment;

	/**
	 * @param string $url
	 */
	public function __construct($url)
	{
		if(mb_strpos($url, "/") === 0)
		{
			//we don't support "current scheme" e.g. "//host/path"
			$url = "/".ltrim($url, "/");
		}

		$parsedUrl = parse_url($url);

		if($parsedUrl !== false)
		{
			$this->scheme = (isset($parsedUrl["scheme"])? mb_strtolower($parsedUrl["scheme"]) : "http");
			$this->host = (isset($parsedUrl["host"])? $parsedUrl["host"] : "");
			if(isset($parsedUrl["port"]))
			{
				$this->port = $parsedUrl["port"];
			}
			else
			{
				$this->port = ($this->scheme == "https"? 443 : 80);
			}
			$this->user = (isset($parsedUrl["user"])? $parsedUrl["user"] : "");
			$this->pass = (isset($parsedUrl["pass"])? $parsedUrl["pass"] : "");
			$this->path = (isset($parsedUrl["path"])? $parsedUrl["path"] : "/");
			$this->query = (isset($parsedUrl["query"])? $parsedUrl["query"] : "");
			$this->fragment = (isset($parsedUrl["fragment"])? $parsedUrl["fragment"] : "");
		}
	}

	/**
	 * @deprecated Use getLocator() or getUri().
	 */
	public function getUrl()
	{
		return $this->getLocator();
	}

	/**
	 * Return the URI without a fragment.
	 * @return string
	 */
	public function getLocator()
	{
		$url = "";
		if($this->host <> '')
		{
			$url .= $this->scheme."://".$this->host;

			if(($this->scheme == "http" && $this->port <> 80) || ($this->scheme == "https" && $this->port <> 443))
			{
				$url .= ":".$this->port;
			}
		}

		$url .= $this->getPathQuery();

		return $url;
	}

	/**
	 * Return the URI with a fragment, if any.
	 * @return string
	 */
	public function getUri()
	{
		$url = $this->getLocator();

		if($this->fragment <> '')
		{
			$url .= "#".$this->fragment;
		}

		return $url;
	}

	/**
	 * Returns the fragment.
	 * @return string
	 */
	public function getFragment()
	{
		return $this->fragment;
	}

	/**
	 * Returns the host.
	 * @return string
	 */
	public function getHost()
	{
		return $this->host;
	}

	/**
	 * Sets the host
	 * @param string $host Host name.
	 * @return $this
	 */
	public function setHost($host)
	{
		$this->host = $host;
		return $this;
	}

	/**
	 * Returns the password.
	 * @return string
	 */
	public function getPass()
	{
		return $this->pass;
	}

	/**
	 * Sets the password.
	 * @param string $pass Password,
	 * @return $this
	 */
	public function setPass($pass)
	{
		$this->pass = $pass;
		return $this;
	}

	/**
	 * Returns the path.
	 * @return string
	 */
	public function getPath()
	{
		return $this->path;
	}

	/**
	 * Sets the path.
	 * @param string $path
	 * @return $this
	 */
	public function setPath($path)
	{
		$this->path = $path;
		return $this;
	}

	/**
	 * Returns the path with the query.
	 * @return string
	 */
	public function getPathQuery()
	{
		$pathQuery = $this->path;
		if($this->query <> "")
		{
			$pathQuery .= '?'.$this->query;
		}
		return $pathQuery;
	}

	/**
	 * Returns the port number.
	 * @return string
	 */
	public function getPort()
	{
		return $this->port;
	}

	/**
	 * Returns the query.
	 * @return string
	 */
	public function getQuery()
	{
		return $this->query;
	}

	/**
	 * Returns the scheme.
	 * @return string
	 */
	public function getScheme()
	{
		return $this->scheme;
	}

	/**
	 * Returns the user.
	 * @return string
	 */
	public function getUser()
	{
		return $this->user;
	}

	/**
	 * Sets the user.
	 * @param string $user User.
	 * @return $this
	 */
	public function setUser($user)
	{
		$this->user = $user;
		return $this;
	}

	/**
	 * Extended parsing to allow dots and spaces in parameters names.
	 * @param string $params
	 * @return array
	 */
	protected static function parseParams($params)
	{
		$data = preg_replace_callback(
			'/(?:^|(?<=&))[^=[]+/',
			function($match)
			{
				return bin2hex(urldecode($match[0]));
			},
			$params
		);

		parse_str($data, $values);

		return array_combine(array_map('hex2bin', array_keys($values)), $values);
	}

	/**
	 * Deletes parameters from the query.
	 * @param array $params Parameters to delete.
	 * @param bool $preserveDots Special treatment of dots and spaces in the parameters names.
	 * @return $this
	 */
	public function deleteParams(array $params, $preserveDots = false)
	{
		if($this->query <> '')
		{
			if($preserveDots)
			{
				$currentParams = static::parseParams($this->query);
			}
			else
			{
				$currentParams = array();
				parse_str($this->query, $currentParams);
			}

			foreach($params as $param)
			{
				unset($currentParams[$param]);
			}

			$this->query = http_build_query($currentParams, "", "&");
		}
		return $this;
	}

	/**
	 * Adds parameters to query or replaces existing ones.
	 * @param array $params Parameters to add.
	 * @param bool $preserveDots Special treatment of dots and spaces in the parameters names.
	 * @return $this
	 */
	public function addParams(array $params, $preserveDots = false)
	{
		$currentParams = array();
		if($this->query <> '')
		{
			if($preserveDots)
			{
				$currentParams = static::parseParams($this->query);
			}
			else
			{
				parse_str($this->query, $currentParams);
			}
		}

		$currentParams = array_replace($currentParams, $params);

		$this->query = http_build_query($currentParams, "", "&");

		return $this;
	}

	public function __toString()
	{
		return $this->getUri();
	}

	/**
	 * Specify data which should be serialized to JSON
	 * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
	 * @return mixed data which can be serialized by <b>json_encode</b>,
	 * which is a value of any type other than a resource.
	 * @since 5.4.0
	 */
	public function jsonSerialize()
	{
		return $this->getUri();
	}

	/**
	 * Converts the host to punycode.
	 * @return string|\Bitrix\Main\Error
	 */
	public function convertToPunycode()
	{
		$host = \CBXPunycode::ToASCII($this->getHost(), $encodingErrors);

		if(!empty($encodingErrors))
		{
			return new \Bitrix\Main\Error(implode("\n", $encodingErrors));
		}

		$this->setHost($host);

		return $host;
	}
}
