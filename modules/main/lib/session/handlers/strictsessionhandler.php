<?php

namespace Bitrix\Main\Session\Handlers;

class StrictSessionHandler extends AbstractSessionHandler
{
	/** @var NativeFileSessionHandler */
	private $handler;

	public function __construct(NativeFileSessionHandler $handler)
	{
		$this->handler = $handler;
	}

	public function gc($maxLifeTime)
	{
		return $this->handler->gc($maxLifeTime);
	}

	public function open($savePath, $sessionName)
	{
		return $this->handler->open($savePath, $sessionName);
	}

	public function updateTimestamp($sessionId, $sessionData)
	{
		return $this->write($sessionId, $sessionData);
	}

	protected function processRead($sessionId): string
	{
		return $this->handler->read($sessionId);
	}

	protected function processWrite($sessionId, $sessionData): bool
	{
		return $this->handler->write($sessionId, $sessionData);
	}

	protected function lock($sessionId): bool
	{
		return true;
	}

	protected function unlock($sessionId): bool
	{
		return true;
	}

	protected function processDestroy($sessionId): bool
	{
		return $this->handler->destroy($sessionId);
	}

	public function close()
	{
		return $this->handler->close();
	}
}