<?php
namespace Bitrix\Main;

use Bitrix\Main\DI\ServiceLocator;

/**
 * Class Loader loads required files, classes and modules. It is the only class which is included directly.
 * @package Bitrix\Main
 */
class Loader
{
	/**
	 * Can be used to prevent loading all modules except main and fileman
	 */
	const SAFE_MODE = false;

	const BITRIX_HOLDER = "bitrix";
	const LOCAL_HOLDER = "local";

	protected static $safeModeModules = ["main" => true, "fileman" => true];
	protected static $loadedModules = ["main" => true];
	protected static $semiloadedModules = [];
	protected static $modulesHolders = ["main" => self::BITRIX_HOLDER];
	protected static $sharewareModules = [];

	/**
	 * Custom autoload paths.
	 * @var array [namespace => [ [path1, depth1], [path2, depth2] ]
	 */
	protected static $namespaces = [];

	/**
	 * Returned by includeSharewareModule() if module is not found
	 */
	const MODULE_NOT_FOUND = 0;
	/**
	 * Returned by includeSharewareModule() if module is installed
	 */
	const MODULE_INSTALLED = 1;
	/**
	 * Returned by includeSharewareModule() if module works in demo mode
	 */
	const MODULE_DEMO = 2;
	/**
	 * Returned by includeSharewareModule() if the trial period is expired
	 */
	const MODULE_DEMO_EXPIRED = 3;

	protected static $autoLoadClasses = [];

	/**
	 * @var bool Controls throwing exception by requireModule method
	 */
	protected static $requireThrowException = true;

	/** @deprecated   */
	const ALPHA_LOWER = "qwertyuioplkjhgfdsazxcvbnm";
	/** @deprecated   */
	const ALPHA_UPPER = "QWERTYUIOPLKJHGFDSAZXCVBNM";

     /**
	 * Includes a module by its name.
	 *
	 * @param string $moduleName Name of the included module
	 * @return bool Returns true if module was included successfully, otherwise returns false
	 * @throws LoaderException
	 */
	public static function includeModule($moduleName)
	{
		if (!is_string($moduleName) || $moduleName == "")
		{
			throw new LoaderException("Empty module name");
		}
		if (preg_match("#[^a-zA-Z0-9._]#", $moduleName))
		{
			throw new LoaderException(sprintf("Module name '%s' is not correct", $moduleName));
		}

		$moduleName = strtolower($moduleName);

		if (self::SAFE_MODE)
		{
			if (!isset(self::$safeModeModules[$moduleName]))
			{
				return false;
			}
		}

		if (isset(self::$loadedModules[$moduleName]))
		{
			return self::$loadedModules[$moduleName];
		}

		if (isset(self::$semiloadedModules[$moduleName]))
		{
			trigger_error("Module '".$moduleName."' is in loading progress", E_USER_WARNING);
		}

		$arInstalledModules = ModuleManager::getInstalledModules();
		if (!isset($arInstalledModules[$moduleName]))
		{
			return (self::$loadedModules[$moduleName] = false);
		}

		$documentRoot = self::getDocumentRoot();

		$moduleHolder = self::LOCAL_HOLDER;
		$pathToInclude = $documentRoot."/".$moduleHolder."/modules/".$moduleName;
		if (!file_exists($pathToInclude))
		{
			$moduleHolder = self::BITRIX_HOLDER;
			$pathToInclude = $documentRoot."/".$moduleHolder."/modules/".$moduleName;
			if (!file_exists($pathToInclude))
			{
				return (self::$loadedModules[$moduleName] = false);
			}
		}

		//register a PSR-4 base folder for the module
		if(strpos($moduleName, ".") !== false)
		{
			//partner's module
			$baseName = str_replace(".", "\\", ucwords($moduleName, "."));
		}
		else
		{
			//bitrix's module
			$baseName = "Bitrix\\".ucfirst($moduleName);
		}
		self::registerNamespace($baseName, $documentRoot."/".$moduleHolder."/modules/".$moduleName."/lib");

		self::$modulesHolders[$moduleName] = $moduleHolder;

		$res = true;
		if(file_exists($pathToInclude."/include.php"))
		{
			//recursion control
			self::$semiloadedModules[$moduleName] = true;

			$res = self::includeModuleInternal($pathToInclude."/include.php");

			unset(self::$semiloadedModules[$moduleName]);
		}

		self::$loadedModules[$moduleName] = ($res !== false);

		if(self::$loadedModules[$moduleName] == false)
		{
			//unregister the namespace if "include" fails
			self::unregisterNamespace($baseName);
		}
		else
		{
			ServiceLocator::getInstance()->registerByModuleSettings($moduleName);
		}

		return self::$loadedModules[$moduleName];
	}

	/**
	 * Includes module by its name, throws an exception in case of failure
	 *
	 * @param $moduleName
	 *
	 * @return bool
	 * @throws LoaderException
	 */
	public static function requireModule($moduleName)
	{
		$included = self::includeModule($moduleName);

		if (!$included && self::$requireThrowException)
		{
			throw new LoaderException("Required module `{$moduleName}` was not found");
		}

		return $included;
	}

	private static function includeModuleInternal($path)
	{
		/** @noinspection PhpUnusedLocalVariableInspection */
		global $DB, $MESS;
		return include_once($path);
	}

	/**
	 * Includes shareware module by its name.
	 * Module must initialize constant <module name>_DEMO = Y in include.php to define demo mode.
	 * include.php must return false to define trial period expiration.
	 * Constants is used because it is easy to obfuscate them.
	 *
	 * @param string $moduleName Name of the included module
	 * @return int One of the following constant: Loader::MODULE_NOT_FOUND, Loader::MODULE_INSTALLED, Loader::MODULE_DEMO, Loader::MODULE_DEMO_EXPIRED
	 */
	public static function includeSharewareModule($moduleName)
	{
		if (isset(self::$sharewareModules[$moduleName]))
		{
			return self::$sharewareModules[$moduleName];
		}

		$module = str_replace(".", "_", $moduleName);

		if (self::includeModule($moduleName))
		{
			if (defined($module."_DEMO") && constant($module."_DEMO") == "Y")
			{
				self::$sharewareModules[$moduleName] = self::MODULE_DEMO;
			}
			else
			{
				self::$sharewareModules[$moduleName] = self::MODULE_INSTALLED;
			}

			return self::$sharewareModules[$moduleName];
		}

		if (defined($module."_DEMO") && constant($module."_DEMO") == "Y")
		{
			return (self::$sharewareModules[$moduleName] = self::MODULE_DEMO_EXPIRED);
		}

		return (self::$sharewareModules[$moduleName] = self::MODULE_NOT_FOUND);
	}

	public static function clearModuleCache($moduleName)
	{
		if (!is_string($moduleName) || $moduleName == "")
		{
			throw new LoaderException("Empty module name");
		}

		if($moduleName !== "main")
		{
			unset(self::$loadedModules[$moduleName]);
			unset(self::$modulesHolders[$moduleName]);
		}

		unset(self::$sharewareModules[$moduleName]);
	}

	/**
	 * Returns document root
	 *
	 * @return string Document root
	 */
	public static function getDocumentRoot()
	{
		static $documentRoot = null;
		if ($documentRoot === null)
		{
			$documentRoot = rtrim($_SERVER["DOCUMENT_ROOT"], "/\\");
		}
		return $documentRoot;
	}

	/**
	 * Registers classes for auto loading.
	 * All the frequently used classes should be registered for auto loading (performance).
	 * It is not necessary to register rarely used classes. They can be found and loaded dynamically.
	 *
	 * @param string $moduleName Name of the module. Can be null if classes are not part of any module
	 * @param array $classes Array of classes with class names as keys and paths as values.
	 * @throws LoaderException
	 */
	public static function registerAutoLoadClasses($moduleName, array $classes)
	{
		if (empty($classes))
		{
			return;
		}
		if (($moduleName !== null) && empty($moduleName))
		{
			throw new LoaderException(sprintf("Module name '%s' is not correct", $moduleName));
		}

		foreach ($classes as $class => $file)
		{
			$class = ltrim($class, "\\");
			$class = strtolower($class);

			self::$autoLoadClasses[$class] = [
				"module" => $moduleName,
				"file" => $file,
			];
		}
	}

	/**
	 * Registers namespaces with custom paths.
	 * e.g. ('Bitrix\Main\Dev', '/home/bitrix/web/site/bitrix/modules/main/dev/lib')
	 *
	 * @param string $namespace A namespace prefix.
	 * @param string $path An absolute path.
	 */
	public static function registerNamespace($namespace, $path)
	{
		$namespace = trim($namespace, "\\")."\\";
		$namespace = strtolower($namespace);

		$path = rtrim($path, "/\\");
		$depth = substr_count(rtrim($namespace, "\\"), "\\");

		self::$namespaces[$namespace][] = [
			"path" => $path,
			"depth" => $depth,
		];
	}

	/**
	 * Unregisters a namespace.
	 * @param string $namespace
	 */
	public static function unregisterNamespace($namespace)
	{
		$namespace = trim($namespace, "\\")."\\";
		$namespace = strtolower($namespace);

		unset(self::$namespaces[$namespace]);
	}

	/**
	 * Registers an additional autoload handler.
	 * @param callable $handler
	 */
	public static function registerHandler(callable $handler)
	{
		\spl_autoload_register($handler);
	}

	/**
	 * PSR-4 compatible autoloader.
	 * https://www.php-fig.org/psr/psr-4/
	 *
	 * @param $className
	 */
	public static function autoLoad($className)
	{
		// fix web env
		$className = ltrim($className, "\\");

		$classLower = strtolower($className);

		static $documentRoot = null;
		if ($documentRoot === null)
		{
			$documentRoot = self::getDocumentRoot();
		}

		//optimization via direct paths
		if (isset(self::$autoLoadClasses[$classLower]))
		{
			$pathInfo = self::$autoLoadClasses[$classLower];
			if ($pathInfo["module"] != "")
			{
				$module = $pathInfo["module"];
				$holder = (self::$modulesHolders[$module] ?? self::BITRIX_HOLDER);

				$filePath = (defined('REPOSITORY_ROOT'))
					? REPOSITORY_ROOT
					: "{$documentRoot}/{$holder}/modules";

				$filePath .= '/'.$module."/".$pathInfo["file"];

				require_once($filePath);
			}
			else
			{
				require_once($documentRoot.$pathInfo["file"]);
			}
			return;
		}

		if (preg_match("#[^\\\\/a-zA-Z0-9_]#", $className))
		{
			return;
		}

		$tryFiles = [[
			"real" => $className,
			"lower" => $classLower,
		]];

		if (substr($classLower, -5) == "table")
		{
			// old *Table stored in reserved files
			$tryFiles[] = [
				"real" => substr($className, 0, -5),
				"lower" => substr($classLower, 0, -5),
			];
		}

		foreach ($tryFiles as $classInfo)
		{
			$classParts = explode("\\", $classInfo["lower"]);

			//remove class name
			array_pop($classParts);

			while(!empty($classParts))
			{
				//go from the end
				$namespace = implode("\\", $classParts)."\\";

				if(isset(self::$namespaces[$namespace]))
				{
					//found
					foreach (self::$namespaces[$namespace] as $namespaceLocation)
					{
						$depth = $namespaceLocation["depth"];
						$path = $namespaceLocation["path"];

						$fileParts = explode("\\", $classInfo["real"]);

						for ($i=0; $i <= $depth; $i++)
						{
							array_shift($fileParts);
						}

						$classPath = implode("/", $fileParts);

						$classPathLower = strtolower($classPath);

						// final path lower case
						$filePath = $path.'/'.$classPathLower.".php";

						if (file_exists($filePath))
						{
							require_once($filePath);
							break 3;
						}

						// final path original case
						$filePath = $path.'/'.$classPath.".php";

						if (file_exists($filePath))
						{
							require_once($filePath);
							break 3;
						}
					}
				}

				//try the shorter namespace
				array_pop($classParts);
			}
		}
	}

	/**
	 * @param $className
	 *
	 * @throws LoaderException
	 */
	public static function requireClass($className)
	{
		$file = ltrim($className, "\\");    // fix web env
		$file = strtolower($file);

		if (preg_match("#[^\\\\/a-zA-Z0-9_]#", $file))
			return;

		$tryFiles = [$file];

		if (substr($file, -5) == "table")
		{
			// old *Table stored in reserved files
			$tryFiles[] = substr($file, 0, -5);
		}

		foreach ($tryFiles as $file)
		{
			$file = str_replace('\\', '/', $file);
			$arFile = explode("/", $file);

			if ($arFile[0] === "bitrix")
			{
				array_shift($arFile);

				if (empty($arFile))
				{
					break;
				}

				$module = array_shift($arFile);
				if ($module == null || empty($arFile))
				{
					break;
				}
			}
			else
			{
				$module1 = array_shift($arFile);
				$module2 = array_shift($arFile);

				if ($module1 == null || $module2 == null || empty($arFile))
				{
					break;
				}

				$module = $module1.".".$module2;
			}

			if (!self::includeModule($module))
			{
				throw new LoaderException(sprintf(
					"There is no `%s` class, module `%s` is unavailable", $className, $module
				));
			}
		}

		self::autoLoad($className);
	}

	/**
	 * Checks if file exists in /local or /bitrix directories
	 *
	 * @param string $path File path relative to /local/ or /bitrix/
	 * @param string|null $root Server document root, default self::getDocumentRoot()
	 * @return string|bool Returns combined path or false if the file does not exist in both dirs
	 */
	public static function getLocal($path, $root = null)
	{
		if ($root === null)
		{
			$root = self::getDocumentRoot();
		}

		if (file_exists($root."/local/".$path))
		{
			return $root."/local/".$path;
		}
		elseif (file_exists($root."/bitrix/".$path))
		{
			return $root."/bitrix/".$path;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Checks if file exists in personal directory.
	 * If $_SERVER["BX_PERSONAL_ROOT"] is not set than personal directory is equal to /bitrix/
	 *
	 * @param string $path File path relative to personal directory
	 * @return string|bool Returns combined path or false if the file does not exist
	 */
	public static function getPersonal($path)
	{
		$root = self::getDocumentRoot();
		$personal = ($_SERVER["BX_PERSONAL_ROOT"] ?? "");

		if ($personal <> '' && file_exists($root.$personal."/".$path))
		{
			return $root.$personal."/".$path;
		}

		return self::getLocal($path, $root);
	}

	/**
	 * Changes requireModule behavior
	 *
	 * @param bool $requireThrowException
	 */
	public static function setRequireThrowException($requireThrowException)
	{
		self::$requireThrowException = (bool) $requireThrowException;
	}
}

class LoaderException extends \Exception
{
	public function __construct($message = "", $code = 0, \Exception $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}
}
