<?php

namespace Bitrix\Main\Web\Http;

use Psr\Http\Message\StreamInterface;
use Bitrix\Main\ArgumentException;

class Stream implements StreamInterface
{
	/**
	 * @var resource
	 */
	protected $resource;

	/**
	 * @param string | resource | Stream $stream
	 * @param string $mode
	 * @throws ArgumentException
	 */
	public function __construct($stream, $mode = 'r')
	{
		if (is_resource($stream))
		{
			$this->resource = $stream;
		}
		elseif ($stream instanceof Stream)
		{
			$this->resource = $stream->resource;
		}
		elseif (is_string($stream))
		{
			$this->resource = fopen($stream, $mode);
		}
		else
		{
			throw new ArgumentException('Stream must be a Stream object, a string identifier, or a resource.', 'stream');
		}
	}

	/**
	 * @inheritdoc
	 */
	public function __toString()
	{
		if (!$this->isReadable())
		{
			return '';
		}

		try
		{
			$this->rewind();
			return $this->getContents();
		}
		catch (\RuntimeException $e)
		{
			return '';
		}
	}

	/**
	 * @inheritdoc
	 */
	public function close()
	{
		if ($this->resource)
		{
			$resource = $this->detach();
			fclose($resource);
		}
	}

	/**
	 * @inheritdoc
	 */
	public function detach()
	{
		$resource = $this->resource;
		$this->resource = null;
		return $resource;
	}

	/**
	 * @inheritdoc
	 */
	public function getSize()
	{
		if ($this->resource !== null)
		{
			$stats = fstat($this->resource);
			return $stats['size'];
		}
		return null;
	}

	/**
	 * @inheritdoc
	 */
	public function tell()
	{
		if (!$this->resource)
		{
			throw new \RuntimeException('No resource available, cannot tell position.');
		}

		$result = ftell($this->resource);
		if ($result === false)
		{
			throw new \RuntimeException('Error occurred during tell operation.');
		}

		return $result;
	}

	/**
	 * @inheritdoc
	 */
	public function eof()
	{
		if ($this->resource)
		{
			return feof($this->resource);
		}
		return true;
	}

	/**
	 * @inheritdoc
	 */
	public function isSeekable()
	{
		if ($this->resource)
		{
			return $this->getMetadata('seekable');
		}

		return false;
	}

	/**
	 * @inheritdoc
	 */
	public function seek($offset, $whence = SEEK_SET)
	{
		if (!$this->resource)
		{
			throw new \RuntimeException('No resource available, cannot seek position.');
		}

		if (!$this->isSeekable())
		{
			throw new \RuntimeException('Stream is not seekable.');
		}

		$result = fseek($this->resource, $offset, $whence);

		if ($result !== 0)
		{
			throw new \RuntimeException('Error seeking within stream.');
		}

		return true;
	}

	/**
	 * @inheritdoc
	 */
	public function rewind()
	{
		return $this->seek(0);
	}

	/**
	 * @inheritdoc
	 */
	public function isWritable()
	{
		if ($this->resource)
		{
			return is_writable($this->getMetadata('uri'));
		}

		return false;
	}

	/**
	 * @inheritdoc
	 */
	public function write($string)
	{
		if (!$this->resource)
		{
			throw new \RuntimeException('No resource available, cannot write.');
		}

		$result = fwrite($this->resource, $string);

		if ($result === false)
		{
			throw new \RuntimeException('Error writing to stream.');
		}

		return $result;
	}

	/**
	 * @inheritdoc
	 */
	public function isReadable()
	{
		if ($this->resource)
		{
			$mode = $this->getMetadata('mode');

			return (strpos($mode, 'r') !== false || strpos($mode, '+') !== false);
		}

		return false;
	}

	/**
	 * @inheritdoc
	 */
	public function read($length)
	{
		if (!$this->resource)
		{
			throw new \RuntimeException('No resource available, cannot read.');
		}

		if (!$this->isReadable())
		{
			throw new \RuntimeException('Stream is not readable.');
		}

		$result = fread($this->resource, $length);

		if ($result === false)
		{
			throw new \RuntimeException('Error reading stream.');
		}

		return $result;
	}

	/**
	 * @inheritdoc
	 */
	public function getContents()
	{
		if (!$this->isReadable())
		{
			return '';
		}

		$result = stream_get_contents($this->resource);

		if ($result === false)
		{
			throw new \RuntimeException('Error reading stream.');
		}

		return $result;
	}

	/**
	 * @inheritdoc
	 */
	public function getMetadata($key = null)
	{
		$meta = stream_get_meta_data($this->resource);

		if ($key === null)
		{
			return $meta;
		}

		return $meta[$key] ?? null;
	}

	public function copyTo($stream)
	{
		$this->rewind();
		return stream_copy_to_stream($this->resource, $stream);
	}
}
