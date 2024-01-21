<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2023 Bitrix
 */

namespace Bitrix\Main\Web\Http\Socket;

use Bitrix\Main\Web\Http;

class Stream extends Http\Stream
{
	protected string $address;
	protected int $socketTimeout = 30;
	protected int $streamTimeout = 60;
	protected array $contextOptions = [];
	protected bool $async = false;
	protected int $lastTime = 0;

	/**
	 * @param string $address
	 * @param array $options
	 */
	public function __construct(string $address, array $options = [])
	{
		$this->address = $address;

		if (isset($options['socketTimeout']))
		{
			$this->socketTimeout = (int)$options['socketTimeout'];
		}
		if (isset($options['streamTimeout']))
		{
			$this->streamTimeout = (int)$options['streamTimeout'];
		}
		if (isset($options['contextOptions']))
		{
			$this->contextOptions = $options['contextOptions'];
		}
		if (isset($options['async']))
		{
			$this->async = (bool)$options['async'];
		}
	}

	/**
	 * Connects asynchronously.
	 * @return void
	 */
	public function connect(): void
	{
		$context = stream_context_create($this->contextOptions);

		$flags = STREAM_CLIENT_CONNECT;
		if ($this->async)
		{
			$flags |= STREAM_CLIENT_ASYNC_CONNECT;
		}

		// $context can be FALSE
		if ($context)
		{
			$res = stream_socket_client($this->address, $errno, $errstr, $this->socketTimeout, $flags, $context);
		}
		else
		{
			$res = stream_socket_client($this->address, $errno, $errstr, $this->socketTimeout, $flags);
		}

		if (is_resource($res))
		{
			$this->resource = $res;

			if ($this->streamTimeout > 0)
			{
				stream_set_timeout($this->resource, $this->streamTimeout);
				$this->lastTime = time();
			}

			$this->setBlocking(false);
		}
		else
		{
			throw new \RuntimeException($errno > 0 ? "[{$errno}] {$errstr}" : 'Socket connection error.');
		}
	}

	/**
	 * @return false|string
	 */
	public function gets()
	{
		$result = fgets($this->resource);

		if ($result !== false && $this->streamTimeout > 0)
		{
			$this->lastTime = time();
		}

		if ($this->timedOut())
		{
			throw new \RuntimeException('Stream reading timeout has been reached.');
		}

		return $result;
	}

	/**
	 * @inheritdoc
	 */
	public function read(int $length): string
	{
		$result = parent::read($length);

		if ($result !== '' && $this->streamTimeout > 0)
		{
			$this->lastTime = time();
		}

		if ($this->timedOut())
		{
			throw new \RuntimeException('Stream reading timeout has been reached.');
		}

		return $result;
	}

	/**
	 * @inheritdoc
	 */
	public function write(string $string): int
	{
		// Handle E_NOTICE from fwrite()
		set_error_handler(function ($errno, $errstr) {
			throw new \RuntimeException($errstr);
		});

		try
		{
			$result = parent::write($string);
		}
		finally
		{
			restore_error_handler();
		}

		if ($this->streamTimeout > 0)
		{
			$this->lastTime = time();
		}

		if ($this->timedOut())
		{
			throw new \RuntimeException('Stream writing timeout has been reached.');
		}

		return $result;
	}

	/**
	 * Sets blocking mode on a socket.
	 *
	 * @param bool $enable
	 * @return bool
	 */
	public function setBlocking(bool $enable = true): bool
	{
		return stream_set_blocking($this->resource, $enable);
	}

	/**
	 * @return null|resource
	 */
	public function getResource()
	{
		return $this->resource;
	}

	/**
	 * Enables SSL on an open socket.
	 *
	 * @param bool $enable
	 * @return bool|int
	 */
	public function enableCrypto(bool $enable = true)
	{
		return stream_socket_enable_crypto($this->resource, $enable, STREAM_CRYPTO_METHOD_ANY_CLIENT);
	}

	/**
	 * Checks if the socket was timed out.
	 *
	 * @return bool
	 */
	public function timedOut(): bool
	{
		if ($this->streamTimeout > 0)
		{
			if ($this->getMetadata('timed_out'))
			{
				return true;
			}

			if (time() > $this->lastTime + $this->streamTimeout)
			{
				return true;
			}
		}

		return false;
	}
}
