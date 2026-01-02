<?php

namespace Bitrix\Mail\Grid\MailboxSettingsGrid\Filter;

use Bitrix\Main\Filter\Settings;

class MailboxFilterSettings extends Settings
{
	public const OWNER_FIELD = 'OWNER';
	public const EMAIL_FIELD = 'EMAIL';
	public const LAST_SYNC_FIELD = 'LAST_SYNC';
	public const CRM_INTEGRATION_FIELD = 'CRM_INTEGRATION';
	public const ACCESS_USERS_FIELD = 'ACCESS_USERS';
	public const CRM_QUEUE_FIELD = 'CRM_QUEUE';
	public const DISK_SIZE_FIELD = 'DISK_SIZE';
	public const SENDER_NAME_FIELD = 'SENDER_NAME';

	protected array $filterAvailability = [];
	protected array $whiteList = [];

	public function __construct(array $params)
	{
		parent::__construct($params);
		$this->initFilterAvailability();

		$this->whiteList = isset($params['WHITE_LIST']) && is_array($params['WHITE_LIST'])
			? $params['WHITE_LIST']
			: []
		;
	}

	public function getFilterAvailability(): array
	{
		return $this->filterAvailability;
	}

	public function isFilterAvailable(string $filterField): bool
	{
		return $this->getFilterAvailability()[$filterField] ?? true;
	}

	public function getWhiteList(): array
	{
		return $this->whiteList;
	}

	private function initFilterAvailability(): void
	{
		$this->filterAvailability = [
			self::OWNER_FIELD => true,
			self::EMAIL_FIELD => true,
			self::LAST_SYNC_FIELD => true,
			self::CRM_INTEGRATION_FIELD => true,
			self::ACCESS_USERS_FIELD => true,
			self::CRM_QUEUE_FIELD => true,
			self::DISK_SIZE_FIELD => true,
			self::SENDER_NAME_FIELD => true,
		];
	}
}
