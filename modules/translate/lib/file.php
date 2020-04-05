<?php
namespace Bitrix\Translate;

use Bitrix\Main\Text\BinaryString;
use Bitrix\Main;
use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Errorable;


class File
	extends Main\IO\File
	implements Errorable
{
	/** @var  ErrorCollection */
	protected $errorCollection;

	/**
	 * Constructor.
	 *
	 * @param string $path File path.
	 * @param string|null $siteId  Site Id.
	 */
	public function __construct($path, $siteId = null)
	{
		parent::__construct($path, $siteId);
	}

	/**
	 * Opens file for reading.
	 *
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

	/**
	 * Creates temporal file.
	 *
	 * @param string $prefix Name prefix.
	 * @param string $suffix Name suffix.
	 * @param float $timeToLive Hours to keep files alive.
	 *
	 * @return self
	 */
	public static function generateTemporalFile($prefix, $suffix = '.tmp', $timeToLive = 1)
	{
		$tempDir = \CTempFile::GetDirectoryName($timeToLive, array($prefix, uniqid($prefix, true)));
		\CheckDirPath($tempDir);

		$hash = str_pad(dechex(crc32($tempDir)), 8, '0', STR_PAD_LEFT);
		$fileName = uniqid($hash. '_', false). $suffix;

		return new static($tempDir. $fileName);
	}



	/**
	 * Adds error to error collection.
	 * @param Error $error Error.
	 *
	 * @return $this
	 */
	protected function addError(Error $error)
	{
		if (!$this->errorCollection instanceof ErrorCollection)
		{
			$this->errorCollection = new ErrorCollection;
		}

		$this->errorCollection[] = $error;

		return $this;
	}

	/**
	 * Adds list of errors to error collection.
	 * @param Error[] $errors Errors.
	 *
	 * @return $this
	 */
	protected function addErrors(array $errors)
	{
		if (!$this->errorCollection instanceof ErrorCollection)
		{
			$this->errorCollection = new ErrorCollection;
		}

		$this->errorCollection->add($errors);

		return $this;
	}

	/**
	 * Getting array of errors.
	 * @return Error[]
	 */
	final public function getErrors()
	{
		if (!$this->errorCollection instanceof ErrorCollection)
		{
			return array();
		}

		return $this->errorCollection->toArray();
	}

	/**
	 * Getting once error with the necessary code.
	 * @param string $code Code of error.
	 * @return Error|null
	 */
	final public function getErrorByCode($code)
	{
		if (!$this->errorCollection instanceof ErrorCollection)
		{
			return null;
		}

		return $this->errorCollection->getErrorByCode($code);
	}


	/**
	 * Getting array of errors.
	 * @return boolean
	 */
	public function hasErrors()
	{
		if (!$this->errorCollection instanceof ErrorCollection)
		{
			return false;
		}

		return $this->errorCollection->isEmpty();
	}
}
