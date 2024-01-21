<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2022 Bitrix
 */

namespace Bitrix\Main\Web;

use Bitrix\Main\Context;
use Bitrix\Main\Text\Encoding;
use IteratorAggregate;
use Traversable;

class HttpHeaders implements IteratorAggregate
{
	public const DEFAULT_HTTP_STATUS = 0;

	protected array $headers = [];
	protected string $version = '1.1';
	protected int $status;
	protected ?string $reasonPhrase = null;

	/**
	 * @param string[] | string[][] | null $headers
	 */
	public function __construct(array $headers = null)
	{
		if ($headers !== null)
		{
			foreach ($headers as $header => $value)
			{
				$this->add($header, $value);
			}
		}
	}

	/**
	 * Adds a header value.
	 * @param string $name
	 * @param string | array $value
	 * @throws \InvalidArgumentException
	 */
	public function add($name, $value)
	{
		// compatibility
		$name = (string)$name;

		// PSR-7: The implementation SHOULD reject invalid values and SHOULD NOT make any attempt to automatically correct the provided values.
		if ($name == '' || !static::validateName($name))
		{
			throw new \InvalidArgumentException("Invalid header name '{$name}'.");
		}

		if (!is_array($value))
		{
			$value = [$value];
		}

		foreach ($value as $key => $val)
		{
			$value[$key] = (string)$val;
			if (!static::validateValue($value[$key]))
			{
				throw new \InvalidArgumentException("Invalid header value '{$value[$key]}'.");
			}
		}

		$nameLower = strtolower($name);

		if (!isset($this->headers[$nameLower]))
		{
			$this->headers[$nameLower] = [
				'name' => $name,
				'values' => [],
			];
		}
		foreach ($value as $val)
		{
			$this->headers[$nameLower]['values'][] = $val;
		}
	}

	/**
	 * @see https://tools.ietf.org/html/rfc7230#section-3.2
	 * field-name     = token
	 * token          = 1*tchar
	 * tchar          = "!" / "#" / "$" / "%" / "&" / "'" / "*"
	 *                  / "+" / "-" / "." / "^" / "_" / "`" / "|" / "~"
	 *                  / DIGIT / ALPHA
	 */
	protected static function validateName(string $name): bool
	{
		return (!str_contains($name, "\0") && preg_match('/^[a-zA-Z0-9\'`#$%&*+.^_|~!-]+$/', $name));
	}

	/**
     * @see https://tools.ietf.org/html/rfc7230#section-3.2
	 * field-value    = *( field-content / obs-fold )
     * field-content  = field-vchar [ 1*( SP / HTAB ) field-vchar ]
     * field-vchar    = VCHAR / obs-text
     * VCHAR          = %x21-7E
     * obs-text       = %x80-FF
	 */
	protected static function validateValue(string $value): bool
	{
		return (!str_contains($value, "\0") && preg_match('/^[\x20\x09\x21-\x7E\x80-\xFF]*$/', $value));
	}

	/**
	 * Sets (replaces) a header value.
	 * @param string $name
	 * @param string | string[] $value
	 */
	public function set($name, $value)
	{
		$this->delete($name);
		$this->add($name, $value);
	}

	/**
	 * Returns a header value by its name. If $returnArray is true then an array with multiple values is returned.
	 * @param string $name
	 * @param bool $returnArray
	 * @return null | string | string[]
	 */
	public function get($name, $returnArray = false)
	{
		$nameLower = strtolower($name);

		if (isset($this->headers[$nameLower]))
		{
			if ($returnArray)
			{
				return $this->headers[$nameLower]['values'];
			}

			return $this->headers[$nameLower]['values'][0];
		}

		return null;
	}

	/**
	 * Deletes a header or headers by its name.
	 *
	 * @param string $name
	 * @return void
	 */
	public function delete($name)
	{
		$nameLower = strtolower($name);

		if (isset($this->headers[$nameLower]))
		{
			unset($this->headers[$nameLower]);
		}
	}

	/**
	 * Returns true if a header is set.
	 *
	 * @param string $name
	 * @return bool
	 */
	public function has(string $name): bool
	{
		$nameLower = strtolower($name);

		return isset($this->headers[$nameLower]);
	}

	/**
	 * Clears all headers.
	 */
	public function clear()
	{
		$this->headers = [];
	}

	/**
	 * Returns the string representation for an HTTP request.
	 * @return string
	 */
	public function toString()
	{
		$str = "";
		foreach ($this->headers as $header)
		{
			foreach ($header["values"] as $value)
			{
				$str .= $header["name"] . ": " . $value . "\r\n";
			}
		}

		return $str;
	}

	/**
	 * Returns headers as a raw array.
	 * @return array
	 */
	public function toArray()
	{
		return $this->headers;
	}

	/**
	 * Returns the content type part of the Content-Type header in lower case.
	 * @return null|string
	 */
	public function getContentType()
	{
		$contentType = $this->get("Content-Type");
		if ($contentType !== null)
		{
			$parts = explode(";", $contentType);

			// RFC 2045 says: "The type, subtype, and parameter names are not case-sensitive."
			return strtolower(trim($parts[0]));
		}

		return null;
	}

	/**
	 * Returns the specified attribute part of the Content-Type header.
	 * @return null|string
	 */
	public function getContentTypeAttribute(string $attribute)
	{
		$contentType = $this->get('Content-Type');
		if ($contentType !== null)
		{
			$attribute = strtolower($attribute);
			$parts = explode(';', $contentType);

			foreach ($parts as $part)
			{
				$values = explode('=', $part);
				if (strtolower(trim($values[0])) == $attribute)
				{
					return trim($values[1]);
				}
			}
		}

		return null;
	}

	/**
	 * Returns the boundary value of the Content-Type header.
	 * @return null|string
	 */
	public function getBoundary()
	{
		return $this->getContentTypeAttribute('boundary');
	}

	/**
	 * Returns the charset part of the Content-Type header.
	 * @return null|string
	 */
	public function getCharset()
	{
		return $this->getContentTypeAttribute('charset');
	}

	/**
	 * Returns disposition-type part of the Content-Disposition header
	 * @return null|string Disposition-type part of the Content-Disposition header if found or null otherwise.
	 */
	public function getContentDisposition()
	{
		$contentDisposition = $this->get("Content-Disposition");
		if ($contentDisposition !== null)
		{
			$parts = explode(";", $contentDisposition);

			return trim($parts[0]);
		}

		return null;
	}

	/**
	 * Returns a filename from the Content-disposition header.
	 *
	 * @return string|null Filename if it was found in the Content-disposition header or null otherwise.
	 */
	public function getFilename()
	{
		$contentDisposition = $this->get('Content-Disposition');
		if ($contentDisposition !== null)
		{
			$filename = null;
			$encoding = null;

			$contentElements = explode(';', $contentDisposition);
			foreach ($contentElements as $contentElement)
			{
				$contentElement = trim($contentElement);
				if (preg_match('/^filename\*=(.+)\'(.+)?\'(.+)$/', $contentElement, $matches))
				{
					$filename = $matches[3];
					$encoding = $matches[1];
					break;
				}
				elseif (preg_match('/^filename="(.+)"$/', $contentElement, $matches))
				{
					$filename = $matches[1];
				}
				elseif (preg_match('/^filename=(.+)$/', $contentElement, $matches))
				{
					$filename = $matches[1];
				}
			}

			if ($filename <> '')
			{
				$filename = urldecode($filename);

				if ($encoding <> '')
				{
					$charset = Context::getCurrent()->getCulture()->getCharset();
					$filename = Encoding::convertEncoding($filename, $encoding, $charset);
				}
			}

			return $filename;
		}

		return null;
	}

	/**
	 * Retrieve an external iterator
	 * @link https://php.net/manual/en/iteratoraggregate.getiterator.php
	 * @return Traversable An instance of an object implementing <b>Iterator</b> or
	 * <b>Traversable</b>
	 * @since 5.0.0
	 */
	public function getIterator(): Traversable
	{
		$toIterate = [];
		foreach ($this->headers as $header)
		{
			$toIterate[$header['name']] = $header['values'];
		}

		return new \ArrayIterator($toIterate);
	}

	/**
	 * Returns parsed cookies from 'set-cookie' headers.
	 * @return HttpCookies
	 */
	public function getCookies(): HttpCookies
	{
		$cookies = new HttpCookies();

		if ($this->has('set-cookie'))
		{
			foreach ($this->get('set-cookie', true) as $value)
			{
				$cookies->addFromString($value);
			}
		}

		return $cookies;
	}

	/**
	 * Retuns the headers as a two-dimentional array ('name' => values).
	 *
	 * @return string[][]
	 */
	public function getHeaders(): array
	{
		return iterator_to_array($this->getIterator());
	}

	/**
	 * Creates an object from a http response string.
	 *
	 * @param string $response
	 * @return HttpHeaders
	 */
	public static function createFromString(string $response): HttpHeaders
	{
		$headers = new static();

		$headerName = null;
		foreach (explode("\n", $response) as $k => $header)
		{
			if ($k == 0)
			{
				$headers->parseStatus($header);
			}
			elseif (preg_match("/^[ \\t]/", $header))
			{
				if ($headerName !== null)
				{
					try
					{
						$headers->add($headerName, trim($header));
					}
					catch (\InvalidArgumentException)
					{
						// ignore an invalid header
					}
				}
			}
			elseif (str_contains($header, ':'))
			{
				[$headerName, $headerValue] = explode(':', $header, 2);
				try
				{
					$headers->add($headerName, trim($headerValue));
				}
				catch (\InvalidArgumentException)
				{
					// ignore an invalid header
				}
			}
		}

		return $headers;
	}

	/**
	 * @param string $status
	 * @return $this
	 */
	public function parseStatus(string $status): HttpHeaders
	{
		if (preg_match('#^HTTP/(\S+) (\d+) *(.*)#', $status, $find))
		{
			$this->version = $find[1];
			$this->setStatus((int)$find[2], trim($find[3]));
		}

		return $this;
	}

	/**
	 * Sets HTTP status code and prase.
	 *
	 * @param int $status
	 * @param string|null $reasonPhrase
	 * @return $this
	 */
	public function setStatus(int $status, ?string $reasonPhrase = null): HttpHeaders
	{
		$this->status = $status;
		$this->reasonPhrase = $reasonPhrase;

		return $this;
	}

	public function getStatus(): int
	{
		return $this->status ?? self::DEFAULT_HTTP_STATUS;
	}

	public function setVersion(string $version): HttpHeaders
	{
		$this->version = $version;

		return $this;
	}

	public function getVersion(): ?string
	{
		return $this->version;
	}

	public function getReasonPhrase(): string
	{
		return $this->reasonPhrase ?? '';
	}
}
