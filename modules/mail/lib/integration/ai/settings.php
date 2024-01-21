<?php

namespace Bitrix\Mail\Integration\AI;

use Bitrix\Mail\Integration\AI\EventHandler;
use Bitrix\AI;
use Bitrix\Main\Loader;

final class Settings
{
	private const SETTINGS_ITEM_MAIL_CODE = EventHandler::SETTINGS_ITEM_MAIL_CODE;
	private const SETTINGS_ITEM_MAIL_CRM_CODE = EventHandler::SETTINGS_ITEM_MAIL_CRM_CODE;
	public const MAIL_NEW_MESSAGE_CONTEXT_ID = 'mail_new_message';
	public const MAIL_REPLY_MESSAGE_CONTEXT_ID = 'mail_reply_message';
	public const MAIL_CRM_NEW_MESSAGE_CONTEXT_ID = 'crm_mail_new_message';
	public const MAIL_CRM_REPLY_MESSAGE_CONTEXT_ID = 'crm_mail_reply_message';

	public static function instance(): self
	{
		return new self();
	}

	public function isMailCopilotEnabledInGlobalSettings(): bool
	{
		if (!Loader::includeModule('ai')
			|| !$this->isAITurningManagerAvailable()
		)
		{
			return false;
		}

		$manager = new AI\Tuning\Manager();
		$item = $manager->getItem(self::SETTINGS_ITEM_MAIL_CODE);

		return (bool)$item?->getValue();
	}

	public function isMailCrmCopilotEnabledInGlobalSettings(): bool
	{
		if (!Loader::includeModule('ai')
			|| !Loader::includeModule('crm')
			|| !$this->isAITurningManagerAvailable()
		)
		{
			return false;
		}

		$manager = new AI\Tuning\Manager();
		$item = $manager->getItem(self::SETTINGS_ITEM_MAIL_CRM_CODE);

		return (bool)$item?->getValue();
	}

	public function getMailCopilotParams(string $contextId, ?array $contextParams = null): array
	{
		if (!$this->isMailCopilotEnabledInGlobalSettings())
		{
			return ['isCopilotEnabled' => false];
		}

		return $this->prepareMailCopilotParams(
			$this->isMailCopilotEnabledInGlobalSettings(),
			$contextId,
			'mail',
			$contextParams,
		);
	}

	public function getMailCrmCopilotParams(string $contextId, ?array $contextParams = null): array
	{
		if (!$this->isMailCrmCopilotEnabledInGlobalSettings())
		{
			return ['isCopilotEnabled' => false];
		}

		return $this->prepareMailCopilotParams(
			$this->isMailCrmCopilotEnabledInGlobalSettings(),
			$contextId,
			'mail_crm',
			$contextParams,
		);
	}

	private function prepareMailCopilotParams(bool $isCopilotEnabled, string $contextId, string $category, ?array $contextParams = null): array
	{
		return [
			'isCopilotEnabled' => $isCopilotEnabled,
			'isCopilotTextEnabled' => $isCopilotEnabled,
			'moduleId' => 'mail',
			'contextId' => $contextId,
			'category' => $category,
			'invitationLineMode' => 'eachLine',
			'contextParameters' => $contextParams ?? [],
		];
	}

	private function isAITurningManagerAvailable(): bool
	{
		return Loader::includeModule('ai') && class_exists('\Bitrix\AI\Tuning\Manager');
	}
}