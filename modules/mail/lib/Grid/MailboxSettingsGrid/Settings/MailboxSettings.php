<?php

namespace Bitrix\Mail\Grid\MailboxSettingsGrid\Settings;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Grid\Settings;

class MailboxSettings extends Settings
{
	private const DEFAULT_EXTENSION_NAME = 'Mail.MailboxList';
	private const DEFAULT_EXTENSION_LOAD_NAME = 'mail.grid.mailbox-grid';

	private string $extensionName;
	private string $extensionLoadName;
	private array $viewFields;

	public function __construct(array $params)
	{
		parent::__construct($params);
		$this->initViewFields();

		$this->extensionName = $params['extensionName'] ?? self::DEFAULT_EXTENSION_NAME;
		$this->extensionLoadName = $params['extensionLoadName'] ?? self::DEFAULT_EXTENSION_LOAD_NAME;
	}

	public function getExtensionName(): string
	{
		return $this->extensionName;
	}

	public function getExtensionLoadName(): string
	{
		return $this->extensionLoadName;
	}

	public function getViewFields(): array
	{
		return $this->viewFields;
	}

	private function initViewFields(): void
	{
		$result = [];
		$optionViewFields = Option::get('mail', 'mailbox_list_view_fields', false, SITE_ID);

		if (!empty($optionViewFields))
		{
			$unserializedViewFields = unserialize($optionViewFields, ["allowed_classes" => false]);
			if (
				is_array($unserializedViewFields)
				&& !empty($unserializedViewFields)
			)
			{
				$result = $unserializedViewFields;
			}
		}

		$this->viewFields = !empty($result) ? $result : $this->getMailboxFieldsDefault();
	}

	private function getMailboxFieldsDefault(): array
	{
		return [
			'EMAIL',
			'OWNER', 
			'LAST_SYNC',
			'CRM_INTEGRATION',
			'ACCESS_USERS',
			'CRM_QUEUE',
			'DISK_SIZE',
			'SENDER_NAME',
		];
	}
}
