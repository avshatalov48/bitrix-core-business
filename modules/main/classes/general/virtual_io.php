<?
include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/virtual_file.php");

interface IBXVirtualIO
{
	function CombinePath();
	function RelativeToAbsolutePath($relativePath);
	function SiteRelativeToAbsolutePath($relativePath, $site = null);
	function GetPhysicalName($path);
	function GetLogicalName($path);
	function ExtractNameFromPath($path);
	function ExtractPathFromPath($path);
	function ValidatePathString($path);
	function ValidateFilenameString($filename);
	function DirectoryExists($path);
	function FileExists($path);
	function GetDirectory($path);
	function GetFile($path);
	function OpenFile($path, $mode);
	function CreateDirectory($path);
	function Delete($path);
	function Copy($source, $target, $bRewrite = true);
	function Move($source, $target, $bRewrite = true);
	function Rename($source, $target);
	function ClearCache();
}

interface IBXGetErrors
{
	function GetErrors();
}

/**
 * Proxy class for file IO. Provides a set of methods to retrieve resources from a file system.
 */
class CBXVirtualIo
	implements IBXVirtualIO, IBXGetErrors
{
	private static $instance;
	private $io;

	public function __construct()
	{
		include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/virtual_io_filesystem.php");
		$this->io = new CBXVirtualIoFileSystem();
	}

	/**
	 * Returns proxy class instance (singleton pattern)
	 *
	 * @static
	 * @return CBXVirtualIo - Proxy class instance
	 */
	public static function GetInstance()
	{
		if (!isset(self::$instance))
		{
			$c = __CLASS__;
			self::$instance = new $c;
		}

		return self::$instance;
	}

	/**
	 * Combines a path parts
	 *
	 * Variable-length argument list
	 *
	 * @return string - Combined path
	 */
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

		return $this->io->CombinePath($arParts);
	}

	/**
	 * Converts a relative path to absolute one
	 *
	 * @param string $relativePath - Relative path
	 * @return string - Complete path
	 */
	public function RelativeToAbsolutePath($relativePath)
	{
		return $this->io->RelativeToAbsolutePath($relativePath);
	}

	public function SiteRelativeToAbsolutePath($relativePath, $site = null)
	{
		return $this->io->SiteRelativeToAbsolutePath($relativePath, $site);
	}

	/**
	 * Returns Physical path to file or directory
	 *
	 * @param string $path - Path
	 * @return string - Physical path
	 */
	public function GetPhysicalName($path)
	{
		return $this->io->GetPhysicalName($path);
	}

	function GetLogicalName($path)
	{
		return $this->io->GetLogicalName($path);
	}

	/**
	 * Returns name of the file or directory
	 *
	 * @param string $path - Path
	 * @return string - File/directory name
	 */
	public function ExtractNameFromPath($path)
	{
		return $this->io->ExtractNameFromPath($path);
	}

	/**
	 * Returns path to the file or directory (without file/directory name)
	 *
	 * @param string $path - Path
	 * @return string - Result
	 */
	public function ExtractPathFromPath($path)
	{
		return $this->io->ExtractPathFromPath($path);
	}

	/**
	 * @param string $path
	 * @return bool
	 */
	public function ValidatePathString($path)
	{
		return $this->io->ValidatePathString($path);
	}

	/**
	 * @param string $filename
	 * @return bool
	 */
	public function ValidateFilenameString($filename)
	{
		return $this->io->ValidateFilenameString($filename);
	}

	/**
	 * @param string $filename
	 * @return string
	 */
	public function RandomizeInvalidFilename($filename)
	{
		return $this->io->RandomizeInvalidFilename($filename);
	}

	/**
	 * Gets a value that indicates whether a directory exists in the file system
	 *
	 * @param string $path - Complete path to the directory
	 * @return bool - True if the directory exists, false - otherwise
	 */
	public function DirectoryExists($path)
	{
		return $this->io->DirectoryExists($path);
	}

	/**
	 * Gets a value that indicates whether a file exists in the file system
	 *
	 * @param string $path - Complete path to the file
	 * @return bool - True if the file exists, false - otherwise
	 */
	public function FileExists($path)
	{
		return $this->io->FileExists($path);
	}

	/**
	 * Gets a directory from the file system
	 *
	 * @param string $path - Complete path to the directory
	 * @return CBXVirtualDirectoryFileSystem
	 */
	public function GetDirectory($path)
	{
		return $this->io->GetDirectory($path);
	}

	/**
	 * Gets a virtual file from the file system
	 *
	 * @param string $path - Complete path to the file
	 * @return CBXVirtualFileFileSystem
	 */
	public function GetFile($path)
	{
		return $this->io->GetFile($path);
	}

	/**
	 * Returns a stream from a file
	 *
	 * @param string $path - Complete path to the file
	 * @param string $mode - The type of access to the file ('rb' - reading, 'wb' - writing, 'ab' - appending)
	 * @return resource
	 */
	public function OpenFile($path, $mode)
	{
		return $this->io->OpenFile($path, $mode);
	}

	/**
	 * Deletes a file or directory from the file system
	 *
	 * @param string $path - Complete path to the file or directory
	 * @return bool - Result
	 */
	public function Delete($path)
	{
		return $this->io->Delete($path);
	}

	/**
	 * Copies a file or directory from source location to target
	 *
	 * @param string $source - Complete path of the source file or directory
	 * @param string $target - Complete path of the target file or directory
	 * @param bool $bRewrite - True to rewrite existing files, false - otherwise
	 * @return bool - Result
	 */
	public function Copy($source, $target, $bRewrite = true)
	{
		return $this->io->Copy($source, $target, $bRewrite);
	}

	/**
	 * Moves a file or directory from source location to target
	 *
	 * @param string $source - Complete path of the source file or directory
	 * @param string $target - Complete path of the target file or directory
	 * @param bool $bRewrite - True to rewrite existing files, false - otherwise
	 * @return bool - Result
	 */
	public function Move($source, $target, $bRewrite = true)
	{
		return $this->io->Move($source, $target, $bRewrite);
	}

	public function Rename($source, $target)
	{
		return $this->io->Rename($source, $target);
	}

	/**
	 * Clear file system cache (if any)
	 *
	 * @return void
	 */
	function ClearCache()
	{
		$this->io->ClearCache();
	}

	/**
	 * Creates a directory if is is not exist
	 *
	 * @param string $path - Complete path of the directory
	 * @return CBXVirtualDirectory|null
	 */
	public function CreateDirectory($path)
	{
		return $this->io->CreateDirectory($path);
	}

	/**
	 * Returns runtime errors
	 *
	 * @return array - Array of errors
	 */
	public function GetErrors()
	{
		return $this->io->GetErrors();
	}
}
?>