<?php

namespace Bitrix\Main\Session\Handlers;


class NullSessionHandler extends AbstractSessionHandler
{
	public function gc($maxLifeTime)
	{}

	public function open($savePath, $sessionName)
	{
		return true;
	}

	public function updateTimestamp($sessionId, $sessionData)
	{
		return true;
	}

	protected function processRead($sessionId): string
	{
		return '';
	}

	protected function processWrite($sessionId, $sessionData): bool
	{
		return true;
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
		return true;
	}
}