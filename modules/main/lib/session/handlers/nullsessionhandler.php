<?php

namespace Bitrix\Main\Session\Handlers;


class NullSessionHandler extends AbstractSessionHandler
{
	public function gc($maxLifeTime): int
	{
		return 0;
	}

	public function open($savePath, $sessionName): bool
	{
		return true;
	}

	public function updateTimestamp($sessionId, $sessionData): bool
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