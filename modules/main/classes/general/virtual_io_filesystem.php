<?
/**
 * @deprecated Use \Bitrix\Main\IO
 */
class CBXVirtualIoFileSystem
	implements IBXVirtualIO, IBXGetErrors
{
	private static $systemEncoding;
	private static $serverEncoding;

	const directionEncode = 1;
	const directionDecode = 2;
	const invalidChars = "\\/:*?\"'<>|~#&;";

	//the pattern should be quoted, "|" is allowed below as a delimiter
	const invalidBytes = "\xE2\x80\xAE"; //Right-to-Left Override Unicode Character

	private $arErrors = array();

	public static function ConvertCharset($string, $direction = 1, $skipEvents = false)
	{
		if (is_null(self::$systemEncoding))
		{
			self::$systemEncoding = mb_strtolower(defined("BX_FILE_SYSTEM_ENCODING")? BX_FILE_SYSTEM_ENCODING : "");
			if (empty(self::$systemEncoding))
			{
				if (mb_strtoupper(mb_substr(PHP_OS, 0, 3)) === "WIN")
					self::$systemEncoding = "windows-1251";
				else
					self::$systemEncoding = "utf-8";
			}
		}

		if (is_null(self::$serverEncoding))
		{
			if (defined('BX_UTF'))
				self::$serverEncoding = "utf-8";
			elseif (defined("SITE_CHARSET") && (SITE_CHARSET <> ''))
				self::$serverEncoding = SITE_CHARSET;
			elseif (defined("LANG_CHARSET") && (LANG_CHARSET <> ''))
				self::$serverEncoding = LANG_CHARSET;
			elseif (defined("BX_DEFAULT_CHARSET"))
				self::$serverEncoding = BX_DEFAULT_CHARSET;
			else
				self::$serverEncoding = "windows-1251";

			self::$serverEncoding = mb_strtolower(self::$serverEncoding);
		}

		if (self::$serverEncoding == self::$systemEncoding)
			return $string;

		if ($direction == self::directionEncode)
			$result = \Bitrix\Main\Text\Encoding::convertEncoding($string, self::$serverEncoding, self::$systemEncoding);
		else
			$result = \Bitrix\Main\Text\Encoding::convertEncoding($string, self::$systemEncoding, self::$serverEncoding);

		if (
			defined('BX_IO_Compartible')
			&& !$skipEvents
			&& (BX_IO_Compartible === 'Y')
		)
		{
			$arEventParams = array(
				'original' => $string,
				'converted' => $result,
				'direction' => $direction,
				'systemEncoding' => self::$systemEncoding,
				'serverEncoding' => self::$serverEncoding
			);

			foreach (GetModuleEvents("main", "BXVirtualIO_ConvertCharset", true) as $arEvent)
			{
				$evResult = ExecuteModuleEventEx($arEvent, array($arEventParams));
				if ($evResult !== false)
				{
					$result = $evResult;
					break;
				}
			}
		}

		return $result;
	}

	public function CombinePath()
	{
		$numArgs = func_num_args();
		if ($numArgs <= 0)
			return "";

		$arParts = array();
		for ($i = 0; $i < $numArgs; $i++)
		{
			$arg = func_get_arg($i);
			if (empty($arg))
				continue;

			if (is_array($arg))
			{
				foreach ($arg as $v)
				{
					if (empty($v))
						continue;
					$arParts[] = $v;
				}
			}
			else
			{
				$arParts[] = $arg;
			}
		}

		$result = "";
		foreach ($arParts as $part)
		{
			if (!empty($result))
				$result .= "/";
			$result .= $part;
		}

		$result = self::FormatPath($result);

		return $result;
	}

	public function RelativeToAbsolutePath($relativePath)
	{
		if (empty($relativePath))
			return null;

		$basePath = $_SERVER["DOCUMENT_ROOT"];

		return $this->CombinePath($basePath, $relativePath);
	}

	public function SiteRelativeToAbsolutePath($relativePath, $site = null)
	{
		if ((string)$site === "")
		{
			$site = SITE_ID;
		}
		else
		{
			$dbSite = CSite::GetByID($site);
			$site = "";
			if ($arSite = $dbSite->Fetch())
				$site = $_REQUEST["site"];
			if ((string)$site === "")
				$site = SITE_ID;
		}

		$basePath = CSite::GetSiteDocRoot($site);

		return $this->CombinePath($basePath, $relativePath);
	}

	public function GetPhysicalName($path)
	{
		return CBXVirtualIoFileSystem::ConvertCharset($path);
	}

	function GetLogicalName($path)
	{
		return CBXVirtualIoFileSystem::ConvertCharset($path, self::directionDecode);
	}

	public function ExtractNameFromPath($path)
	{
		$path = rtrim($path, "\\/");
		if (preg_match("#[^\\\\/]+$#", $path, $match))
			$path = $match[0];
		return $path;
	}

	public function ExtractPathFromPath($path)
	{
		return mb_substr($path, 0, -mb_strlen($this->ExtractNameFromPath($path)) - 1);
	}

	private function FormatPath($path)
	{
		if ($path == "")
			return null;

		//slashes doesn't matter for Windows
		if(strncasecmp(PHP_OS, "WIN", 3) == 0)
		{
			//windows
			$pattern = "'[\\\\/]+'";
			$tailPattern = "\0.\\/+ ";
		}
		else
		{
			//unix
			$pattern = "'[/]+'";
			$tailPattern = "\0/";
		}

		$res = preg_replace($pattern, "/", $path);

		if (strpos($res, "\0") !== false)
			throw new \Bitrix\Main\IO\InvalidPathException($path);

		$arPath = explode('/', $res);
		$nPath = count($arPath);
		$pathStack = array();

		for ($i = 0; $i < $nPath; $i++)
		{
			if ($arPath[$i] === ".")
				continue;
			if (($arPath[$i] === '') && ($i !== ($nPath - 1)) && ($i !== 0))
				continue;

			if ($arPath[$i] === "..")
				array_pop($pathStack);
			else
				array_push($pathStack, $arPath[$i]);
		}

		$res = implode("/", $pathStack);

		$res = rtrim($res, $tailPattern);

		if(mb_substr($path, 0, 1) === "/" && mb_substr($res, 0, 1) !== "/")
			$res = "/".$res;

		if ($res === "")
			$res = "/";

		return $res;
	}

	protected static function ValidateCommon($path)
	{
		if (trim($path) == '')
		{
			return false;
		}

		if (strpos($path, "\0") !== false)
		{
			return false;
		}

		if(preg_match("#(".self::invalidBytes.")#", $path))
		{
			return false;
		}

		if(defined("BX_UTF") && !mb_check_encoding($path, "UTF-8"))
		{
			return false;
		}

		return true;
	}

	function ValidatePathString($path)
	{
		if(mb_strlen($path) > 4096)
		{
			return false;
		}

		if(!static::ValidateCommon($path))
		{
			return false;
		}

		return (preg_match("#^([a-z]:)?/([^\x01-\x1F".preg_quote(self::invalidChars, "#")."]+/?)*$#isD", $path) > 0);
	}

	function ValidateFilenameString($filename)
	{
		if(!static::ValidateCommon($filename))
		{
			return false;
		}

		return (preg_match("#^[^\x01-\x1F".preg_quote(self::invalidChars, "#")."]+$#isD", $filename) > 0);
	}

	function RandomizeInvalidFilename($filename)
	{
		return preg_replace_callback(
			"#([\x01-\x1F".preg_quote(self::invalidChars, "#")."]|".self::invalidBytes.")#",
			'CBXVirtualIoFileSystem::getRandomChar',
			$filename
		);
	}

	public static function getRandomChar()
	{
		return chr(rand(97, 122));
	}

	public function DirectoryExists($path)
	{
		$path = CBXVirtualIoFileSystem::ConvertCharset($path);
		return file_exists($path) && is_dir($path);
	}

	public function FileExists($path)
	{
		$path = CBXVirtualIoFileSystem::ConvertCharset($path);
		return file_exists($path) && is_file($path);
	}

	public function GetDirectory($path)
	{
		return new CBXVirtualDirectoryFileSystem($path);
	}

	public function GetFile($path)
	{
		return new CBXVirtualFileFileSystem($path);
	}

	public function OpenFile($path, $mode)
	{
		$file = $this->GetFile($path);
		return $file->Open($mode);
	}

	public function Delete($path)
	{
		$this->ClearErrors();

		if (mb_substr($path, 0, mb_strlen($_SERVER["DOCUMENT_ROOT"])) == $_SERVER["DOCUMENT_ROOT"])
		{
			$pathTmp = mb_substr($path, mb_strlen($_SERVER["DOCUMENT_ROOT"]));
			if (empty($pathTmp) || $pathTmp == '/')
			{
				$this->AddError("Can not delete the root folder of the project");
				return false;
			}
		}

		$pathEncoded = CBXVirtualIoFileSystem::ConvertCharset($path);

		$f = true;
		if (is_file($pathEncoded) || is_link($pathEncoded))
		{
			if (@unlink($pathEncoded))
				return true;

			$this->AddError(sprintf("Can not delete file '%s'", $path));
			return false;
		}
		elseif (is_dir($pathEncoded))
		{
			if ($handle = opendir($pathEncoded))
			{
				while (($file = readdir($handle)) !== false)
				{
					if ($file == "." || $file == "..")
						continue;

					$pathDecodedTmp = CBXVirtualIoFileSystem::ConvertCharset($pathEncoded."/".$file, CBXVirtualIoFileSystem::directionDecode);
					if (!$this->Delete($pathDecodedTmp))
						$f = false;
				}
				closedir($handle);
			}
			if (!@rmdir($pathEncoded))
			{
				$this->AddError(sprintf("Can not delete directory '%s'", $path));
				return false;
			}

			return $f;
		}

		$this->AddError("Unknown error");
		return false;
	}

	private function CopyDirFiles($pathFrom, $pathTo, $bRewrite = true, $bDeleteAfterCopy = false)
	{
		$this->ClearErrors();

		if (mb_strpos($pathTo."/", $pathFrom."/") === 0)
		{
			$this->AddError("Can not copy a file onto itself");
			return false;
		}

		$pathFromEncoded = CBXVirtualIoFileSystem::ConvertCharset($pathFrom);
		if (is_dir($pathFromEncoded))
		{
			$this->CreateDirectory($pathTo);
		}
		elseif (is_file($pathFromEncoded))
		{
			$this->CreateDirectory($this->ExtractPathFromPath($pathTo));

			$pathToEncoded = CBXVirtualIoFileSystem::ConvertCharset($pathTo);
			if (file_exists($pathToEncoded) && !$bRewrite)
			{
				$this->AddError(sprintf("The file '%s' already exists", $pathTo));
				return false;
			}

			@copy($pathFromEncoded, $pathToEncoded);
			if (is_file($pathToEncoded))
			{
				@chmod($pathToEncoded, BX_FILE_PERMISSIONS);

				if ($bDeleteAfterCopy)
					@unlink($pathFromEncoded);
			}
			else
			{
				$this->AddError(sprintf("Creation of file '%s' failed", $pathTo));
				return false;
			}

			return true;
		}
		else
		{
			return true;
		}

		$pathToEncoded = CBXVirtualIoFileSystem::ConvertCharset($pathTo);
		if ($handle = @opendir($pathFromEncoded))
		{
			while (($file = readdir($handle)) !== false)
			{
				if ($file == "." || $file == "..")
					continue;

				if (is_dir($pathFromEncoded."/".$file))
				{
					$pathFromDecodedTmp = CBXVirtualIoFileSystem::ConvertCharset($pathFromEncoded."/".$file, CBXVirtualIoFileSystem::directionDecode);
					$pathToDecodedTmp = CBXVirtualIoFileSystem::ConvertCharset($pathToEncoded."/".$file, CBXVirtualIoFileSystem::directionDecode);
					CopyDirFiles($pathFromDecodedTmp, $pathToDecodedTmp, $bRewrite, $bDeleteAfterCopy);
					if ($bDeleteAfterCopy)
						@rmdir($pathFromEncoded."/".$file);
				}
				elseif (is_file($pathFromEncoded."/".$file))
				{
					if (file_exists($pathToEncoded."/".$file) && !$bRewrite)
						continue;

					@copy($pathFromEncoded."/".$file, $pathToEncoded."/".$file);
					@chmod($pathToEncoded."/".$file, BX_FILE_PERMISSIONS);

					if ($bDeleteAfterCopy)
						@unlink($pathFromEncoded."/".$file);
				}
			}
			@closedir($handle);

			if ($bDeleteAfterCopy)
				@rmdir($pathFromEncoded);

			return true;
		}

		$this->AddError(sprintf("Can not open directory '%s'", $pathFrom));
		return false;
	}

	public function Copy($source, $target, $bRewrite = true)
	{
		return $this->CopyDirFiles($source, $target, $bRewrite, false);
	}

	public function Move($source, $target, $bRewrite = true)
	{
		return $this->CopyDirFiles($source, $target, $bRewrite, true);
	}

	public function Rename($source, $target)
	{
		$sourceEncoded = CBXVirtualIoFileSystem::ConvertCharset($source);
		$targetEncoded = CBXVirtualIoFileSystem::ConvertCharset($target);
		return rename($sourceEncoded, $targetEncoded);
	}

	public function CreateDirectory($path)
	{
		$fld = $this->GetDirectory($path);
		if ($fld->Create())
			return $fld;

		return null;
	}

	function ClearCache()
	{
		clearstatcache();
	}

	public function GetErrors()
	{
		return $this->arErrors;
	}

	protected function AddError($error, $errorCode = "")
	{
		if (empty($error))
			return;

		$fs = (empty($errorCode) ? "%s" : "[%s] %s");
		$this->arErrors[] = sprintf($fs, $error, $errorCode);
	}

	protected function ClearErrors()
	{
		$this->arErrors = array();
	}
}

/**
 * @deprecated Use \Bitrix\Main\IO
 */
class CBXVirtualFileFileSystem
	extends CBXVirtualFile
{
	protected $pathEncoded = null;
	private $arErrors = array();

	protected function GetPathWithNameEncoded()
	{
		if (is_null($this->pathEncoded))
			$this->pathEncoded = CBXVirtualIoFileSystem::ConvertCharset($this->path);

		return $this->pathEncoded;
	}

	public function Open($mode)
	{
		$lmode = mb_strtolower(mb_substr($mode, 0, 1));
		$bExists = $this->IsExists();

		if (
			( $bExists && ($lmode !== 'x'))
			|| (!$bExists && ($lmode !== 'r'))
		)
			return fopen($this->GetPathWithNameEncoded(), $mode);

		return null;
	}

	public function GetContents()
	{
		if ($this->IsExists())
			return file_get_contents($this->GetPathWithNameEncoded());

		return null;
	}

	public function PutContents($data)
	{
		$this->ClearErrors();

		$io = CBXVirtualIo::GetInstance();
		$dir = $io->CreateDirectory($this->GetPath());
		if (is_null($dir))
		{
			$this->AddError(sprintf("Can not create directory '%s' or access denied", $this->GetPath()));
			return false;
		}

		if ($this->IsExists() && !$this->IsWritable())
			$this->MarkWritable();

		$fd = fopen($this->GetPathWithNameEncoded(), "wb");
		if (!$fd)
		{
			$this->AddError(sprintf("Can not open file '%s' for writing", $this->GetPathWithNameEncoded()));
			return false;
		}
		if (fwrite($fd, $data) === false)
		{
			$this->AddError(sprintf("Can not write %d bytes to file '%s'", mb_strlen($data), $this->GetPathWithNameEncoded()));
			fclose($fd);
			return false;
		}
		fclose($fd);

		return true;
	}

	public function GetFileSize()
	{
		if ($this->IsExists())
			return intval(filesize($this->GetPathWithNameEncoded()));

		return 0;
	}

	public function GetCreationTime()
	{
		if ($this->IsExists())
			return filectime($this->GetPathWithNameEncoded());

		return null;
	}

	public function GetModificationTime()
	{
		if ($this->IsExists())
			return filemtime($this->GetPathWithNameEncoded());

		return null;
	}

	public function GetLastAccessTime()
	{
		if ($this->IsExists())
			return fileatime($this->GetPathWithNameEncoded());

		return null;
	}

	public function IsWritable()
	{
		return is_writable($this->GetPathWithNameEncoded());
	}

	public function IsReadable()
	{
		return is_readable($this->GetPathWithNameEncoded());
	}

	public function MarkWritable()
	{
		if ($this->IsExists())
			@chmod($this->GetPathWithNameEncoded(), BX_FILE_PERMISSIONS);
	}

	public function IsExists()
	{
		$io = CBXVirtualIo::GetInstance();
		return $io->FileExists($this->path);
	}

	public function GetPermissions()
	{
		return fileperms($this->GetPathWithNameEncoded());
	}

	public function ReadFile()
	{
		return readfile($this->GetPathWithNameEncoded());
	}

	public function unlink()
	{
		return unlink($this->GetPathWithNameEncoded());
	}

	public function GetErrors()
	{
		return $this->arErrors;
	}

	protected function AddError($error, $errorCode = "")
	{
		if (empty($error))
			return;

		$fs = (empty($errorCode) ? "%s" : "[%s] %s");
		$this->arErrors[] = sprintf($fs, $error, $errorCode);
	}

	protected function ClearErrors()
	{
		$this->arErrors = array();
	}
}

/**
 * @deprecated Use \Bitrix\Main\IO
 */
class CBXVirtualDirectoryFileSystem
	extends CBXVirtualDirectory
{
	protected $pathEncoded = null;
	private $arErrors = array();

	protected function GetPathWithNameEncoded()
	{
		if (is_null($this->pathEncoded))
			$this->pathEncoded = CBXVirtualIoFileSystem::ConvertCharset($this->path);

		return $this->pathEncoded;
	}

	/**
	 * @return CBXVirtualDirectoryFileSystem[]|CBXVirtualFileFileSystem[]
	 */
	public function GetChildren()
	{
		$arResult = array();

		if (!$this->IsExists())
			return $arResult;

		if ($handle = opendir($this->GetPathWithNameEncoded()))
		{
			while (($file = readdir($handle)) !== false)
			{
				if ($file == "." || $file == "..")
					continue;

				$pathDecoded = CBXVirtualIoFileSystem::ConvertCharset($this->GetPathWithNameEncoded()."/".$file, CBXVirtualIoFileSystem::directionDecode);
				if (is_dir($this->GetPathWithNameEncoded()."/".$file))
					$arResult[] = new CBXVirtualDirectoryFileSystem($pathDecoded);
				else
					$arResult[] = new CBXVirtualFileFileSystem($pathDecoded);
			}
			closedir($handle);
		}

		return $arResult;
	}

	public function Create()
	{
		if (!file_exists($this->GetPathWithNameEncoded()))
			return mkdir($this->GetPathWithNameEncoded(), BX_DIR_PERMISSIONS, true);
		else
			return is_dir($this->GetPathWithNameEncoded());
	}

	public function IsExists()
	{
		$io = CBXVirtualIo::GetInstance();
		return $io->DirectoryExists($this->path);
	}

	public function MarkWritable()
	{
		if ($this->IsExists())
			@chmod($this->GetPathWithNameEncoded(), BX_DIR_PERMISSIONS);
	}

	public function GetPermissions()
	{
		return fileperms($this->GetPathWithNameEncoded());
	}

	public function GetCreationTime()
	{
		if ($this->IsExists())
			return filectime($this->GetPathWithNameEncoded());

		return null;
	}

	public function GetModificationTime()
	{
		if ($this->IsExists())
			return filemtime($this->GetPathWithNameEncoded());

		return null;
	}

	public function GetLastAccessTime()
	{
		if ($this->IsExists())
			return fileatime($this->GetPathWithNameEncoded());

		return null;
	}

	public function IsEmpty()
	{
		if ($this->IsExists())
		{
			if ($handle = opendir($this->GetPathWithNameEncoded()))
			{
				while (($file = readdir($handle)) !== false)
				{
					if ($file != "." && $file != "..")
					{
						closedir($handle);
						return false;
					}
				}
				closedir($handle);
			}
		}
		return true;
	}

	public function rmdir()
	{
		return rmdir($this->GetPathWithNameEncoded());
	}

	public function GetErrors()
	{
		return $this->arErrors;
	}

	protected function AddError($error, $errorCode = "")
	{
		if (empty($error))
			return;

		$fs = (empty($errorCode) ? "%s" : "[%s] %s");
		$this->arErrors[] = sprintf($fs, $error, $errorCode);
	}

	protected function ClearErrors()
	{
		$this->arErrors = array();
	}
}
