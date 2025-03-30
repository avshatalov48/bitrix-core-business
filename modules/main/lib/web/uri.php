<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2024 Bitrix
 */

namespace Bitrix\Main\Web;

use Bitrix\Main;
use Bitrix\Main\Text\Encoding;
use Psr\Http\Message\UriInterface;

class Uri implements \JsonSerializable, UriInterface
{
	protected $scheme = '';
	protected $host = '';
	protected $port = null;
	protected $user = '';
	protected $pass = '';
	protected $path = '';
	protected $query = '';
	protected $fragment = '';

	/**
	 * @param string $url
	 */
	public function __construct($url)
	{
		if (str_starts_with($url, '/'))
		{
			//we don't support "current scheme" e.g. "//host/path"
			$url = '/' . ltrim($url, '/');
		}

		$parsedUrl = parse_url($url);

		if ($parsedUrl !== false)
		{
			$this->scheme = strtolower($parsedUrl['scheme'] ?? '');
			$this->setHost($parsedUrl['host'] ?? '');
			$this->port = $parsedUrl['port'] ?? null;
			$this->setUser($parsedUrl['user'] ?? '');
			$this->setPass($parsedUrl['pass'] ?? '');
			$this->path = $parsedUrl['path'] ?? '';
			$this->query = $parsedUrl['query'] ?? '';
			$this->fragment = $parsedUrl['fragment'] ?? '';
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
		$uri = '';

		$scheme = $this->getScheme();
		if ($scheme != '')
		{
			$uri .= $scheme . ':';
		}

		$authority = $this->getAuthority();
		if ($authority != '')
		{
			$uri .= '//' . $authority;
		}

		$uri .= $this->getPathQuery();

		return $uri;
	}

	/**
	 * Return the URI with a fragment, if any.
	 * @return string
	 */
	public function getUri()
	{
		$url = $this->getLocator();
		$fragment = $this->getFragment();

		if ($fragment != '')
		{
			$url .= '#' . $fragment;
		}

		return $url;
	}

	/**
	 * @inheritdoc
	 */
	public function getFragment(): string
	{
		return $this->fragment;
	}

	/**
	 * @inheritdoc
	 */
	public function getHost(): string
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
		$this->host = strtolower($host);
		return $this;
	}

	/**
	 * Returns the rawurlencoded password.
	 * @return string
	 */
	public function getPass()
	{
		return rawurlencode($this->pass);
	}

	/**
	 * Decodes and sets the password.
	 * @param string $pass Password,
	 * @return $this
	 */
	public function setPass($pass)
	{
		$this->pass = rawurldecode($pass);
		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function getPath(): string
	{
		// TODO: make it work as described
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
		$pathQuery = $this->getPath();

		if ($pathQuery == '')
		{
			$pathQuery = '/';
		}

		$query = $this->getQuery();

		if ($query != '')
		{
			$pathQuery .= '?' . $query;
		}

		return $pathQuery;
	}

	/**
	 * @inheritdoc
	 */
	public function getPort(): ?int
	{
		if ($this->port === null)
		{
			switch ($this->getScheme())
			{
				case 'https':
					return 443;
				case 'http':
					return 80;
				default:
					return null;
			}
		}
		return (int)$this->port;
	}

	/**
	 * @inheritdoc
	 */
	public function getQuery(): string
	{
		return $this->query;
	}

	/**
	 * @inheritdoc
	 */
	public function getScheme(): string
	{
		return $this->scheme;
	}

	/**
	 * Returns the rawurlencoded user.
	 * @return string
	 */
	public function getUser()
	{
		return rawurlencode($this->user);
	}

	/**
	 * Decodes and sets the user.
	 * @param string $user User.
	 * @return $this
	 */
	public function setUser($user)
	{
		$this->user = rawurldecode($user);
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
		$query = $this->getQuery();
		if ($query != '')
		{
			if ($preserveDots)
			{
				$currentParams = static::parseParams($query);
			}
			else
			{
				$currentParams = [];
				parse_str($query, $currentParams);
			}

			foreach($params as $param)
			{
				unset($currentParams[$param]);
			}

			$this->query = http_build_query($currentParams, '', '&', PHP_QUERY_RFC3986);
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
		$currentParams = [];
		$query = $this->getQuery();

		if ($query != '')
		{
			if ($preserveDots)
			{
				$currentParams = static::parseParams($query);
			}
			else
			{
				parse_str($query, $currentParams);
			}
		}

		$currentParams = array_replace($currentParams, $params);

		$this->query = http_build_query($currentParams, '', '&', PHP_QUERY_RFC3986);

		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function __toString(): string
	{
		return $this->getUri();
	}

	/**
	 * Specify data which should be serialized to JSON
	 * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
	 * @return string data which can be serialized by <b>json_encode</b>,
	 * which is a value of any type other than a resource.
	 * @since 5.4.0
	 */
	public function jsonSerialize(): string
	{
		return $this->getUri();
	}

	/**
	 * Converts the host to punycode.
	 * @return string|Main\Error
	 */
	public function convertToPunycode()
	{
		$host = \CBXPunycode::ToASCII($this->getHost(), $encodingErrors);

		if (!empty($encodingErrors))
		{
			return new Main\Error(implode("\n", $encodingErrors));
		}

		$this->setHost($host);

		return $host;
	}

	/**
	 * Converts the host to Unicode.
	 * @return string|Main\Error
	 */
	public function convertToUnicode()
	{
		$host = \CBXPunycode::ToUnicode($this->getHost(), $encodingErrors);

		if (!empty($encodingErrors))
		{
			return new Main\Error(implode("\n", $encodingErrors));
		}

		$this->setHost($host);

		return $host;
	}

	/**
	 * Searches for /../ and ulrencoded /../
	 */
	public function isPathTraversal(): bool
	{
		return (bool)preg_match("#(?:/|2f|^|\\\\|5c)(?:(?:%0*(25)*2e)|\\.){2,}(?:/|%0*(25)*2f|\\\\|%0*(25)*5c|$)#i", $this->getPath());
	}

	/**
	 * Encodes the URI string without parsing it.
	 * @param $str
	 * @param $charset
	 * @return string
	 */
	public static function urnEncode($str, $charset = 'UTF-8')
	{
		$result = '';
		$parts = preg_split("#(://|:\\d+/|/|\\?|=|&)#", $str, -1, PREG_SPLIT_DELIM_CAPTURE);

		if ($charset === false)
		{
			foreach ($parts as $i => $part)
			{
				$result .= ($i % 2) ? $part : rawurlencode($part);
			}
		}
		else
		{
			$currentCharset = Main\Context::getCurrent()->getCulture()->getCharset();
			foreach ($parts as $i => $part)
			{
				$result .= ($i % 2)	? $part	: rawurlencode(Encoding::convertEncoding($part, $currentCharset, $charset));
			}
		}
		return $result;
	}

	/**
	 * Decodes the URI string without parsing it.
	 * @param $str
	 * @param $charset
	 * @return string
	 */
	public static function urnDecode($str, $charset = false)
	{
		$result = '';
		$parts = preg_split("#(://|:\\d+/|/|\\?|=|&)#", $str, -1, PREG_SPLIT_DELIM_CAPTURE);

		if ($charset === false)
		{
			foreach ($parts as $i => $part)
			{
				$result .= ($i % 2) ? $part : rawurldecode($part);
			}
		}
		else
		{
			$currentCharset = Main\Context::getCurrent()->getCulture()->getCharset();
			foreach ($parts as $i => $part)
			{
				$result .= ($i % 2) ? $part : rawurldecode(Encoding::convertEncoding($part, $charset, $currentCharset));
			}
		}
		return $result;
	}

	/**
	 * Converts a relative uri to the absolute one using given host name or the host from the current context server object.
	 * @param string | null $host
	 * @return $this
	 */
	public function toAbsolute(string $host = null): Uri
	{
		if ($this->host == '')
		{
			$request = Main\HttpContext::getCurrent()->getRequest();

			$this->scheme = $request->isHttps() ? 'https' : 'http';

			if ($host !== null)
			{
				$this->host = preg_replace('/:(443|80)$/', '', $host);
			}
			else
			{
				$this->host = $request->getHttpHost();
			}
		}
		return $this;
	}

	/**
	 * Converts the relative URI to the absolute one within a context of a base URI.
	 *
	 * @see https://www.rfc-editor.org/rfc/rfc3986#section-5
	 * @param Uri $base
	 * @return $this
	 */
	public function resolveRelativeUri(Uri $base): Uri
	{
		if (empty($this->scheme))
		{
			if (empty($this->getAuthority()))
			{
				if (empty($this->getPath()))
				{
					$this->setPath($base->getPath());

					if (empty($this->getQuery()))
					{
						$this->query = $base->getQuery();
					}
				}
				else
				{
					if (!str_starts_with($this->getPath(), '/'))
					{
						$basePath = $base->getPath();

						if (!empty($base->getAuthority()) && empty($basePath))
						{
							$this->setPath('/' . $this->getPath());
						}
						else
						{
							if (($p = strrpos($basePath, '/')) !== false)
							{
								$this->setPath(substr($basePath, 0, $p + 1) . $this->getPath());
							}
						}
					}
				}

				// authority
				$this->setUser($base->getUser());
				$this->setPass($base->getPass());
				$this->setHost($base->getHost());
				$this->port = $base->getPort();
			}

			$this->scheme = $base->getScheme();
		}

		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function getAuthority(): string
	{
		$authority = '';

		$userInfo = $this->getUserInfo();
		if ($userInfo != '')
		{
			$authority = $userInfo . '@';
		}

		$host = $this->getHost();
		if ($host != '')
		{
			$authority .= $host;
			$port = $this->getPort();

			if ($port !== null)
			{
				if (($this->scheme == 'http' && $port != 80) || ($this->scheme == 'https' && $port != 443))
				{
					$authority .= ':' . $port;
				}
			}
		}

		return $authority;
	}

	/**
	 * @inheritdoc
	 */
	public function getUserInfo(): string
	{
		$user = $this->getUser();

		if ($user != '')
		{
			$password = $this->getPass();
			return $user . ($password != '' ? ':' . $password : '');
		}

		return '';
	}

	/**
	 * @inheritdoc
	 */
	public function withScheme(string $scheme): UriInterface
	{
		$new = clone $this;
		$new->scheme = $scheme;

		return $new;
	}

	/**
	 * @inheritdoc
	 */
	public function withUserInfo(string $user, ?string $password = null): UriInterface
	{
		$new = clone $this;
		$new
			->setUser($user)
			->setPass((string)$password)
		;

		return $new;
	}

	/**
	 * @inheritdoc
	 */
	public function withHost(string $host): UriInterface
	{
		$new = clone $this;
		$new->setHost($host);

		return $new;
	}

	/**
	 * @inheritdoc
	 */
	public function withPort(?int $port): UriInterface
	{
		$new = clone $this;
		$new->port = $port;

		return $new;
	}

	/**
	 * @inheritdoc
	 */
	public function withPath(string $path): UriInterface
	{
		$new = clone $this;
		$new->setPath($path);

		return $new;
	}

	/**
	 * @inheritdoc
	 */
	public function withQuery(string $query): UriInterface
	{
		$new = clone $this;
		$new->query = $query;

		return $new;
	}

	/**
	 * @inheritdoc
	 */
	public function withFragment(string $fragment): UriInterface
	{
		$new = clone $this;
		$new->fragment = $fragment;

		return $new;
	}
}
