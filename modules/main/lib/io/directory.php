<?php

namespace Bitrix\Main\IO;

class Directory extends DirectoryEntry
{
	public function __construct($path, $siteId = null)
	{
		parent::__construct($path, $siteId);
	}

	public function isExists()
	{
		if ($this->exists === null)
		{
			$p = $this->getPhysicalPath();
			$this->exists = file_exists($p) && is_dir($p);
		}

		return $this->exists;
	}

	public function delete()
	{
		$this->exists = null;
		return self::deleteInternal($this->getPhysicalPath());
	}

	private static function deleteInternal($path)
	{
		if (is_file($path) || is_link($path))
		{
			if (!@unlink($path))
			{
				throw new FileDeleteException($path);
			}
		}
		elseif (is_dir($path))
		{
			if ($handle = opendir($path))
			{
				while (($file = readdir($handle)) !== false)
				{
					if ($file == "." || $file == "..")
					{
						continue;
					}

					self::deleteInternal(Path::combine($path, $file));
				}
				closedir($handle);
			}
			if (!@rmdir($path))
			{
				throw new FileDeleteException($path);
			}
		}

		return true;
	}

	/**
	 * @return array|FileSystemEntry[]
	 * @throws FileNotFoundException
	 */
	public function getChildren()
	{
		if (!$this->isExists())
		{
			throw new FileNotFoundException($this->originalPath);
		}

		$arResult = [];

		if ($handle = opendir($this->getPhysicalPath()))
		{
			while (($file = readdir($handle)) !== false)
			{
				if ($file == "." || $file == "..")
				{
					continue;
				}

				$pathLogical = Path::combine($this->path, Path::convertPhysicalToLogical($file));
				$pathPhysical = Path::combine($this->getPhysicalPath(), $file);
				if (is_dir($pathPhysical))
				{
					$arResult[] = new Directory($pathLogical, $this->siteId);
				}
				else
				{
					$arResult[] = new File($pathLogical, $this->siteId);
				}
			}
			closedir($handle);
		}

		return $arResult;
	}

	/**
	 * @param $name
	 * @return Directory|DirectoryEntry
	 */
	public function createSubdirectory($name)
	{
		$dir = new Directory(Path::combine($this->path, $name));
		if (!$dir->isExists())
		{
			$dir->exists = null;
			try
			{
				mkdir($dir->getPhysicalPath(), BX_DIR_PERMISSIONS, true);
			}
			catch (\ErrorException $exception)
			{
				if (!$dir->isExists())
				{
					throw $exception;
				}
			}
		}

		return $dir;
	}

	public function markWritable()
	{
		if (!$this->isExists())
		{
			throw new FileNotFoundException($this->originalPath);
		}

		@chmod($this->getPhysicalPath(), BX_DIR_PERMISSIONS);
	}

	/**
	 * @param $path
	 *
	 * @return Directory
	 */
	public static function createDirectory($path)
	{
		$dir = new self($path);
		$dir->create();

		return $dir;
	}

	public static function deleteDirectory($path)
	{
		$dir = new self($path);
		$dir->delete();
	}

	public static function isDirectoryExists($path)
	{
		$f = new self($path);
		return $f->isExists();
	}
}
