<?php

namespace Bitrix\Main\Session\Handlers;

use Bitrix\Main\Application;
use Bitrix\Main\HttpResponse;
use Bitrix\Main\Security\Random;

abstract class AbstractSessionHandler implements \SessionHandlerInterface, \SessionUpdateTimestampHandlerInterface, \SessionIdInterface
{
	public const LOCK_ERROR_MESSAGE = 'Unable to get session lock within 60 seconds.';

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
    /** @var array */
    private $listValidatedIds = [];
	/** @var bool */
	private $releaseLockAfterClose = true;

	#[\ReturnTypeWillChange]
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
			$this->triggerLockFatalError();
		}

		return $this->processRead($sessionId);
	}

	abstract protected function processRead($sessionId): string;

	protected function triggerLockFatalError(string $additionalText = ''): void
	{
		$text = self::LOCK_ERROR_MESSAGE;
		if ($additionalText)
		{
			$text .= $additionalText;
		}

		$httpResponse = new HttpResponse();
		$httpResponse->setStatus('500 Internal Server Error');
		trigger_error($text, E_USER_ERROR);
		Application::getInstance()->end(0, $httpResponse);
	}

	public function write($sessionId, $sessionData): bool
	{
		if (!$this->validateSessionId($sessionId))
		{
			return false;
		}

		if ($this->readOnly)
		{
			return true;
		}

		return $this->processWrite($sessionId, $sessionData);
	}

	abstract protected function processWrite($sessionId, $sessionData): bool;

	abstract protected function lock($sessionId): bool;

	abstract protected function unlock($sessionId): bool;

	private function releaseLocksAfterValidate(): void
	{
		unset($this->listValidatedIds[$this->sessionId]);
		foreach ($this->listValidatedIds as $mustBeUnlockedId => $true)
		{
			$this->unlock($mustBeUnlockedId);
			unset($this->listValidatedIds[$this->sessionId]);
		}
	}

	public function close(): bool
	{
		if (!$this->readOnly && $this->validateSessionId($this->sessionId))
		{
			if (isSessionExpired())
			{
				$this->destroy($this->sessionId);
			}

			if ($this->releaseLockAfterClose)
			{
				$this->unlock($this->sessionId);
			}

			$this->releaseLocksAfterValidate();
		}

		$this->sessionId = null;
		$this->lastCreatedId = null;

		return true;
	}

	public function destroy($sessionId): bool
	{
		if ($this->readOnly)
		{
			return true;
		}

		if (!$this->validateSessionId($sessionId))
		{
			return false;
		}

		$result = $this->processDestroy($sessionId);
		$this->lastCreatedId = null;

		return $result;
	}

	abstract protected function processDestroy($sessionId): bool;

	public function validateId($sessionId): bool
	{
		$this->listValidatedIds[$sessionId] = true;

		$this->prefetchData = $this->read($sessionId);
		$this->prefetchId = $sessionId;

		return $this->prefetchData !== '';
	}

	public function create_sid(): string
	{
		$this->lastCreatedId = Random::getString(32, true);

		return $this->lastCreatedId;
	}

	protected function validateSessionId($sessionId): bool
	{
		return
			$sessionId &&
			is_string($sessionId) &&
			preg_match('/^[\da-z\-,]{6,}$/iD', $sessionId)
		;
	}

	public function turnOffReleaseLockAfterCloseSession(): void
	{
		$this->releaseLockAfterClose = false;
	}

	public function turnOnReleaseLockAfterCloseSession(): void
	{
		$this->releaseLockAfterClose = true;
	}
}