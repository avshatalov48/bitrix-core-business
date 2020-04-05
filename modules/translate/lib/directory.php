<?php
namespace Bitrix\Translate;

use Bitrix\Translate;
use Bitrix\Main;
use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Errorable;


class Directory
	extends Main\IO\Directory
	implements Errorable
{
	/** @var  ErrorCollection */
	protected $errorCollection;

	/**
	 * Constructor.
	 * @param string $path Folder path.
	 * @param string|null $siteId Site id.
	 */
	public function __construct($path, $siteId = null)
	{
		parent::__construct($path, $siteId);
	}

	/**
	 * Creates temporal directory.
	 *
	 * @param string $prefix Name prefix.
	 * @param float $timeToLive Hours to keep files alive.
	 *
	 * @return self
	 */
	public static function generateTemporalDirectory($prefix, $timeToLive = 1)
	{
		$tempDirPath = \CTempFile::GetDirectoryName($timeToLive, array($prefix, uniqid($prefix, true)));
		$tempDir = new static($tempDirPath);
		if (!$tempDir->isExists())
		{
			$tempDir->create();
		}

		return $tempDir;
	}

	//region Errorable

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
	//endregion

	/**
	 * Copy full structure of the folders with its contents.
	 *
	 * @param Main\IO\Directory $target Destination folder.
	 * @param bool $reWrite Rewrire files.
	 * @param bool $recursive Recurcivly follow folder structure.
	 * @param bool $convertEncoding Allow encoding conver.
	 * @param string $sourceEncoding Encoding of source files.
	 * @param string $targetEncoding Target encoding.
	 *
	 * @return boolean
	 */
	public function copy(Main\IO\Directory $target, $reWrite = true, $recursive = false, $convertEncoding = false, $sourceEncoding = '', $targetEncoding = '')
	{
		if (strpos($target->getPhysicalPath(), $this->getPhysicalPath()) === 0)
		{
			$this->addError(new Error('Destination is inside in the source folder.'));

			return false;
		}
		if (!$this->isExists())
		{
			$this->addError(new Error('Source is not exists.'));

			return false;
		}
		if (!$target->isExists())
		{
			$target->create();
		}

		$retFlag = true;

		$children = $this->getChildren();

		/** @var Main\IO\Directory $dir */
		foreach ($children as $entry)
		{
			if (in_array($entry->getName(), Translate\IGNORE_FS_NAMES))
			{
				continue;
			}

			if (
				($entry instanceof Main\IO\Directory) &&
				$entry->isDirectory() &&
				$recursive
			)
			{
				$source = new self($entry->getPhysicalPath());
				$res = $source->copy(
					(new Main\IO\Directory($target->getPhysicalPath(). '/'. $entry->getName())),
					$reWrite,
					$recursive,
					$convertEncoding,
					$sourceEncoding,
					$targetEncoding
				);
				if (!$res)
				{
					$retFlag = false;
					$this->addErrors($source->getErrors());
				}

			}
			elseif (
				($entry instanceof Main\IO\File) &&
				$entry->isFile()
			)
			{
				$file = new Main\IO\File($target->getPhysicalPath(). '/'. $entry->getName());
				if ($file->isExists() && !$reWrite)
				{
					continue;
				}

				try
				{
					$content = $entry->getContents();
					$content = str_replace(array("\r\n", "\r"), array("\n", "\n"), $content);

					if ($convertEncoding)
					{
						$errorMessage = '';
						$content = \Bitrix\Main\Text\Encoding::convertEncoding($content, $sourceEncoding, $targetEncoding, $errorMessage);
						if (!$content && !empty($errorMessage))
						{
							$retFlag = false;
							$this->addError(new Error($errorMessage));
						}
					}

					$file->putContents($content);
				}
				catch (Main\IO\IoException $exception)
				{
					$retFlag = false;
					$this->addError(new Main\Error($exception->getMessage()));
				}
			}
		}

		/*
		global $APPLICATION;

		if (strpos($path_to."/", $path_from."/")===0)
		{
			return False;
		}

		if (is_dir($path_from))
		{
			CheckDirPath($path_to."/");
		}
		else
		{
			return True;
		}

		if ($handle = @opendir($path_from))
		{
			while (($file = readdir($handle)) !== false)
			{
				if ($file === '.' || $file === '..' || $file === '.access.php' || $file === '.htaccess')
				{
					continue;
				}

				if (is_dir($path_from."/".$file) && $recursive)
				{
					$this->copy(
						$path_from."/".$file,
						$path_to."/".$file,
						$reWrite,
						$recursive,
						$convertEncoding,
						$sourceEncoding,
						$targetEncoding
					);
				}
				elseif (is_file($path_from."/".$file))
				{
					if (file_exists($path_to."/".$file) && !$reWrite)
					{
						continue;
					}

					@copy($path_from."/".$file, $path_to."/".$file);
					@chmod($path_to."/".$file, BX_FILE_PERMISSIONS);
					$filesrc_tmp = $APPLICATION->GetFileContent($path_to."/".$file);
					$filesrc_tmp = str_replace("\r\n", "\n", $filesrc_tmp);
					$filesrc_tmp = str_replace("\r", "\n", $filesrc_tmp);
					if ($convertEncoding)
					{
						$filesrc_tmp = $APPLICATION->ConvertCharset($filesrc_tmp, $sourceEncoding, $targetEncoding);
					}
					$APPLICATION->SaveFileContent($path_to."/".$file, $filesrc_tmp);
				}
			}
			@closedir($handle);

			return true;
		}
		*/

		return $retFlag;
	}


	/**
	 * Copy only language folders with content.
	 *
	 * @param Main\IO\Directory $target Destination folder.
	 * @param string $languageId Language to filter.
	 * @param bool $convertEncoding Allow encoding conver.
	 * @param string $sourceEncoding Encoding of source files.
	 * @param string $targetEncoding Target encoding.
	 *
	 * @return boolean
	 */
	public function copyLangOnly(Main\IO\Directory $target, $languageId, $convertEncoding = false, $sourceEncoding = '', $targetEncoding = '')
	{
		if (strpos($target->getPhysicalPath(), $this->getPhysicalPath()) === 0)
		{
			$this->addError(new Error('Destination is inside in the source folder.'));

			return false;
		}
		if (!$this->isExists())
		{
			$this->addError(new Error('Source is not exists.'));

			return false;
		}

		$children = $this->getChildren();

		$retFlag = true;

		/** @var Main\IO\Directory $dir */
		foreach ($children as $dir)
		{
			$dirName = $dir->getName();
			if (
				!$dir instanceof Main\IO\Directory ||
				!$dir->isDirectory() ||
				in_array($dirName, Translate\IGNORE_FS_NAMES)
			)
			{
				continue;
			}

			if ($dirName === 'lang' || $dirName === 'payment')
			{
				$source = new self($dir->getPhysicalPath(). '/'. $languageId);
				if ($source->isExists())
				{
					if (!$target->isExists())
					{
						$target->create();
					}
					$targetDir = $target->createSubdirectory($dirName)->createSubdirectory($languageId);

					$res = $source->copy(
						$targetDir,
						true,
						true,
						$convertEncoding,
						$sourceEncoding,
						$targetEncoding
					);
					if (!$res)
					{
						$retFlag = false;
						$this->addErrors($source->getErrors());
					}
				}
			}
			else
			{
				$source = new self($dir->getPhysicalPath());
				$res = $source->copyLangOnly(
					(new Main\IO\Directory($target->getPhysicalPath(). '/'. $dirName)),
					$languageId,
					$convertEncoding,
					$sourceEncoding,
					$targetEncoding
				);
				if (!$res)
				{
					$retFlag = false;
					$this->addErrors($source->getErrors());
				}
			}
		}

		/*
		$handle = @opendir($pathFrom);
		if ($handle)
		{
			while (false !== ($dir = readdir($handle)))
			{
				if (!is_dir($pathFrom.'/'.$dir) || $dir == '.' || $dir == '..' || $dir == '.hg' || $dir == '.svn')
				{
					continue;
				}

				if ($dir == 'lang' || (strlen($pathFrom) -  strrpos($pathFrom, 'payment')) == 7)
				{
					if (file_exists($pathFrom."/".$dir."/".$languageId))
					{
						CheckDirPath($pathTo."/".$dir."/".$languageId."/");
						$this->copy(
							$pathFrom."/".$dir."/".$languageId,
							$pathTo."/".$dir."/".$languageId,
							true,
							true,
							$bConvert,
							$strEncodingIn,
							$strEncodingOut
						);
					}
				}
				else
				{
					$this->copyLangOnly(
						$pathFrom."/".$dir,
						$pathTo."/".$dir,
						$languageId,
						$bConvert,
						$strEncodingIn,
						$strEncodingOut
					);
				}
			}
			closedir($handle);
		}
		*/

		return $retFlag;
	}

}
