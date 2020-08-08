<?php declare(strict_types = 1);

namespace Bitrix\Translate\IO;

use Bitrix\Translate;
use Bitrix\Main;


class Directory
	extends Main\IO\Directory
	implements Translate\IErrorable
{
	// trait implements interface Translate\IErrorable
	use Translate\Error;

	/**
	 * Constructor.
	 * @param string $path Folder path.
	 * @param string|null $siteId Site id.
	 */
	public function __construct(string $path, ?string $siteId = null)
	{
		parent::__construct($path, $siteId);
	}

	/**
	 * Creates temporal directory.
	 *
	 * @param string $prefix Name prefix.
	 * @param int $timeToLive Hours to keep files alive.
	 *
	 * @return self
	 */
	public static function generateTemporalDirectory(string $prefix, int $timeToLive = 3): self
	{
		$tempDirPath = \CTempFile::GetDirectoryName($timeToLive, array($prefix, uniqid($prefix, true)));
		$tempDir = new static($tempDirPath);
		if (!$tempDir->isExists())
		{
			$tempDir->create();
		}

		return $tempDir;
	}


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
	public function copy(
		Main\IO\Directory $target,
		bool $reWrite = true,
		bool $recursive = false,
		bool $convertEncoding = false,
		string $sourceEncoding = '',
		string $targetEncoding = ''
	): bool
	{
		if (mb_strpos($target->getPhysicalPath(), $this->getPhysicalPath()) === 0)
		{
			$this->addError(new Main\Error('Destination is inside in the source folder.'));

			return false;
		}
		if (!$this->isExists())
		{
			$this->addError(new Main\Error('Source is not exists.'));

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
			if (in_array($entry->getName(), Translate\IGNORE_FS_NAMES, true))
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
							$this->addError(new Main\Error($errorMessage));
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
	public function copyLangOnly(
		Main\IO\Directory $target,
		string $languageId,
		bool $convertEncoding = false,
		string $sourceEncoding = '',
		string $targetEncoding = ''
	): bool
	{
		if (mb_strpos($target->getPhysicalPath(), $this->getPhysicalPath()) === 0)
		{
			$this->addError(new Main\Error('Destination is inside in the source folder.'));

			return false;
		}
		if (!$this->isExists())
		{
			$this->addError(new Main\Error('Source is not exists.'));

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
				in_array($dirName, Translate\IGNORE_FS_NAMES, true)
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

		return $retFlag;
	}


	/**
	 * Wipes folder out of children.
	 *
	 * @param \Closure|null $filter Filter function.
	 * @return bool
	 */
	public function wipe(?\Closure $filter = null): bool
	{
		if (!$this->isExists())
		{
			throw new Main\IO\FileNotFoundException($this->originalPath);
		}

		if($this->getPath() === '/')
		{
			throw new Main\IO\InvalidPathException($this->originalPath);
		}

		$children = $this->getChildren();
		$result = true;
		foreach ($children as $entry)
		{
			if ($filter instanceof \Closure)
			{
				if ($filter($entry) !== true)
				{
					continue;
				}
			}
			$result = $entry->delete();

			if (!$result)
			{
				break;
			}
		}
		if ($result)
		{
			clearstatcache();
		}

		return $result;
	}
}
