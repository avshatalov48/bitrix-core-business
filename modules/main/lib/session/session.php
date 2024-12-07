<?php

namespace Bitrix\Main\Session;

use Bitrix\Main\Application;
use Bitrix\Main\Session\Handlers\AbstractSessionHandler;
use Bitrix\Main\Session\Handlers\NullSessionHandler;
use Bitrix\Main\Web\Cookie;

class Session implements SessionInterface, \ArrayAccess
{
	use ArrayAccessWithReferences;

	protected bool $started = false;
	protected ?\SessionHandlerInterface $sessionHandler;
	protected bool $lazyStartEnabled = false;
	protected bool $debug = false;
	protected Debugger $debugger;
	protected bool $ignoringSessionStartErrors = false;
	protected bool $useStrictMode = true;

	/**
	 * Session constructor.
	 */
	public function __construct(\SessionHandlerInterface $sessionHandler = null)
	{
		$this->sessionHandler = $sessionHandler;
		$this->debugger = new Debugger();

		session_register_shutdown();
		if ($this->sessionHandler)
		{
			session_set_save_handler($this->sessionHandler, false);
		}
	}

	public function enableLazyStart(): self
	{
		$this->lazyStartEnabled = true;

		return $this;
	}

	public function disableLazyStart(): self
	{
		$this->lazyStartEnabled = false;

		return $this;
	}

	public function enableDebug(): self
	{
		$this->debug = true;

		return $this;
	}

	public function disableDebug(): self
	{
		$this->debug = false;

		return $this;
	}

	public function enableIgnoringSessionStartErrors(): self
	{
		$this->ignoringSessionStartErrors = true;

		return $this;
	}

	public function disableIgnoringSessionStartErrors(): self
	{
		$this->ignoringSessionStartErrors = false;

		return $this;
	}

	public function isActive(): bool
	{
		return session_status() === \PHP_SESSION_ACTIVE;
	}

	private function isHeadersSent(&$file, &$line): bool
	{
		return filter_var(ini_get('session.use_cookies'), FILTER_VALIDATE_BOOLEAN) && headers_sent($file, $line);
	}

	public function isAccessible(): bool
	{
		return !$this->isHeadersSent($file, $line);
	}

	public function getId(): string
	{
		return session_id();
	}

	public function setId($id): void
	{
		if ($this->isActive())
		{
			throw new \RuntimeException('Could not change the ID of an active session');
		}

		session_id($id);
	}

	public function getName(): string
	{
		return session_name();
	}

	public function setName($name): void
	{
		if ($this->isActive())
		{
			throw new \RuntimeException('Could not change the name of an active session');
		}

		session_name($name);
	}

	public function getSessionHandler(): ?\SessionHandlerInterface
	{
		return $this->sessionHandler;
	}

	public function start(): bool
	{
		if ($this->isStarted())
		{
			return true;
		}

		if ($this->isActive())
		{
			throw new \RuntimeException('Could not start session by PHP because session is active.');
		}

		if ($this->isHeadersSent($file, $line))
		{
			throw new \RuntimeException(
				"Could not start session because headers have already been sent. \"{$file}\":{$line}."
			);
		}

		$this->debug('Session tries to start at');
		$this->detectFirstUsage();

		try
		{
			$this->applySessionStartIniSettings($this->getSessionStartOptions());
			if (!session_start() && !$this->ignoringSessionStartErrors)
			{
				throw new \RuntimeException('Could not start session by PHP.');
			}
		}
		catch (\Error $error)
		{
			if ($this->shouldLogError($error))
			{
				$this->writeToLogError($error);
			}

			if (!$this->ignoringSessionStartErrors)
			{
				throw $error->getPrevious() ?: $error;
			}
		}
		$this->debug('Session started at');

		$this->sessionData = &$_SESSION;
		$this->started = true;

		if ($this->has('destroyed'))
		{
			//todo 100? why?
			if ($this->get('destroyed') < time() - 100)
			{
				$this->clear();
			}
			else
			{
				$newSessionId = $this->get('newSid');
				$this->save();

				$this->setId($newSessionId);

				return $this->start();
			}
		}

		return true;
	}

	protected function getSessionStartOptions(): array
	{
		$useStrictModeValue = $this->useStrictMode? 1 : 0;

		if ($this->sessionHandler instanceof NullSessionHandler)
		{
			return [
				'use_cookies' => 0,
				'use_strict_mode' => $useStrictModeValue,
			];
		}

		$options = [
			'cookie_httponly' => 1,
			'use_strict_mode' => $useStrictModeValue,
		];

		$domain = Cookie::getCookieDomain();
		if ($domain)
		{
			$options['cookie_domain'] = $domain;
		}

		return $options;
	}

	protected function applySessionStartIniSettings(array $settings): void
	{
		foreach ($settings as $name => $value)
		{
			ini_set("session.{$name}", $value);
		}
	}

	private function writeToLogError(\Error $error): void
	{
		$exceptionHandler = Application::getInstance()->getExceptionHandler();
		$exceptionHandler->writeToLog($error);

		if ($error->getPrevious())
		{
			$exceptionHandler->writeToLog($error->getPrevious());
		}
	}

	private function shouldLogError(\Error $error): bool
	{
		if (!$error->getPrevious())
		{
			return true;
		}

		if (str_starts_with($error->getPrevious()->getMessage(), AbstractSessionHandler::LOCK_ERROR_MESSAGE))
		{
			return false;
		}

		return true;
	}

	public function regenerateId(): bool
	{
		if (!$this->isStarted())
		{
			return false;
		}

		$newSessionId = session_create_id();

		$this->set('newSid', $newSessionId);
		$this->set('destroyed', time());

		$backup = $this->sessionData;
		$this->saveWithoutReleaseLock();

		$this->disableStrictMode();
		$this->setId($newSessionId);
// Idea to switch on strict mode after setId is good. But in that case
// session_start will invoke validateId for one time more. So behavior in
// 7.1, 7.2 & 7.4 is different and now it's way to avoid that.
//		if ($prevStrictMode !== false)
//		{
//			ini_set('session.use_strict_mode', $prevStrictMode);
//		}

		$this->start();
		$this->enableStrictMode();

		$_SESSION = $backup;

		$this->remove('newSid');
		$this->remove('destroyed');

		return true;
	}

	public function destroy(): void
	{
		if ($this->isActive())
		{
			session_destroy();
			$this->started = false;
		}
	}

	protected function saveWithoutReleaseLock(): void
	{
		if ($this->sessionHandler instanceof AbstractSessionHandler)
		{
			$this->sessionHandler->turnOffReleaseLockAfterCloseSession();
			$this->save();
			$this->sessionHandler->turnOnReleaseLockAfterCloseSession();
		}
		else
		{
			$this->save();
		}
	}

	public function save(): void
	{
		$session = $_SESSION;

		$previousHandler = set_error_handler(
			function($type, $msg, $file, $line) use (&$previousHandler) {
				if ($type === E_WARNING && str_starts_with($msg, 'session_write_close():'))
				{
					$handler = $this->sessionHandler;
					$msg = sprintf(
						"session_write_close(): Failed to write session data with \"%s\" handler",
						$handler? \get_class($handler) : ''
					);
				}

				return $previousHandler ? $previousHandler($type, $msg, $file, $line) : false;
			}
		);

		try
		{
			$this->refineReferencesBeforeSave();
			session_write_close();
		}
		finally
		{
			restore_error_handler();
			if ($_SESSION)
			{
				$_SESSION = $session;
			}
		}

		$this->started = false;
	}

	protected function processLazyStart(): bool
	{
		if (!$this->lazyStartEnabled)
		{
			return false;
		}
		if ($this->isStarted())
		{
			return false;
		}

		return $this->start();
	}

	public function clear(): void
	{
		$_SESSION = [];
		$this->nullPointers = [];
	}

	public function isStarted(): bool
	{
		return $this->started;
	}

	/**
	 * @return Debugger
	 */
	public function getDebugger(): Debugger
	{
		return $this->debugger;
	}

	private function debug(string $text): void
	{
		if (!$this->debug)
		{
			return;
		}

		$this->getDebugger()->logToFile($text);
	}

	private function detectFirstUsage(): void
	{
		if (!$this->debug)
		{
			return;
		}

		$this->getDebugger()->detectFirstUsage();
	}

	private function enableStrictMode(): self
	{
		$this->useStrictMode = true;

		return $this;
	}

	private function disableStrictMode(): self
	{
		ini_set('session.use_strict_mode', 0);
		$this->useStrictMode = false;

		return $this;
	}
}