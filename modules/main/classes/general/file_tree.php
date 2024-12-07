<?php

class CFileTree
{
	protected $inPath = '/';
	protected $path = '';
	protected $dir = false;

	public function __construct($in_path = "/")
	{
		$this->inPath = preg_replace("#[\\\\/]+#", "/", $in_path);
	}

	public function Start($path = "/")
	{
		$this->path = preg_replace("#[\\\\/]+#", "/", $this->inPath . trim($path, "/"));

		if (!file_exists($this->path) || is_file($this->path))
		{
			$last = self::ExtractFileFromPath($this->path);
			$this->dir = $this->ReadDir($this->path);
			if (is_array($this->dir))
			{
				while (count($this->dir))
				{
					if (strcmp($this->dir[0], $last) > 0)
					{
						break;
					}
					array_shift($this->dir);
				}
			}
		}
	}

	public function GetNextFile()
	{
		if (!is_array($this->dir))
		{
			$this->dir = $this->ReadDir($this->path);
			if (!is_array($this->dir))
			{
				return false;
			}
		}

		$next = current($this->dir);
		next($this->dir);

		if ($next === false)
		{
			//try to go up dir tree
			if ($this->GoUp())
			{
				return $this->GetNextFile();
			}
			else
			{
				return false;
			}
		}
		elseif (is_file($next))
		{
			//it's our target
			return $next;
		}
		else
		{
			//it's dir or link try to go deeper
			$this->path = $next;
			$this->dir = false;
			return true;
		}
	}

	public static function ExtractFileFromPath(&$path)
	{
		$arPath = explode("/", $path);
		$last = array_pop($arPath);
		$path = implode("/", $arPath);
		return $path . "/" . $last;
	}

	protected function GoUp()
	{
		$last_dir = self::ExtractFileFromPath($this->path);
		//We are not going to go up anymore
		if (mb_strlen($this->path . "/") < mb_strlen($this->inPath))
		{
			return false;
		}

		$this->dir = $this->ReadDir($this->path);
		//This shouldn't happen so try to go up one more level
		if (!is_array($this->dir))
		{
			return $this->GoUp();
		}

		//Skip all dirs till current
		while (count($this->dir))
		{
			if (strcmp($this->dir[0], $last_dir) > 0)
			{
				break;
			}
			array_shift($this->dir);
		}

		if (!empty($this->dir))
		{
			return true;
		} //there is more work to do
		else
		{
			return $this->GoUp();
		} // try to go upper
	}

	protected function ReadDir($dir)
	{
		$dir = rtrim($dir, "/");
		if (is_dir($dir))
		{
			$dh = opendir($dir);
			if ($dh)
			{
				$result = [];
				while (($f = readdir($dh)) !== false)
				{
					if ($f == "." || $f == "..")
					{
						continue;
					}
					$result[] = $dir . "/" . $f;
				}
				closedir($dh);
				sort($result);

				//try to delete an empty directory
				if (empty($result))
				{
					@rmdir($dir);
				}

				return $result;
			}
		}
		return false;
	}
}
