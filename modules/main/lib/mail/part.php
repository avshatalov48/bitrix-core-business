<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2018 Bitrix
 */
namespace Bitrix\Main\Mail;

/**
 * Class Part
 * @package Bitrix\Main\Mail
 */
class Part
{
	/** @var [] $headers Headers. */
	protected $headers = [];

	/** @var string $body Body. */
	protected $body = '';

	/** @var string $eol Symbol of end-of-line. */
	protected $eol;

	/**
	 * Multipart constructor.
	 */
	public function __construct()
	{
		$this->eol = Mail::getMailEol();
	}

	/**
	 * Get EOL.
	 *
	 * @return string
	 */
	public function getEol()
	{
		return $this->eol;
	}

	/**
	 * Set EOL.
	 *
	 * @param string $eol
	 * @return $this
	 */
	public function setEol($eol)
	{
		$this->eol = $eol;
		return $this;
	}

	/**
	 * Add header.
	 *
	 * @param string $name Name.
	 * @param string $value Value.
	 * @return $this
	 */
	public function addHeader($name, $value)
	{
		$this->headers[$name] = $value;
		return $this;
	}

	/**
	 * Set headers.
	 *
	 * @param array $headers Headers.
	 * @return $this
	 */
	public function setHeaders(array $headers)
	{
		$this->headers = [];
		foreach ($headers as $name => $value)
		{
			$this->addHeader($name, $value);
		}
		return $this;
	}

	/**
	 * Get headers.
	 *
	 * @return array
	 */
	public function getHeaders()
	{
		return $this->headers;
	}

	/**
	 * Get header.
	 *
	 * @param string $name Name.
	 * @return mixed|null
	 */
	public function getHeader($name)
	{
		return isset($this->headers[$name]) ? $this->headers[$name] : null;
	}

	/**
	 * Set body.
	 *
	 * @param string $body Body.
	 * @return $this
	 */
	public function setBody($body)
	{
		$this->body = $body;
		return $this;
	}

	/**
	 * Get body.
	 *
	 * @return string
	 */
	public function getBody()
	{
		return $this->body;
	}

	/**
	 * Convert to string.
	 *
	 * @return string
	 */
	public function toStringBody()
	{
		return $this->splitBody($this->body) . $this->eol . $this->eol;
	}

	/**
	 * Convert headers to string.
	 *
	 * @return string
	 */
	public function toStringHeaders()
	{
		$result = '';
		foreach ($this->headers as $name => $value)
		{
			$result .= $name . ': '. $value . $this->eol;
		}

		return $result ? $result  : '';
	}

	/**
	 * Convert object to string.
	 *
	 * @return string
	 */
	public function toString()
	{
		return $this->toStringHeaders() . $this->eol . $this->toStringBody();
	}

	/**
	 * Magic method.
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->toString();
	}

	protected function splitBody(&$body)
	{
		if ($this->getHeader('Content-Transfer-Encoding') === 'base64')
		{
			return rtrim(chunk_split(base64_encode($body), 76, $this->eol));
		}
		elseif ($this->getHeader('Content-Transfer-Encoding') === 'quoted-printable')
		{
			return str_replace(
				$this->eol !== "\r\n" ? "=\r\n" : '',
				'=' . $this->eol,
				quoted_printable_encode($body)
			);
		}
		else
		{
			return preg_replace(
				'/(.{1,990})(?:\s|$)|(.{990})/S',
				'$1$2' . $this->eol,
				$body
			);
		}
	}
}
