<?php

namespace Bitrix\Main\IO;

use Bitrix\Main;

abstract class FileSystemEntry
{
	protected $path;
	protected $originalPath;
	protected $pathPhysical;
	protected $siteId;
	protected ?bool $exists = null;

	public function __construct($path, $siteId = null)
	{
		if ($path == '')
		{
			throw new InvalidPathException($path);
		}

		$this->originalPath = $path;
		$this->path = Path::normalize($path);
		$this->siteId = $siteId;

		if ($this->path == '')
		{
			throw new InvalidPathException($path);
		}
	}

	public function isSystem()
	{
		if (preg_match("#/\\.#", $this->path))
		{
			return true;
		}

		$documentRoot = static::getDocumentRoot($this->siteId);

		if (mb_substr($this->path, 0, mb_strlen($documentRoot)) === $documentRoot)
		{
			$relativePath = mb_substr($this->path, mb_strlen($documentRoot));
			$relativePath = ltrim($relativePath, "/");
			if (($pos = mb_strpos($relativePath, "/")) !== false)
			{
				$s = mb_substr($relativePath, 0, $pos);
			}
			else
			{
				$s = $relativePath;
			}
			$s = mb_strtolower(rtrim($s, "."));

			$ar = [
				"bitrix" => 1,
				Main\Config\Option::get("main", "upload_dir", "upload") => 1,
				"local" => 1,
				"urlrewrite.php" => 1,
			];
			if (isset($ar[$s]))
			{
				return true;
			}
		}

		return false;
	}

	public function getName()
	{
		return Path::getName($this->path);
	}

	public function getDirectoryName()
	{
		return Path::getDirectory($this->path);
	}

	public function getPath()
	{
		return $this->path;
	}

	public function getDirectory()
	{
		return new Directory($this->getDirectoryName());
	}

	public function getCreationTime()
	{
		if (!$this->isExists())
		{
			throw new FileNotFoundException($this->originalPath);
		}

		return filectime($this->getPhysicalPath());
	}

	public function getLastAccessTime()
	{
		if (!$this->isExists())
		{
			throw new FileNotFoundException($this->originalPath);
		}

		return fileatime($this->getPhysicalPath());
	}

	public function getModificationTime()
	{
		if (!$this->isExists())
		{
			throw new FileNotFoundException($this->originalPath);
		}

		return filemtime($this->getPhysicalPath());
	}

	abstract public function isExists();

	abstract public function isDirectory();

	abstract public function isFile();

	abstract public function isLink();

	abstract public function markWritable();

	public function getPermissions()
	{
		if (!$this->isExists())
		{
			throw new FileNotFoundException($this->originalPath);
		}

		return fileperms($this->getPhysicalPath());
	}

	abstract public function delete();

	public function getPhysicalPath()
	{
		if (is_null($this->pathPhysical))
		{
			$this->pathPhysical = Path::convertLogicalToPhysical($this->path);
		}

		return $this->pathPhysical;
	}

	public function rename($newPath)
	{
		$newPathNormalized = Path::normalize($newPath);

		$success = true;
		if ($this->isExists())
		{
			$success = rename($this->getPhysicalPath(), Path::convertLogicalToPhysical($newPathNormalized));
		}

		if ($success)
		{
			$this->originalPath = $newPath;
			$this->path = $newPathNormalized;
			$this->pathPhysical = null;
			$this->exists = null;
		}

		return $success;
	}

	protected static function getDocumentRoot($siteId)
	{
		if ($siteId === null)
		{
			$documentRoot = Main\Application::getDocumentRoot();
		}
		else
		{
			$documentRoot = Main\SiteTable::getDocumentRoot($siteId);
		}
		return $documentRoot;
	}
}
