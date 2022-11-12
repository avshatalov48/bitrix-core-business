<?php

namespace Bitrix\Translate\IO;

use Bitrix\Translate;
use Bitrix\Main;


class File
	extends Main\IO\File
	implements Translate\IErrorable
{
	// trait implements interface Translate\IErrorable
	use Translate\Error;

	/**
	 * Creates temporal file.
	 *
	 * @param string $prefix Name prefix.
	 * @param string $suffix Name suffix.
	 * @param int $timeToLive Hours to keep files alive.
	 *
	 * @return static
	 */
	public static function generateTemporalFile(string $prefix, string $suffix = '.tmp', int $timeToLive = 3): self
	{
		$tempDir = \CTempFile::getDirectoryName($timeToLive, array($prefix, \uniqid($prefix, true)));
		Path::checkCreatePath($tempDir.'/');

		$hash = \str_pad(\dechex(\crc32($tempDir)), 8, '0', STR_PAD_LEFT);
		$fileName = \uniqid($hash. '_', false). $suffix;

		return new static($tempDir. $fileName);
	}

	/**
	 * Opens file for reading.
	 *
	 * @return bool
	 */
	public function openLoad(): bool
	{
		if ($this->isExists())
		{
			$this->open(Main\IO\FileStreamOpenMode::READ);
		}

		return $this->isExists() && $this->isReadable();
	}

	/**
	 * Opens file for writing.
	 *
	 * @return bool
	 */
	public function openWrite(): bool
	{
		$this->open(Main\IO\FileStreamOpenMode::WRITE);

		return $this->isWritable();
	}


	/**
	 * Read file.
	 *
	 * @param int $length Amount bytes to read.
	 *
	 * @return string
	 */
	public function read(int $length): string
	{
		if (\feof($this->filePointer))
		{
			return '';
		}

		return \fread($this->filePointer, $length);
	}

	/**
	 * Write file.
	 *
	 * @param string $content Data to write.
	 *
	 * @return int
	 * @throws Main\IO\FileNotOpenedException
	 * @throws Main\IO\IoException
	 */
	public function write(string $content): int
	{
		if (!\is_resource($this->filePointer))
		{
			throw new Main\IO\FileNotOpenedException($this->getPath());
		}

		$length = \fwrite($this->filePointer, $content);
		if ($length === false)
		{
			throw new Main\IO\IoException("Cannot write file");
		}

		return $length;
	}

	/**
	 * Closes the file.
	 *
	 * @return void
	 * @throws Main\IO\FileNotOpenedException
	 */
	public function close(): void
	{
		if (!\is_resource($this->filePointer))
		{
			@\fflush($this->filePointer);
		}

		parent::close();

		@clearstatcache(true, $this->getPhysicalPath());
	}
}
