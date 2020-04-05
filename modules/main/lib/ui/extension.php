<?

namespace Bitrix\Main\UI;

use Bitrix\Main\Application;
use Bitrix\Main\IO\File;

class Extension
{
	public static function load($extName)
	{
		if (static::register($extName))
		{
			\CJSCore::init($extName);
		}
	}

	public static function register($extName)
	{
		$extension = static::getConfig($extName);
		if ($extension !== null)
		{
			static::registerAssets($extName, $extension);

			return true;
		}

		return false;
	}

	public static function registerAssets($id, array $options)
	{
		\CJSCore::registerExt($id, $options);
	}

	private static function getConfig($extName)
	{
		$extensionPath = static::getPath($extName);
		if ($extensionPath === null)
		{
			return null;
		}

		$configFile = Application::getDocumentRoot().$extensionPath."/config.php";
		if (!File::isFileExists($configFile))
		{
			return null;
		}

		$config = include($configFile);

		if (is_array($config))
		{
			if (isset($config["lang"]))
			{
				if (is_array($config["lang"]))
				{
					$config["lang"][] = $extensionPath."/config.php";
				}
				else
				{
					$config["lang"] = array($config["lang"], $extensionPath."/config.php");
				}
			}
			else
			{
				$config["lang"] = $extensionPath."/config.php";
			}
		}

		return is_array($config) ? $config : null;
	}

	private static function getPath($extName)
	{
		if (!is_string($extName))
		{
			return null;
		}

		$namespaces = explode(".", $extName);
		if (count($namespaces) < 2)
		{
			return null;
		}

		$path = "js";
		foreach ($namespaces as $namespace)
		{
			if (!preg_match("/^[a-z0-9_\\.\\-]+$/i", $namespace))
			{
				return null;
			}

			$path .= "/".$namespace;
		}

		return \getLocalPath($path, BX_PERSONAL_ROOT);
	}
}