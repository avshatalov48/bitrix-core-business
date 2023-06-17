<?php

namespace Bitrix\Main\Session\Handlers;

use Bitrix\Main\Session\Handlers\Table\UserSessionTable;

class DatabaseSessionHandler extends AbstractSessionHandler
{
	public function __construct(array $options)
	{
		$this->readOnly = $options['readOnly'] ?? false; //defined('BX_SECURITY_SESSION_READONLY');
	}

	public function open($savePath, $sessionName): bool
	{
		return true;
	}

	public function processRead($sessionId): string
	{
		$sessionRow = UserSessionTable::getRow([
			'select' => ['SESSION_DATA'],
			'filter' => [
				'=SESSION_ID' => $sessionId
			]
		]);

		if (isset($sessionRow['SESSION_DATA']))
		{
			return base64_decode($sessionRow['SESSION_DATA']);
		}

		return '';
	}

	public function processWrite($sessionId, $sessionData): bool
	{
		$this->processDestroy($sessionId);
		$result = UserSessionTable::add([
			'SESSION_ID' => $sessionId,
			'TIMESTAMP_X' => new \Bitrix\Main\Type\DateTime(),
			'SESSION_DATA' => base64_encode($sessionData),
		]);

		return $result->isSuccess();
	}

	protected function lock($sessionId): bool
	{
		return UserSessionTable::lock($this->sessionId);
	}

	protected function unlock($sessionId): bool
	{
		return UserSessionTable::unlock($this->sessionId);
	}

	protected function processDestroy($sessionId): bool
	{
		return UserSessionTable::delete($sessionId)->isSuccess();
	}

	/**
	 * @param int $maxLifeTime
	 * @return int
	 */
	public function gc($maxLifeTime): int
	{
		UserSessionTable::deleteOlderThan($maxLifeTime);

		return 0;
	}

	public function updateTimestamp($sessionId, $sessionData): bool
	{
		$result = UserSessionTable::update($sessionId, [
			'TIMESTAMP_X' => new \Bitrix\Main\Type\DateTime(),
		]);

		return $result->isSuccess();
	}
}
