<?php

namespace Bitrix\Main\Session;

use Bitrix\Main\Application;
use Bitrix\Main\Config\Configuration;

class Session implements SessionInterface, \ArrayAccess
{
	use ArrayAccessWithReferences;

	/** @var bool */
	protected $started = false;
	/** @var \SessionHandlerInterface|null */
	protected $sessionHandler;
	/** @var bool */
	protected $lazyStartEnabled = false;
	/** @var string */
	protected $previousSessionId;
	/** @var bool */
	protected $debug = false;

	/**
	 * Session constructor.
	 */
	public function __construct(\SessionHandlerInterface $sessionHandler = null)
	{
		$this->sessionHandler = $sessionHandler;

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

	public function isActive(): bool
	{
		return session_status() === \PHP_SESSION_ACTIVE;
	}

	public function getId(): string
	{
		return session_id();
	}

	public function setId($id)
	{
		if ($this->isActive())
		{
			throw new \RuntimeException('Could not change the ID of an active session');
		}

		session_id($id);
	}

	public function getPreviousId(): ?string
	{
		return $this->previousSessionId;
	}

	public function resetPreviousId(): void
	{
		$this->previousSessionId = null;
	}

	public function getName(): string
	{
		return session_name();
	}

	public function setName($name)
	{
		if ($this->isActive())
		{
			throw new \RuntimeException('Could not change the name of an active session');
		}

		session_name($name);
	}

	public function start(): bool
	{
		if ($this->started)
		{
			return true;
		}

		if (session_status() === \PHP_SESSION_ACTIVE)
		{
			throw new \RuntimeException('Could not start session by PHP.');
		}

		if (filter_var(ini_get('session.use_cookies'), FILTER_VALIDATE_BOOLEAN) && headers_sent($file, $line))
		{
			throw new \RuntimeException(
				"Could not start session because headers have already been sent. \"{$file}\":{$line}."
			);
		}

		$this->debug('Session tries to start at');
		if (!session_start())
		{
			throw new \RuntimeException('Could not start session by PHP.');
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
			elseif ($this->has('newSid'))
			{
				$newSessionId = $this->get('newSid');
				$this->remove('newSid');
				$this->save();

				$this->setId($newSessionId);

				return $this->start();
			}
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
		$this->save();

		$this->previousSessionId = $this->getId();
		$prevStrictMode = ini_set('session.use_strict_mode', 0);
		$this->setId($newSessionId);
		if ($prevStrictMode !== false)
		{
			ini_set('session.use_strict_mode', $prevStrictMode);
		}

		$this->start();
		$_SESSION = $backup;

		$this->remove('newSid');
		$this->remove('destroyed');

		return true;
	}

	public function destroy()
	{
		if ($this->isActive())
		{
			session_destroy();
			$this->started = false;
		}
	}

	public function save()
	{
		$session = $_SESSION;

		$previousHandler = set_error_handler(
			function($type, $msg, $file, $line) use (&$previousHandler) {
				if (E_WARNING === $type && 0 === strpos($msg, 'session_write_close():'))
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

	protected function processLazyStart()
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

	public function clear()
	{
		$_SESSION = [];
		$this->nullPointers = [];
	}

	public function isStarted()
	{
		return (bool)$this->started;
	}

	private function debug(string $text)
	{
		if (!$this->debug)
		{
			return;
		}

		$requestUri = Application::getInstance()->getContext()->getServer()->getRequestUri();
		AddMessage2Log($text . ' ' . $requestUri, 'main', 20);
	}
}