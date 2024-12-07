<?php
namespace Bitrix\Main\IO;

use Bitrix\Main;
use Bitrix\Main\Text;

/**
 *
 */
class Path
{
	const DIRECTORY_SEPARATOR = '/';
	const DIRECTORY_SEPARATOR_ALT = '\\';
	const PATH_SEPARATOR = PATH_SEPARATOR;

	const INVALID_FILENAME_CHARS = "\\/:*?\"'<>|~#&;";

	//the pattern should be quoted, "|" is allowed below as a delimiter
	const INVALID_FILENAME_BYTES = "\xE2\x80\xAE"; //Right-to-Left Override Unicode Character

	protected static $physicalEncoding = "";
	protected static $logicalEncoding = "";

	protected static $directoryIndex = null;

	public static function normalize($path)
	{
		if (!is_string($path) || ($path == ""))
			return null;

		//slashes doesn't matter for Windows
		static $pattern = null, $tailPattern;
		if (!$pattern)
		{
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
		}
		$pathTmp = preg_replace($pattern, "/", $path);

		if (str_contains($pathTmp, "\0"))
			throw new InvalidPathException($path);

		if (preg_match("#(^|/)(\\.|\\.\\.)(/|\$)#", $pathTmp))
		{
			$arPathTmp = explode('/', $pathTmp);
			$arPathStack = array();
			foreach ($arPathTmp as $i => $pathPart)
			{
				if ($pathPart === '.')
					continue;

				if ($pathPart === "..")
				{
					if (array_pop($arPathStack) === null)
						throw new InvalidPathException($path);
				}
				else
				{
					array_push($arPathStack, $pathPart);
				}
			}
			$pathTmp = implode("/", $arPathStack);
		}

		$pathTmp = rtrim($pathTmp, $tailPattern);

		if (str_starts_with($path, "/") && !str_starts_with($pathTmp, "/"))
			$pathTmp = "/".$pathTmp;

		if ($pathTmp === '')
			$pathTmp = "/";

		return $pathTmp;
	}

	public static function getExtension($path)
	{
		$path = self::getName($path);
		if ($path != '')
		{
			$pos = Text\UtfSafeString::getLastPosition($path, '.');
			if ($pos !== false)
				return mb_substr($path, $pos + 1);
		}
		return '';
	}

	public static function getName($path)
	{
		//$path = self::normalize($path);

		$p = Text\UtfSafeString::getLastPosition($path, self::DIRECTORY_SEPARATOR);
		if ($p !== false)
			return mb_substr($path, $p + 1);

		return $path;
	}

	public static function getDirectory($path)
	{
		return mb_substr($path, 0, -mb_strlen(self::getName($path)) - 1);
	}

	public static function convertLogicalToPhysical($path)
	{
		if (self::$physicalEncoding == "")
			self::$physicalEncoding = self::getPhysicalEncoding();

		if (self::$logicalEncoding == "")
			self::$logicalEncoding = self::getLogicalEncoding();

		if (self::$physicalEncoding == self::$logicalEncoding)
			return $path;

		return Text\Encoding::convertEncoding($path, self::$logicalEncoding, self::$physicalEncoding);
	}

	public static function convertPhysicalToLogical($path)
	{
		if (self::$physicalEncoding == "")
			self::$physicalEncoding = self::getPhysicalEncoding();

		if (self::$logicalEncoding == "")
			self::$logicalEncoding = self::getLogicalEncoding();

		if (self::$physicalEncoding == self::$logicalEncoding)
			return $path;

		return Text\Encoding::convertEncoding($path, self::$physicalEncoding, self::$logicalEncoding);
	}

	public static function convertLogicalToUri($path)
	{
		if (self::$logicalEncoding == "")
			self::$logicalEncoding = self::getLogicalEncoding();

		if (self::$directoryIndex == null)
			self::$directoryIndex = self::getDirectoryIndexArray();

		if (isset(self::$directoryIndex[self::getName($path)]))
			$path = self::getDirectory($path)."/";

		if ('utf-8' !== self::$logicalEncoding)
			$path = Text\Encoding::convertEncoding($path, self::$logicalEncoding, 'utf-8');

		return implode('/', array_map("rawurlencode", explode('/', $path)));
	}

	public static function convertPhysicalToUri($path)
	{
		if (self::$physicalEncoding == "")
			self::$physicalEncoding = self::getPhysicalEncoding();

		if (self::$directoryIndex == null)
			self::$directoryIndex = self::getDirectoryIndexArray();

		if (isset(self::$directoryIndex[self::getName($path)]))
			$path = self::getDirectory($path)."/";

		if ('utf-8' !== self::$physicalEncoding)
			$path = Text\Encoding::convertEncoding($path, self::$physicalEncoding, 'utf-8');

		return implode('/', array_map("rawurlencode", explode('/', $path)));
	}

	public static function convertUriToPhysical($path)
	{
		if (self::$physicalEncoding == "")
			self::$physicalEncoding = self::getPhysicalEncoding();

		if (self::$directoryIndex == null)
			self::$directoryIndex = self::getDirectoryIndexArray();

		$path = implode('/', array_map("rawurldecode", explode('/', $path)));

		if ('utf-8' !== self::$physicalEncoding)
			$path = Text\Encoding::convertEncoding($path, 'utf-8', self::$physicalEncoding);

		return $path;
	}

	protected static function getLogicalEncoding()
	{
		return "utf-8";
	}

	protected static function getPhysicalEncoding()
	{
		$physicalEncoding = defined("BX_FILE_SYSTEM_ENCODING") ? BX_FILE_SYSTEM_ENCODING : "";
		if ($physicalEncoding == "")
		{
			if (strtoupper(substr(PHP_OS, 0, 3)) === "WIN")
				$physicalEncoding = "windows-1251";
			else
				$physicalEncoding = "utf-8";
		}
		return mb_strtolower($physicalEncoding);
	}

	public static function combine()
	{
		$numArgs = func_num_args();
		if ($numArgs <= 0)
			return "";

		$arParts = array();
		for ($i = 0; $i < $numArgs; $i++)
		{
			$arg = func_get_arg($i);
			if (is_array($arg))
			{
				if (empty($arg))
					continue;

				foreach ($arg as $v)
				{
					if (!is_string($v) || $v == "")
						continue;
					$arParts[] = $v;
				}
			}
			elseif (is_string($arg))
			{
				if ($arg == "")
					continue;

				$arParts[] = $arg;
			}
		}

		$result = "";
		foreach ($arParts as $part)
		{
			if ($result !== "")
				$result .= self::DIRECTORY_SEPARATOR;
			$result .= $part;
		}

		$result = self::normalize($result);

		return $result;
	}

	public static function convertRelativeToAbsolute($relativePath)
	{
		if (!is_string($relativePath))
			throw new Main\ArgumentTypeException("relativePath", "string");
		if ($relativePath == "")
			throw new Main\ArgumentNullException("relativePath");

		return self::combine($_SERVER["DOCUMENT_ROOT"], $relativePath);
	}

	public static function convertSiteRelativeToAbsolute($relativePath, $site = null)
	{
		if (!is_string($relativePath) || $relativePath == "")
			$site = SITE_ID;

		$basePath = Main\SiteTable::getDocumentRoot($site);

		return self::combine($basePath, $relativePath);
	}

	protected static function validateCommon($path)
	{
		if (!is_string($path))
		{
			return false;
		}

		if (trim($path) == "")
		{
			return false;
		}

		if (str_contains($path, "\0"))
		{
			return false;
		}

		if(preg_match("#(".self::INVALID_FILENAME_BYTES.")#", $path))
		{
			return false;
		}

		return true;
	}

	public static function validate($path)
	{
		if(!static::validateCommon($path))
		{
			return false;
		}

		return (preg_match("#^([a-z]:)?/([^\x01-\x1F".preg_quote(self::INVALID_FILENAME_CHARS, "#")."]+/?)*$#isD", $path) > 0);
	}

	public static function validateFilename($filename)
	{
		if(!static::validateCommon($filename))
		{
			return false;
		}

		return (preg_match("#^[^\x01-\x1F".preg_quote(self::INVALID_FILENAME_CHARS, "#")."]+$#isD", $filename) > 0);
	}

	/**
	 * @param string $filename
	 * @param callable $callback
	 * @return string
	 */
	public static function replaceInvalidFilename($filename, $callback)
	{
		return preg_replace_callback(
			"#([\x01-\x1F".preg_quote(self::INVALID_FILENAME_CHARS, "#")."]|".self::INVALID_FILENAME_BYTES.")#",
			$callback,
			$filename
		);
	}

	/**
	 * @param string $filename
	 * @return string
	 */
	public static function randomizeInvalidFilename($filename)
	{
		return static::replaceInvalidFilename($filename,
			function()
			{
				return chr(rand(97, 122));
			}
		);
	}

	public static function isAbsolute($path)
	{
		return (str_starts_with($path, "/")) || preg_match("#^[a-z]:/#i", $path);
	}

	protected static function getDirectoryIndexArray()
	{
		static $directoryIndexDefault = array("index.php" => 1, "index.html" => 1, "index.htm" => 1, "index.phtml" => 1, "default.html" => 1, "index.php3" => 1);

		$directoryIndex = Main\Config\Configuration::getValue("directory_index");
		if ($directoryIndex !== null)
			return $directoryIndex;

		return $directoryIndexDefault;
	}
}
