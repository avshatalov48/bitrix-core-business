<?php

namespace Bitrix\Main\Session\Handlers;

use Bitrix\Main\Application;
use Bitrix\Main\HttpResponse;
use Bitrix\Main\Security\Random;

abstract class AbstractSessionHandler implements \SessionHandlerInterface, \SessionUpdateTimestampHandlerInterface, \SessionIdInterface
{
	/** @var bool */
	protected $readOnly = false;
	/** @var string */
	protected $sessionId;
	/** @var string */
    private $prefetchId;
	/** @var string */
    private $prefetchData;
	/** @var string */
    private $lastCreatedId;

	/**
	 * @return string
	 */
	public function read($sessionId)
	{
		if (!$this->validateSessionId($sessionId))
		{
			return '';
		}

		$this->sessionId = $sessionId;
		if ($this->prefetchId !== null)
		{
			$prefetchId = $this->prefetchId;
			$prefetchData = $this->prefetchData;

			$this->prefetchId = null;
			$this->prefetchData = null;

			if ($prefetchId === $this->sessionId)
			{
				return $prefetchData;
			}
		}

		if (!$this->readOnly && !$this->lock($this->sessionId))
		{
			$this->triggerLockFatalError("Unable to get session lock within 60 seconds.");
		}

		return $this->processRead($sessionId);
	}

	abstract protected function processRead($sessionId): string;

	protected function triggerLockFatalError(string $text): void
	{
		$httpResponse = new HttpResponse();
		$httpResponse->setStatus("500 Internal Server Error");

		trigger_error($text, E_USER_ERROR);

		Application::getInstance()->end(0, $httpResponse);
	}

	public function write($sessionId, $sessionData)
	{
		if (!$this->validateSessionId($sessionId))
		{
			return false;
		}

		$session = Application::getInstance()->getSession();
		if ($this->readOnly && !$session->getPreviousId())
		{
			return true;
		}

		if ($session->getPreviousId())
		{
			$oldSessionId = $session->getPreviousId();
			$session->resetPreviousId();

			$this->destroy($oldSessionId);
		}

		return $this->processWrite($sessionId, $sessionData);
	}

	abstract protected function processWrite($sessionId, $sessionData): bool;

	abstract protected function lock($sessionId): bool;

	abstract protected function unlock($sessionId): bool;

	public function close()
	{
		if (!$this->readOnly && $this->validateSessionId($this->sessionId))
		{
			if (isSessionExpired())
			{
				$this->destroy($this->sessionId);
			}

			$this->unlock($this->sessionId);
		}

		$this->sessionId = null;

		return true;
	}

	public function destroy($sessionId)
	{
		if ($this->readOnly)
		{
			return false;
		}

		if (!$this->validateSessionId($sessionId))
		{
			return false;
		}

		$this->processDestroy($sessionId);

		$session = Application::getInstance()->getSession();
		if ($session->getPreviousId())
		{
			$this->processDestroy($session->getPreviousId());
			$session->resetPreviousId();
		}

		return true;
	}

	abstract protected function processDestroy($sessionId): bool;

	public function validateId($sessionId)
	{
		if ($this->lastCreatedId === $sessionId)
		{
			//due to https://bugs.php.net/bug.php?id=77178
			$this->lastCreatedId = null;

			return true;
		}

		$this->prefetchData = $this->read($sessionId);
		$this->prefetchId = $sessionId;

		return $this->prefetchData !== '';
	}

	public function create_sid()
	{
		$this->lastCreatedId = Random::getString(32, true);

		return $this->lastCreatedId;
	}

	protected function validateSessionId(string $sessionId): bool
	{
		return
			$sessionId &&
			is_string($sessionId) &&
			preg_match('/^[\da-z\-,]{6,}$/iD', $sessionId)
		;
	}
}