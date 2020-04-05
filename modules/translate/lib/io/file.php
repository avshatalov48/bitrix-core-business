<?php
namespace Bitrix\Translate\IO;

use Bitrix\Translate;
use Bitrix\Main\Text\BinaryString;
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
	 * @param float $timeToLive Hours to keep files alive.
	 *
	 * @return static
	 */
	public static function generateTemporalFile($prefix, $suffix = '.tmp', $timeToLive = 1)
	{
		$tempDir = \CTempFile::GetDirectoryName($timeToLive, array($prefix, uniqid($prefix, true)));
		Path::checkCreatePath($tempDir.'/');

		$hash = str_pad(dechex(crc32($tempDir)), 8, '0', STR_PAD_LEFT);
		$fileName = uniqid($hash. '_', false). $suffix;

		return new static($tempDir. $fileName);
	}

	/**
	 * Opens file for reading.
	 *
	 * @return bool
	 */
	public function openLoad()
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
	public function openWrite()
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
	public function read($length)
	{
		if (feof($this->filePointer))
		{
			return '';
		}

		return fread($this->filePointer, $length);
	}

	/**
	 * Write file.
	 *
	 * @param string $content Data to write.
	 *
	 * @return bool|int
	 */
	public function write($content)
	{
		if (!is_resource($this->filePointer))
		{
			return false;
		}

		return fwrite($this->filePointer, $content);
	}

	/**
	 * Closes the file.
	 *
	 * @return void
	 */
	public function close()
	{
		if (!is_resource($this->filePointer))
		{
			@fflush($this->filePointer);
		}

		parent::close();

		@clearstatcache(true, $this->getPhysicalPath());
	}
}
