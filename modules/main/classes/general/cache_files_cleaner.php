<?php

class CFileCacheCleaner
{
	private $cacheType;
	private $path;
	private $currentBase;
	private $currentPath;
	private $fileTree;
	private $rootDir;

	public function __construct($cacheType, $rootDir = null)
	{
		global $DB;

		$this->cacheType = $cacheType;
		$this->rootDir = $rootDir ?? $_SERVER["DOCUMENT_ROOT"];

		switch ($this->cacheType)
		{
			case "menu":
				$this->path = [
					BX_PERSONAL_ROOT . '/managed_cache/' . $DB->type . '/menu/',
				];
				break;
			case "managed":
				$this->path = [
					BX_PERSONAL_ROOT . '/managed_cache/',
					BX_PERSONAL_ROOT . '/stack_cache/',
				];
				break;
			case "html":
				$this->path = [
					BX_PERSONAL_ROOT . '/html_pages/',
				];
				break;
			case "expired":
				$this->path = [
					BX_PERSONAL_ROOT . '/cache/',
					BX_PERSONAL_ROOT . '/managed_cache/',
					BX_PERSONAL_ROOT . '/stack_cache/',
				];
				break;
			default:
				$this->path = [
					BX_PERSONAL_ROOT . '/cache/',
					BX_PERSONAL_ROOT . '/managed_cache/',
					BX_PERSONAL_ROOT . '/stack_cache/',
					BX_PERSONAL_ROOT . '/html_pages/',
				];
				break;
		}
	}

	public function InitPath($pathToCheck)
	{
		if ($pathToCheck <> '')
		{
			$pathToCheck = preg_replace("#[\\\\/]+#", "/", "/" . $pathToCheck);
			//Check if path does not contain any injection
			if (preg_match('#/\\.\\.#', $pathToCheck) || preg_match('#\\.\\./#', $pathToCheck))
			{
				return false;
			}

			$base = "";
			foreach ($this->path as $path)
			{
				if (preg_match('#^' . $path . '#', $pathToCheck))
				{
					$base = $path;
					break;
				}
			}

			if ($base <> '')
			{
				$this->currentBase = $base;
				$this->currentPath = mb_substr($pathToCheck, mb_strlen($base));
				return true;
			}
			return false;
		}
		else
		{
			$this->currentBase = $this->path[0];
			$this->currentPath = "";
			return true;
		}
	}

	public function Start()
	{
		if ($this->currentBase)
		{
			$this->fileTree = new CFileTree($this->rootDir . $this->currentBase);
			$this->fileTree->Start($this->currentPath);
		}
	}

	public function GetNextFile()
	{
		if (is_object($this->fileTree))
		{
			$file = $this->fileTree->GetNextFile();
			//Check if current cache subdirectory cleaned
			if ($file === false)
			{
				//Skip all checked bases
				$arPath = $this->path;
				while (!empty($arPath))
				{
					$CurBase = array_shift($arPath);
					if ($CurBase == $this->currentBase)
					{
						break;
					}
				}
				//There is at least one cache directory not checked yet
				//so try to find a file inside
				while (!empty($arPath))
				{
					$this->currentBase = array_shift($arPath);
					$this->currentPath = "";
					$this->fileTree = new CFileTree($this->rootDir . $this->currentBase);
					$this->fileTree->Start($this->currentPath);
					$file = $this->fileTree->GetNextFile();
					if ($file !== false)
					{
						return $file;
					}
				}
				return false;
			}
			return $file;
		}
		else
		{
			return false;
		}
	}

	public function GetFileExpiration($fileName)
	{
		if (preg_match('#^' . $this->rootDir . BX_PERSONAL_ROOT . '/html_pages/.*\\.html$#', $fileName))
		{
			return 1; // like a very old file
		}
		elseif (preg_match('#\\.~\\d+/#', $fileName)) //delayed delete files
		{
			return 1; // like a very old file
		}
		elseif (str_ends_with($fileName, ".php"))
		{
			$fd = fopen($fileName, "rb");
			if ($fd)
			{
				$header = fread($fd, 150);
				fclose($fd);
				if (preg_match("/dateexpire\s*=\s*'(\d+)'/im", $header, $match))
				{
					return doubleval($match[1]);
				}
			}
		}
		elseif (str_ends_with($fileName, ".html"))
		{
			$fd = fopen($fileName, "rb");
			if ($fd)
			{
				$header = fread($fd, 26);
				fclose($fd);
				if (str_starts_with($header, "BX"))
				{
					return doubleval(mb_substr($header, 14, 12));
				}
			}
		}
		return false;
	}
}
