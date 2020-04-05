<?

namespace Bitrix\Pull;

class Loader
{
	public static function register()
	{
		if (!static::isAlreadyRegistered())
		{
			\spl_autoload_register(array(__CLASS__, "autoLoad"), true);
		}
	}

	public static function autoLoad($className)
	{
		$className = ltrim($className, "\\"); // fix web env
		$className = str_replace("\\", '/', $className);

		if (preg_match("#[^\\\\/a-zA-Z0-9_]#", $className))
		{
			return;
		}

		$fileParts = explode("/", $className);
		if (count($fileParts) < 2)
		{
			return;
		}

		$firstNamespace = strtolower($fileParts[0]);
		$secondNamespace = strtolower($fileParts[1]);

		if (
			$firstNamespace === "protobuf" ||
			$firstNamespace === "google" && $secondNamespace === "protobuf" ||
			$firstNamespace === "gpbmetadata" && $secondNamespace === "google"
		)
		{
			$documentRoot = $documentRoot = rtrim($_SERVER["DOCUMENT_ROOT"], "/\\");
			$filePath = $documentRoot."/bitrix/modules/pull/vendor/".implode("/", $fileParts).".php";

			if (file_exists($filePath))
			{
				require_once($filePath);
			}
		}
	}

	private static function isAlreadyRegistered()
	{
		$autoLoaders = spl_autoload_functions();
		if (!$autoLoaders)
		{
			return false;
		}

		foreach ($autoLoaders as $autoLoader)
		{
			if (!is_array($autoLoader))
			{
				continue;
			}

			list($className, $method) = $autoLoader;

			if ($className === __CLASS__)
			{
				return true;
			}
		}

		return false;
	}
}