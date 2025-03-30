<?php

namespace Bitrix\Main\UserField\File;

use Bitrix\Main\Application;

class UploadedFilesRegistry
{
	private const SESSION_CODE_PREFIX = 'UploadedFilesRegistry_';
	private static ?self $instance = null;

	public static function getInstance(): self
	{
		self::$instance ??= new self();

		return self::$instance;
	}

	public function registerFile(int $fileId, string $controlId, string $cid, string $tempFileToken): void
	{
		$session = Application::getInstance()->getSession();
		if (!isset($session[self::SESSION_CODE_PREFIX . $controlId]))
		{
			$session[self::SESSION_CODE_PREFIX . $controlId] = [];
		}

		$session[self::SESSION_CODE_PREFIX . $controlId][$fileId] = [
			'cid' => $cid,
			'token' => $tempFileToken,
		];
	}

	public function getTokenByFileId(string $controlId, int $fileId): ?string
	{
		$session = Application::getInstance()->getSession();

		return $session[self::SESSION_CODE_PREFIX . $controlId][$fileId]['token'] ?? null;
	}

	public function getCidByFileId(string $controlId, int $fileId): ?string
	{
		$session = Application::getInstance()->getSession();

		return $session[self::SESSION_CODE_PREFIX . $controlId][$fileId]['cid'] ?? null;
	}

	public function unregisterFile(string $controlId, int $fileId): void
	{
		$session = Application::getInstance()->getSession();
		if (isset($session[self::SESSION_CODE_PREFIX . $controlId][$fileId]))
		{
			unset($session[self::SESSION_CODE_PREFIX . $controlId][$fileId]);
		}
		if (empty($session[self::SESSION_CODE_PREFIX . $controlId]))
		{
			unset($session[self::SESSION_CODE_PREFIX . $controlId]);
		}
	}
}
