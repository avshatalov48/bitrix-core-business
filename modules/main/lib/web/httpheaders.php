<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2014 Bitrix
 */
namespace Bitrix\Main\Web;

use Bitrix\Main\Context;
use Bitrix\Main\Text\Encoding;
use IteratorAggregate;
use Traversable;

class HttpHeaders implements IteratorAggregate
{
	protected $headers = array();

	public function __construct()
	{
	}

	/**
	 * Adds a header.
	 * @param string $name
	 * @param string $value
	 */
	public function add($name, $value)
	{
		$name = $this->refineString($name);
		$value = $this->refineString($value);

		$nameLower = strtolower($name);

		if (!isset($this->headers[$nameLower]))
		{
			$this->headers[$nameLower] = [
				"name" => $name,
				"values" => [],
			];
		}
		$this->headers[$nameLower]["values"][] = $value;
	}

	private function refineString($string)
	{
		return str_replace(["%0D", "%0A", "\r", "\n"], "", $string);
	}

	/**
	 * Sets a header value.
	 * @param string $name
	 * @param string|null $value
	 */
	public function set($name, $value)
	{
		$name = $this->refineString($name);
		if ($value !== null)
		{
			$value = $this->refineString($value);
		}
		$nameLower = strtolower($name);

		$this->headers[$nameLower] = [
			"name" => $name,
			"values" => [$value],
		];
	}

	/**
	 * Returns a header value by its name. If $returnArray is true then an array with multiple values is returned.
	 * @param string $name
	 * @param bool $returnArray
	 * @return null|string|array
	 */
	public function get($name, $returnArray = false)
	{
		$nameLower = strtolower($name);

		if (isset($this->headers[$nameLower]))
		{
			if ($returnArray)
			{
				return $this->headers[$nameLower]["values"];
			}

			return $this->headers[$nameLower]["values"][0];
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
	 * Clears all headers.
	 */
	public function clear()
	{
		unset($this->headers);
		$this->headers = [];
	}

	/**
	 * Returns the string representation for a HTTP request.
	 * @return string
	 */
	public function toString()
	{
		$str = "";
		foreach($this->headers as $header)
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
	 * Returns the content type part of the Content-Type header.
	 * @return null|string
	 */
	public function getContentType()
	{
		$contentType = $this->get("Content-Type");
		if ($contentType !== null)
		{
			$parts = explode(";", $contentType);
			return trim($parts[0]);
		}

		return null;
	}

	/**
	 * Returns the charset part of the Content-Type header.
	 * @return null|string
	 */
	public function getCharset()
	{
		$contentType = $this->get("Content-Type");
		if ($contentType !== null)
		{
			$parts = explode(";", $contentType);
			foreach ($parts as $part)
			{
				$values = explode("=", $part);
				if (strtolower(trim($values[0])) == "charset")
				{
					return trim($values[1]);
				}
			}
		}

		return null;
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
		$contentDisposition = $this->get('Content-disposition');
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
	public function getIterator()
	{
		$toIterate = [];
		foreach ($this->headers as $header)
		{
			if (count($header["values"]) > 1)
			{
				$toIterate[$header["name"]] = $header["values"];
			}
			else
			{
				$toIterate[$header["name"]] = $header["values"][0];
			}
		}

		return new \ArrayIterator($toIterate);
	}
}
