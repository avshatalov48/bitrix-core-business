<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2023 Bitrix
 */

namespace Bitrix\Main;

use Bitrix\Main\Config\Configuration;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Routing\CompileCache;
use Bitrix\Main\Routing\Route;
use Bitrix\Main\Routing\Router;
use Bitrix\Main\Routing\RoutingConfigurator;
use Bitrix\Main\Session\CompositeSessionManager;
use Bitrix\Main\Session\KernelSessionProxy;
use Bitrix\Main\Session\SessionConfigurationResolver;
use Bitrix\Main\Session\SessionInterface;
use Bitrix\Main\Session\Handlers\CookieSessionHandler;

/**
 * Base class for any application.
 */
abstract class Application
{
	const JOB_PRIORITY_NORMAL = 100;
	const JOB_PRIORITY_LOW = 50;

	/**
	 * @var Application
	 */
	protected static $instance;
	protected bool $initialized = false;
	protected bool $terminating = false;
	/**
	 * Execution context.
	 *
	 * @var Context | HttpContext
	 */
	protected $context;
	/** @var Router */
	protected $router;
	/** @var Route */
	protected $currentRoute;
	/**
	 * Pool of database connections.
	 * @var Data\ConnectionPool
	 */
	protected $connectionPool;
	/**
	 * Managed cache instance.
	 * @var \Bitrix\Main\Data\ManagedCache
	 */
	protected $managedCache;
	/**
	 * Tagged cache instance.
	 * @var \Bitrix\Main\Data\TaggedCache
	 */
	protected $taggedCache;
	/** @var SessionInterface */
	protected $session;
	/** @var SessionInterface */
	protected $kernelSession;
	/** @var CompositeSessionManager */
	protected $compositeSessionManager;
	/** @var Data\LocalStorage\SessionLocalStorageManager */
	protected $sessionLocalStorageManager;
	/*
	 * @var \SplPriorityQueue
	 */
	protected $backgroundJobs;
	/** @var License */
	protected $license;

	/**
	 * Creates new application instance.
	 */
	protected function __construct()
	{
		ServiceLocator::getInstance()->registerByGlobalSettings();
		$this->backgroundJobs = new \SplPriorityQueue();
		$this->initializeExceptionHandler();
		$this->initializeCache();
		$this->createDatabaseConnection();
	}

	/**
	 * Returns current instance of the Application.
	 *
	 * @return Application | HttpApplication
	 */
	public static function getInstance()
	{
		if (!isset(static::$instance))
		{
			static::$instance = new static();
		}
		return static::$instance;
	}

	/**
	 * @return bool
	 */
	public static function hasInstance()
	{
		return isset(static::$instance);
	}

	/**
	 * @deprecated
	 * Does nothing, will be removed soon.
	 */
	public function initializeBasicKernel()
	{
	}

	/**
	 * Does full kernel initialization. Should be called somewhere after initializeBasicKernel().
	 *
	 * @param array $params Parameters of the current request (depends on application type)
	 */
	public function initializeExtendedKernel(array $params)
	{
		$this->initializeContext($params);

		if (!$this->initialized)
		{
			$this->initializeSessions();
			$this->initializeSessionLocalStorage();

			$this->initialized = true;
		}
	}

	private function initializeSessions()
	{
		$resolver = new SessionConfigurationResolver(Configuration::getInstance());
		$resolver->resolve();

		$this->session = $resolver->getSession();
		$this->kernelSession = $resolver->getKernelSession();

		$this->compositeSessionManager = new CompositeSessionManager(
			$this->kernelSession,
			$this->session
		);
	}

	private function initializeSessionLocalStorage()
	{
		$cacheEngine = Data\Cache::createCacheEngine();
		if ($cacheEngine instanceof Data\LocalStorage\Storage\CacheEngineInterface)
		{
			$localSessionStorage = new Data\LocalStorage\Storage\CacheStorage($cacheEngine);
		}
		else
		{
			$localSessionStorage = new Data\LocalStorage\Storage\NativeSessionStorage(
				$this->getSession()
			);
		}

		$this->sessionLocalStorageManager = new Data\LocalStorage\SessionLocalStorageManager($localSessionStorage);
		$configLocalStorage = Config\Configuration::getValue("session_local_storage") ?: [];
		if (isset($configLocalStorage['ttl']))
		{
			$this->sessionLocalStorageManager->setTtl($configLocalStorage['ttl']);
		}
	}

	/**
	 * @return Router
	 */
	public function getRouter(): Router
	{
		if ($this->router === null)
		{
			$this->router = $this->initializeRouter();
		}

		return $this->router;
	}

	/**
	 * @param Router $router
	 */
	public function setRouter(Router $router): void
	{
		$this->router = $router;
	}

	/**
	 * @return Route
	 */
	public function getCurrentRoute(): Route
	{
		return $this->currentRoute;
	}

	public function hasCurrentRoute(): bool
	{
		return isset($this->currentRoute);
	}

	/**
	 * @param Route $currentRoute
	 */
	public function setCurrentRoute(Route $currentRoute): void
	{
		$this->currentRoute = $currentRoute;
	}

	protected function initializeRouter()
	{
		$routes = new RoutingConfigurator();
		$router = new Router();
		$routes->setRouter($router);

		$this->setRouter($router);

		// files with routes
		$files = [];

		// user files
		$routingConfig = Configuration::getInstance()->get('routing');
		$documentRoot = $this->context->getServer()->getDocumentRoot();

		if (!empty($routingConfig['config']))
		{
			$fileNames = $routingConfig['config'];

			foreach ($fileNames as $fileName)
			{
				foreach (['local', 'bitrix'] as $vendor)
				{
					$filename = $documentRoot . '/' . $vendor . '/routes/' . basename($fileName);

					if (file_exists($filename))
					{
						$files[] = $filename;
					}
				}
			}
		}

		// system files
		if (file_exists($documentRoot . '/bitrix/routes/web_bitrix.php'))
		{
			$files[] = $documentRoot . '/bitrix/routes/web_bitrix.php';
		}

		foreach ($files as $file)
		{
			$callback = include $file;
			$callback($routes);
		}

		$router->releaseRoutes();

		// cache for route compiled data
		CompileCache::handle($files, $router);

		return $router;
	}

	/**
	 * Initializes context of the current request.
	 * Should be implemented in subclass.
	 * @param array $params
	 */
	abstract protected function initializeContext(array $params);

	/**
	 * Starts request execution. Should be called after initialize.
	 * Should be implemented in subclass.
	 */
	abstract public function start();

	/**
	 * Runs controller and its action and sends response to the output.
	 *
	 * It's a stub method, and we can't mark it as abstract because there is compatibility.
	 * @return void
	 */
	public function run()
	{
	}

	/**
	 * Ends work of application.
	 * Sends response and then terminates execution.
	 * If there is no $response the method will use Context::$response.
	 *
	 * @param int $status
	 * @param Response|null $response
	 *
	 * @return void
	 */
	public function end($status = 0, Response $response = null)
	{
		if ($response === null)
		{
			//use default response
			$response = $this->context->getResponse();

			//it's possible to have open buffers
			$content = '';
			$n = ob_get_level();
			while (($c = ob_get_clean()) !== false && $n > 0)
			{
				$content .= $c;
				$n--;
			}

			if ($content <> '')
			{
				$response->appendContent($content);
			}
		}

		$this->handleResponseBeforeSend($response);

		//this is the last point of output - all output below will be ignored
		$response->send();

		$this->terminate($status);
	}

	protected function handleResponseBeforeSend(Response $response): void
	{
		$kernelSession = $this->getKernelSession();
		if (!($kernelSession instanceof KernelSessionProxy) && $kernelSession->isStarted())
		{
			//save session data in cookies
			$handler = $kernelSession->getSessionHandler();
			if ($handler instanceof CookieSessionHandler)
			{
				if ($response instanceof HttpResponse)
				{
					$handler->setResponse($response);
				}
			}
			$kernelSession->save();
		}
	}

	/**
	 * Terminates application by invoking exit().
	 * It's the right way to finish application.
	 *
	 * @param int $status
	 * @return void
	 */
	public function terminate($status = 0)
	{
		if ($this->terminating)
		{
			// recursion control
			return;
		}

		$this->terminating = true;

		//old kernel staff
		\CMain::RunFinalActionsInternal();

		//Release session
		session_write_close();

		$pool = $this->getConnectionPool();

		$pool->useMasterOnly(true);

		$this->runBackgroundJobs();

		$pool->useMasterOnly(false);

		Data\ManagedCache::finalize();

		$pool->disconnect();

		exit($status);
	}

	/**
	 * Exception handler can be initialized through the Config\Configuration (.settings.php file).
	 *
	 * 'exception_handling' => array(
	 *        'value' => array(
	 *            'debug' => true,        // output exception on screen
	 *            'handled_errors_types' => E_ALL & ~E_NOTICE,    // catchable error types, printed to log
	 *            'exception_errors_types' => E_ALL & ~E_NOTICE,  // error types from catchable which throws exceptions
	 *            'ignore_silence' => false,      // ignore @
	 *            'assertion_throws_exception' => true,       // assertion throws exception
	 *            'assertion_error_type' => 256,
	 *            'log' => array(
	 *              'class_name' => 'MyLog',        // custom log class, must extend ExceptionHandlerLog; can be omited, in this case default Diag\FileExceptionHandlerLog will be used
	 *              'extension' => 'MyLogExt',      // php extension, is used only with 'class_name'
	 *              'required_file' => 'modules/mylog.module/mylog.php'     // included file, is used only with 'class_name'
	 *                'settings' => array(        // any settings for 'class_name'
	 *                    'file' => 'bitrix/modules/error.log',
	 *                    'log_size' => 1000000,
	 *                ),
	 *            ),
	 *        ),
	 *        'readonly' => false,
	 *    ),
	 *
	 */
	protected function initializeExceptionHandler()
	{
		$exceptionHandler = new Diag\ExceptionHandler();

		$exceptionHandling = Config\Configuration::getValue("exception_handling");
		if ($exceptionHandling == null)
		{
			$exceptionHandling = [];
		}

		if (!isset($exceptionHandling["debug"]) || !is_bool($exceptionHandling["debug"]))
		{
			$exceptionHandling["debug"] = false;
		}
		$exceptionHandler->setDebugMode($exceptionHandling["debug"]);

		if (!empty($exceptionHandling['track_modules']) && is_array($exceptionHandling['track_modules']))
		{
			$exceptionHandler->setTrackModules($exceptionHandling['track_modules']);
		}

		if (isset($exceptionHandling["handled_errors_types"]) && is_int($exceptionHandling["handled_errors_types"]))
		{
			$exceptionHandler->setHandledErrorsTypes($exceptionHandling["handled_errors_types"]);
		}

		if (isset($exceptionHandling["exception_errors_types"]) && is_int($exceptionHandling["exception_errors_types"]))
		{
			$exceptionHandler->setExceptionErrorsTypes($exceptionHandling["exception_errors_types"]);
		}

		if (isset($exceptionHandling["ignore_silence"]) && is_bool($exceptionHandling["ignore_silence"]))
		{
			$exceptionHandler->setIgnoreSilence($exceptionHandling["ignore_silence"]);
		}

		if (isset($exceptionHandling["assertion_throws_exception"]) && is_bool($exceptionHandling["assertion_throws_exception"]))
		{
			$exceptionHandler->setAssertionThrowsException($exceptionHandling["assertion_throws_exception"]);
		}

		if (isset($exceptionHandling["assertion_error_type"]) && is_int($exceptionHandling["assertion_error_type"]))
		{
			$exceptionHandler->setAssertionErrorType($exceptionHandling["assertion_error_type"]);
		}

		$exceptionHandler->initialize(
			[$this, "createExceptionHandlerOutput"],
			[$this, "createExceptionHandlerLog"]
		);

		ServiceLocator::getInstance()->addInstance('exceptionHandler', $exceptionHandler);
	}

	public function createExceptionHandlerLog()
	{
		$exceptionHandling = Config\Configuration::getValue("exception_handling");

		if (!is_array($exceptionHandling) || !isset($exceptionHandling["log"]) || !is_array($exceptionHandling["log"]))
		{
			return null;
		}

		$options = $exceptionHandling["log"];

		$log = null;

		if (!empty($options["class_name"]))
		{
			if (!empty($options["extension"]) && !extension_loaded($options["extension"]))
			{
				return null;
			}

			if (!empty($options["required_file"]) && ($requiredFile = Loader::getLocal($options["required_file"])) !== false)
			{
				require_once($requiredFile);
			}

			$className = $options["class_name"];
			if (!class_exists($className))
			{
				return null;
			}

			$log = new $className();
		}
		elseif (isset($options["settings"]) && is_array($options["settings"]))
		{
			$log = new Diag\FileExceptionHandlerLog();
		}
		else
		{
			return null;
		}

		$log->initialize(
			isset($options["settings"]) && is_array($options["settings"]) ? $options["settings"] : []
		);

		return $log;
	}

	public function createExceptionHandlerOutput()
	{
		return new Diag\ExceptionHandlerOutput();
	}

	/**
	 * Creates database connection pool.
	 */
	protected function createDatabaseConnection()
	{
		$this->connectionPool = new Data\ConnectionPool();
	}

	protected function initializeCache()
	{
		//TODO: Should be transfered to where GET parameter is defined in future
		//magic parameters: show cache usage statistics
		$show_cache_stat = "";
		if (isset($_GET["show_cache_stat"]))
		{
			$show_cache_stat = (strtoupper($_GET["show_cache_stat"]) == "Y" ? "Y" : "");
			@setcookie("show_cache_stat", $show_cache_stat, false, "/");
		}
		elseif (isset($_COOKIE["show_cache_stat"]))
		{
			$show_cache_stat = $_COOKIE["show_cache_stat"];
		}
		Data\Cache::setShowCacheStat($show_cache_stat === "Y");

		if (isset($_GET["clear_cache_session"]))
		{
			Data\Cache::setClearCacheSession($_GET["clear_cache_session"] === 'Y');
		}
		if (isset($_GET["clear_cache"]))
		{
			Data\Cache::setClearCache($_GET["clear_cache"] === 'Y');
		}
	}

	/**
	 * @return \Bitrix\Main\Diag\ExceptionHandler
	 */
	public function getExceptionHandler()
	{
		return ServiceLocator::getInstance()->get('exceptionHandler');
	}

	/**
	 * Returns database connections pool object.
	 *
	 * @return Data\ConnectionPool
	 */
	public function getConnectionPool()
	{
		return $this->connectionPool;
	}

	/**
	 * Returns context of the current request.
	 *
	 * @return Context | HttpContext
	 */
	public function getContext()
	{
		return $this->context;
	}

	/**
	 * Modifies context of the current request.
	 *
	 * @param Context $context
	 */
	public function setContext(Context $context)
	{
		$this->context = $context;
	}

	public function getLicense(): License
	{
		if (!$this->license)
		{
			$this->license = new License();
		}

		return $this->license;
	}

	/**
	 * Static method returns database connection for the specified name.
	 * If name is empty - default connection is returned.
	 *
	 * @static
	 * @param string $name Name of database connection. If empty - default connection.
	 * @return Data\Connection|DB\Connection
	 */
	public static function getConnection($name = "")
	{
		$pool = Application::getInstance()->getConnectionPool();
		return $pool->getConnection($name);
	}

	/**
	 * Returns new instance of the Cache object.
	 *
	 * @return Data\Cache
	 */
	public function getCache()
	{
		return Data\Cache::createInstance();
	}

	/**
	 * Returns manager of the managed cache.
	 *
	 * @return Data\ManagedCache
	 */
	public function getManagedCache()
	{
		if ($this->managedCache == null)
		{
			$this->managedCache = new Data\ManagedCache();
		}

		return $this->managedCache;
	}

	/**
	 * Returns manager of the managed cache.
	 *
	 * @return Data\TaggedCache
	 */
	public function getTaggedCache()
	{
		if ($this->taggedCache == null)
		{
			$this->taggedCache = new Data\TaggedCache();
		}

		return $this->taggedCache;
	}

	final public function getSessionLocalStorageManager(): Data\LocalStorage\SessionLocalStorageManager
	{
		return $this->sessionLocalStorageManager;
	}

	final public function getLocalSession($name): Data\LocalStorage\SessionLocalStorage
	{
		return $this->sessionLocalStorageManager->get($name);
	}

	final public function getKernelSession(): SessionInterface
	{
		return $this->kernelSession;
	}

	final public function getSession(): SessionInterface
	{
		return $this->session;
	}

	final public function getCompositeSessionManager(): CompositeSessionManager
	{
		return $this->compositeSessionManager;
	}

	/**
	 * Returns UF manager.
	 *
	 * @return \CUserTypeManager
	 */
	public static function getUserTypeManager()
	{
		global $USER_FIELD_MANAGER;
		return $USER_FIELD_MANAGER;
	}

	/**
	 * Returns true if server is in UTF-8 mode. False - otherwise.
	 *
	 * @deprecated Always returns true.
	 * @return true
	 */
	public static function isUtfMode()
	{
		return true;
	}

	/**
	 * Returns server document root.
	 *
	 * @return null|string
	 */
	public static function getDocumentRoot()
	{
		static $documentRoot = null;
		if ($documentRoot != null)
		{
			return $documentRoot;
		}

		$context = Application::getInstance()->getContext();
		if ($context != null)
		{
			$server = $context->getServer();
			if ($server != null)
			{
				return $documentRoot = $server->getDocumentRoot();
			}
		}

		return Loader::getDocumentRoot();
	}

	/**
	 * Returns personal root directory (relative to document root)
	 *
	 * @return null|string
	 */
	public static function getPersonalRoot()
	{
		static $personalRoot = null;
		if ($personalRoot != null)
		{
			return $personalRoot;
		}

		$context = Application::getInstance()->getContext();
		if ($context != null)
		{
			$server = $context->getServer();
			if ($server != null)
			{
				return $personalRoot = $server->getPersonalRoot();
			}
		}

		return $_SERVER["BX_PERSONAL_ROOT"] ?? '/bitrix';
	}

	/**
	 * Resets accelerator if any.
	 */
	public static function resetAccelerator(string $filename = null)
	{
		if (defined("BX_NO_ACCELERATOR_RESET"))
		{
			return;
		}

		if (Config\Configuration::getValue("no_accelerator_reset"))
		{
			return;
		}

		if (function_exists("opcache_reset"))
		{
			if ($filename !== null)
			{
				opcache_invalidate($filename);
			}
			else
			{
				opcache_reset();
			}
		}
	}

	/**
	 * Adds a job to do after the response was sent.
	 * @param callable $job
	 * @param array $args
	 * @param int $priority
	 * @return $this
	 */
	public function addBackgroundJob(callable $job, array $args = [], $priority = self::JOB_PRIORITY_NORMAL)
	{
		$this->backgroundJobs->insert([$job, $args], $priority);

		return $this;
	}

	protected function runBackgroundJobs()
	{
		$lastException = null;
		$exceptionHandler = $this->getExceptionHandler();

		//jobs can be added from jobs
		while ($this->backgroundJobs->valid())
		{
			//clear the queue
			$jobs = [];
			foreach ($this->backgroundJobs as $job)
			{
				$jobs[] = $job;
			}

			//do job
			foreach ($jobs as $job)
			{
				try
				{
					call_user_func_array($job[0], $job[1]);
				}
				catch (\Throwable $exception)
				{
					$lastException = $exception;
					$exceptionHandler->writeToLog($exception);
				}
			}
		}

		if ($lastException !== null)
		{
			throw $lastException;
		}
	}

	/**
	 * Returns true if the application is fully initialized.
	 * @return bool
	 */
	public function isInitialized()
	{
		return $this->initialized;
	}
}
