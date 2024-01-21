<?php

namespace Bitrix\Main\Diag;

class ExceptionHandler
{
	private $debug = false;
	private $handledErrorsTypes;
	private $exceptionErrorsTypes;
	private array $trackModules = [];
	private $catchOverflowMemory = false;
	private $memoryReserveLimit = 65536;
	/** @noinspection PhpPropertyOnlyWrittenInspection */
	private $memoryReserve;
	private $ignoreSilence = false;
	private $assertionThrowsException = true;
	private $assertionErrorType = E_USER_ERROR;
	/** @var ExceptionHandlerLog */
	private $handlerLog = null;
	private $handlerLogCreator = null;
	/** @var IExceptionHandlerOutput */
	private $handlerOutput = null;
	private $handlerOutputCreator = null;
	private $isInitialized = false;

	/**
	 * ExceptionHandler constructor.
	 */
	public function __construct()
	{
		$this->handledErrorsTypes = E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR | E_RECOVERABLE_ERROR;
		$this->exceptionErrorsTypes = E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR | E_RECOVERABLE_ERROR;
	}

	/**
	 * Sets debug mode.
	 * Should be used for development install.
	 *
	 * @param boolean $debug If true errors will be displayed in html output. If false most errors will be suppressed.
	 *
	 * @return void
	 */
	public function setDebugMode($debug)
	{
		$this->debug = $debug;
	}

	/**
	 * Whenever to try catch and report memory overflows errors or not.
	 *
	 * @param boolean $catchOverflowMemory If true memory overflow errors will be handled.
	 *
	 * @return void
	 */
	public function setOverflowMemoryCatching($catchOverflowMemory)
	{
		$this->catchOverflowMemory = $catchOverflowMemory;
	}

	/**
	 * Sets error types to be handled.
	 *
	 * @param integer $handledErrorsTypes Bitmask of error types.
	 *
	 * @return void
	 * @see http://php.net/manual/en/errorfunc.constants.php
	 */
	public function setHandledErrorsTypes($handledErrorsTypes)
	{
		$this->handledErrorsTypes = $handledErrorsTypes;
	}

	public function getTrackModules(): array
	{
		return $this->trackModules;
	}

	public function setTrackModules(array $trackModules): void
	{
		$this->trackModules = $trackModules;
	}

	/**
	 * Sets assertion types to be handled.
	 *
	 * @param integer $assertionErrorType Bitmask of assertion types.
	 *
	 * @return void
	 * @see http://php.net/manual/en/errorfunc.constants.php
	 */
	public function setAssertionErrorType($assertionErrorType)
	{
		$this->assertionErrorType = $assertionErrorType;
	}

	/**
	 * Whenever to throw an exception on assertion or not.
	 *
	 * @param boolean $assertionThrowsException If true an assertion will throw exception.
	 *
	 * @return void
	 */
	public function setAssertionThrowsException($assertionThrowsException)
	{
		$this->assertionThrowsException = $assertionThrowsException;
	}

	/**
	 * Sets which errors will raise an exception.
	 *
	 * @param integer $errorTypesException Bitmask of error types.
	 *
	 * @return void
	 * @see http://php.net/manual/en/errorfunc.constants.php
	 */
	public function setExceptionErrorsTypes($errorTypesException)
	{
		$this->exceptionErrorsTypes = $errorTypesException;
	}

	/**
	 * Whenever to ignore error_reporting() == 0 or not.
	 *
	 * @param boolean $ignoreSilence If true then error_reporting()==0 will be ignored.
	 *
	 * @return void
	 */
	public function setIgnoreSilence($ignoreSilence)
	{
		$this->ignoreSilence = $ignoreSilence;
	}

	/**
	 * Sets logger object to use for log writing.
	 *
	 * @param ExceptionHandlerLog|null $handlerLog Logger object.
	 *
	 * @return void
	 */
	public function setHandlerLog(ExceptionHandlerLog $handlerLog = null)
	{
		$this->handlerLog = $handlerLog;
	}

	/**
	 * Sets an object used for error message display to user.
	 *
	 * @param IExceptionHandlerOutput $handlerOutput Object will display errors to user.
	 *
	 * @return void
	 */
	public function setHandlerOutput(IExceptionHandlerOutput $handlerOutput)
	{
		$this->handlerOutput = $handlerOutput;
	}

	/**
	 * Adjusts PHP for error handling.
	 *
	 * @return void
	 */
	protected function initializeEnvironment()
	{
		if ($this->debug)
		{
			error_reporting($this->handledErrorsTypes);
			@ini_set('display_errors', 'On');
			@ini_set('display_startup_errors', 'On');
			@ini_set('report_memleaks', 'On');
		}
		else
		{
			error_reporting(E_ERROR | E_PARSE);
		}
	}

	/**
	 * Returns an object used for error message display to user.
	 *
	 * @return IExceptionHandlerOutput|null
	 */
	protected function getHandlerOutput()
	{
		if ($this->handlerOutput === null)
		{
			$h = $this->handlerOutputCreator;
			if (is_callable($h))
			{
				$this->handlerOutput = call_user_func_array($h, []);
			}
		}

		return $this->handlerOutput;
	}

	/**
	 * Returns an object for error message writing to log.
	 *
	 * @return ExceptionHandlerLog|null
	 */
	protected function getHandlerLog()
	{
		if ($this->handlerLog === null)
		{
			$h = $this->handlerLogCreator;
			if (is_callable($h))
			{
				$this->handlerLog = call_user_func_array($h, []);
			}
		}

		return $this->handlerLog;
	}

	/**
	 * Initializes error handling.
	 * Must be called after the object creation.
	 *
	 * @param callable $exceptionHandlerOutputCreator Function to return an object for error message formatting.
	 * @param callable|null $exceptionHandlerLogCreator Function to return an object for log writing.
	 *
	 * @return void
	 */
	public function initialize($exceptionHandlerOutputCreator, $exceptionHandlerLogCreator = null)
	{
		if ($this->isInitialized)
		{
			return;
		}

		$this->initializeEnvironment();

		$this->handlerOutputCreator = $exceptionHandlerOutputCreator;
		$this->handlerLogCreator = $exceptionHandlerLogCreator;

		if ($this->catchOverflowMemory)
		{
			$this->memoryReserve = str_repeat('b', $this->memoryReserveLimit);
		}

		set_error_handler([$this, "handleError"], $this->handledErrorsTypes);
		set_exception_handler([$this, "handleException"]);
		register_shutdown_function([$this, "handleFatalError"]);

		if ($this->debug)
		{
			assert_options(ASSERT_ACTIVE, 1);
			assert_options(ASSERT_WARNING, 0);
			assert_options(ASSERT_BAIL, 0);
			assert_options(ASSERT_CALLBACK, [$this, "handleAssertion"]);
		}
		else
		{
			assert_options(ASSERT_ACTIVE, 0);
		}

		$this->isInitialized = true;
	}

	/**
	 * Writes exception information into log, displays it to user and terminates with die().
	 *
	 * @param \Exception|\Error $exception Exception object.
	 *
	 * @param int $logType
	 * @return void
	 * @see ExceptionHandler::writeToLog
	 * @see ExceptionHandler::initialize
	 */
	public function handleException($exception, $logType = ExceptionHandlerLog::UNCAUGHT_EXCEPTION)
	{
		$this->writeToLog($exception, $logType);
		$out = $this->getHandlerOutput();
		$out->renderExceptionMessage($exception, $this->debug);
		die();
	}

	/**
	 * Creates and exception object from its arguments.
	 * Throws it if $code matches exception mask or writes it into log.
	 *
	 * @param integer $code Error code.
	 * @param string $message Error message.
	 * @param string $file File where error has occurred.
	 * @param integer $line File line number where error has occurred.
	 *
	 * @return true
	 * @throws \ErrorException
	 * @see ExceptionHandler::setExceptionErrorsTypes
	 */
	public function handleError($code, $message, $file, $line)
	{
		$exception = new \ErrorException($message, 0, $code, $file, $line);

		if (!$this->ignoreSilence)
		{
			$errorReporting = error_reporting();
			if (
				$errorReporting === 0 //Prior to PHP 8.0.0
				|| $errorReporting === (E_ERROR | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR | E_RECOVERABLE_ERROR | E_PARSE)
			)
			{
				return true;
			}
		}

		if (!$this->isFileInTrackedModules($file))
		{
			return true;
		}

		if ($code & $this->exceptionErrorsTypes)
		{
			throw $exception;
		}
		else
		{
			$this->writeToLog($exception, ExceptionHandlerLog::LOW_PRIORITY_ERROR);
			return true;
		}
	}

	private function isFileInTrackedModules(string $file): bool
	{
		$modules = $this->getTrackModules();
		if (!$modules)
		{
			return true;
		}

		foreach ($modules as $module)
		{
			$moduleDir = DIRECTORY_SEPARATOR . "modules" . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR;
			if (str_contains($file, $moduleDir))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Creates and exception object from its arguments.
	 * Throws it if assertion set to raise exception (which is by default) or writes it to log.
	 *
	 * @param string $file File where error has occurred.
	 * @param integer $line File line number where error has occurred.
	 * @param string $message Error message.
	 *
	 * @return void
	 * @throws \ErrorException
	 * @see ExceptionHandler::setAssertionThrowsException
	 */
	public function handleAssertion($file, $line, $message)
	{
		$exception = new \ErrorException($message, 0, $this->assertionErrorType, $file, $line);

		if ($this->assertionThrowsException)
		{
			throw $exception;
		}
		else
		{
			$this->writeToLog($exception, ExceptionHandlerLog::ASSERTION);
		}
	}

	/**
	 * Gets error information from error_get_last() function.
	 * Checks if type for certain error types and writes it to log.
	 *
	 * @return void
	 * @see error_get_last
	 * @see ExceptionHandler::setHandledErrorsTypes
	 */
	public function handleFatalError()
	{
		$this->memoryReserve = null;
		if ($error = error_get_last())
		{
			if (($error['type'] & (E_ERROR | E_USER_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_RECOVERABLE_ERROR)))
			{
				if (($error['type'] & $this->handledErrorsTypes))
				{
					$exception = new \ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']);
					$this->handleException($exception, ExceptionHandlerLog::FATAL);
				}
			}
		}
	}

	/**
	 * Writes an exception information to log.
	 *
	 * @param \Throwable $exception Exception object.
	 * @param integer|null $logType See ExceptionHandlerLog class constants.
	 *
	 * @return void
	 * @see ExceptionHandler::initialize
	 */
	public function writeToLog($exception, $logType = null)
	{
		$log = $this->getHandlerLog();
		$log?->write($exception, $logType);
	}
}
