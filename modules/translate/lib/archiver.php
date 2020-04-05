<?php
namespace Bitrix\Translate;

use Bitrix\Main;
use Bitrix\Translate;

require_once $_SERVER['DOCUMENT_ROOT']. '/bitrix/modules/main/classes/general/tar_gz.php';

class Archiver extends File
{
	/**@var \CArchiver */
	private $archive;

	/** @var bool  */
	private $canUseCompression = false;

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

	}

	/**
	 * Checks zlib|gzcompress availability.
	 *
	 * @return boolean
	 */
	public static function libAvailable()
	{
		return (extension_loaded('zlib') || function_exists('gzcompress'));
	}


		/**
	 * Pack language folder.
	 *
	 * @param Translate\Directory $directory Folder to pack into archive.
	 *
	 * @return boolean
	 */
	public function pack(Translate\Directory $directory)
	{
		$this->archive = new \CArchiver($this->getPhysicalPath(), $this->canUseCompression);
		$this->archive->_strSeparator = '|';

		$res = $this->archive->Add($directory->getPhysicalPath(), false, $directory->getDirectory()->getPhysicalPath());

		if (!$res)
		{
			$errors = $this->archive->GetErrors();
			if (count($errors) > 0)
			{
				foreach ($errors as $errorMessage)
				{
					$this->addError(new Main\Error($errorMessage[1], $errorMessage[0]));
				}
			}
		}

		return ($this->hasErrors() !== true);
	}

	/**
	 * Extract language archive into target folder.
	 *
	 * @param Translate\Directory $target Folder to extract files into it.
	 *
	 * @return boolean
	 */
	public function extract(Translate\Directory $target)
	{
		$this->archive = new \CArchiver($this->getPhysicalPath(), true);
		$this->archive->_strSeparator = '|';

		$res = $this->archive->extractFiles($target->getPhysicalPath());

		if (!$res)
		{
			$errors = $this->archive->GetErrors();
			if (count($errors) > 0)
			{
				foreach ($errors as $errorMessage)
				{
					$this->addError(new Main\Error($errorMessage[1], $errorMessage[0]));
				}
			}
		}

		return ($this->hasErrors() !== true);
	}
}
