<?php

namespace Bitrix\Socialservices\OAuth;

use Bitrix\Main\Application;
use Bitrix\Main\Data\LocalStorage\SessionLocalStorage;
use Bitrix\Main\Web\Json;

final class StateService
{
	private const STORAGE_PREFIX = 'StateService';
	private static self $instance;
	private SessionLocalStorage $storage;

	public function __construct()
	{
		$this->storage = Application::getInstance()->getLocalSession(self::STORAGE_PREFIX);
	}

	private function saveState(string $state, array $payload): void
	{
		$this->storage->set($state, $payload);
	}

	#region public api

	public static function getInstance(): self
	{
		self::$instance ??= new self();

		return self::$instance;
	}

	public function createState(array $payload, bool $appendTimestamp = true): string
	{
		$value = Json::encode($payload);
		if ($appendTimestamp)
		{
			$value .= time();
		}

		$state = hash('sha224', $value);
		$this->saveState($state, $payload);

		return $state;
	}

	public function getPayload(string $state): ?array
	{
		$payload = $this->storage->get($state);
		if (is_array($payload))
		{
			return $payload;
		}

		return null;
	}

	#endregion public api
}
