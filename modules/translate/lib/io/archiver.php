<?php
namespace Bitrix\Translate\IO;

use Bitrix\Main;
use Bitrix\Translate;
use Bitrix\Main\Errorable;

require_once $_SERVER['DOCUMENT_ROOT']. '/bitrix/modules/main/classes/general/tar_gz.php';

class Archiver
	extends Main\IO\File
	implements Translate\IErrorable
{
	// trait implements interface Translate\IErrorable
	use Translate\Error;

	/**@var \CArchiver */
	private $archive;

	/** @var bool */
	private $canUseCompression = false;

	/** @var string */
	private $seekPath;

	/** @var int */
	private $processedFileCount = 0;

	/**
	 * Archiver constructor.
	 *
	 * @param string $path End point archive file path.
	 * @param string|null $siteId Site id.
	 */
	public function __construct($path, $siteId = null)
	{
		parent::__construct($path, $siteId);

		$this->canUseCompression = self::libAvailable();

		$this->archive = new \CArchiver($this->getPhysicalPath(), $this->canUseCompression);
		$this->archive->_strSeparator = '|';
	}

	/**
	 * Checks zlib|gzcompress availability.
	 *
	 * @return boolean
	 */
	public static function libAvailable()
	{
		return (\extension_loaded('zlib') || \function_exists('gzcompress'));
	}

	/**
	 * Defines packing/unpacking options.
	 *
	 * @param array $options An array with the options' names and their values.
	 * @see \CArchiver::setOptions()
	 *
	 * @return void
	 */
	public function setOptions($options)
	{
		$this->archive->setOptions($options);
	}

	/**
	 * Sets packing seek position path.
	 * @param string $seekPath Seek position.
	 * @return void
	 */
	public function setSeekPosition($seekPath)
	{
		$this->seekPath = $seekPath;
	}

	/**
	 * Gets packing seek position path.
	 * @return string
	 */
	public function getSeekPosition()
	{
		return $this->seekPath;
	}

	/**
	 * Gets processed file count by archive.
	 * @return int
	 */
	public function getProcessedFileCount()
	{
		return $this->processedFileCount;
	}


	/**
	 * Pack language folder.
	 *
	 * @param Translate\IO\Directory $directory Folder to pack into archive.
	 * @param string $seekPath Continue process from this path.
	 *
	 * @return int
	 */
	public function pack(Translate\IO\Directory $directory, $seekPath = '')
	{
		$this->setOptions(array(
			'ADD_PATH' => $directory->getName(),
			'REMOVE_PATH' => $directory->getPhysicalPath(),
		));

		if (empty($seekPath) && !empty($this->seekPath))
		{
			$seekPath = $this->seekPath;
		}

		$counter = \Closure::bind(
			function()
			{
				if ($this instanceof \CArchiver)
				{
					/** @noinspection */
					return \count($this->lastFile) + ($this->tempres == "continue" ? 1 : 0);
				}
				return -1;
			},
			$this->archive,
			'\CArchiver'
		);

		$res = $this->archive->pack([$directory->getPhysicalPath()], $seekPath);

		switch ($res)
		{
			case \IBXArchive::StatusContinue:
				$this->setSeekPosition($this->archive->getStartFile());
				$this->processedFileCount = $counter();
				break;

			case \IBXArchive::StatusSuccess:
				$this->processedFileCount = $counter();
				break;

			case \IBXArchive::StatusError:
				$errors = $this->archive->getErrors();
				if (count($errors) > 0)
				{
					foreach ($errors as $errorMessage)
					{
						$this->addError(new Main\Error($errorMessage[1], $errorMessage[0]));
					}
				}
				break;
		}

		return $res;//($this->hasErrors() !== true);
	}

	/**
	 * Extract language archive into target folder.
	 *
	 * @param Translate\IO\Directory $target Folder to extract files into it.
	 *
	 * @return boolean
	 */
	public function extract(Translate\IO\Directory $target)
	{
		$unpack = \Closure::bind(
			function($path)
			{
				if ($this instanceof \CArchiver)
				{
					/** @noinspection */
					$this->_arErrors = array();

					$listDetail = array();

					if ($result = $this->_openRead())
					{
						$result = $this->_extractList($path, $listDetail, 'complete', array(), '');
						$this->_close();
					}
					if ($result)
					{
						return \count($listDetail);
					}
				}
				return -1;
			},
			$this->archive,
			'\CArchiver'
		);

		//$res = $this->archive->extractFiles($target->getPhysicalPath());
		$res = $unpack($target->getPhysicalPath());

		if ($res < 0)
		{
			$errors = $this->archive->getErrors();
			if (\count($errors) > 0)
			{
				foreach ($errors as $errorMessage)
				{
					$this->addError(new Main\Error($errorMessage[1], $errorMessage[0]));
				}
			}
		}
		else
		{
			$this->processedFileCount = $res;
		}

		return ($this->hasErrors() !== true);
	}
}
