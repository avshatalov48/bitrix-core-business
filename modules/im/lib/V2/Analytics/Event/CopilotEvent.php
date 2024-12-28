<?php

namespace Bitrix\Im\V2\Analytics\Event;

use Bitrix\AI\Engine;
use Bitrix\Im\V2\Integration\AI\AIHelper;
use Bitrix\Im\V2\Integration\AI\RoleManager;
use Bitrix\Imbot\Bot\CopilotChatBot;
use Bitrix\Main\Result;

class CopilotEvent extends Event
{
	protected const ANALYTICS_STATUS = [
		'SUCCESS' => 'success',
		'ERROR_PROVIDER' => 'error_provider',
		'ERROR_B24' => 'error_b24',
		'ERROR_LIMIT_DAILY' => 'error_limit_daily',
		'ERROR_LIMIT_MONTHLY' => 'error_limit_monthly',
		'ERROR_AGREEMENT' => 'error_agreement',
		'ERROR_TURNEDOFF' => 'error_turnedoff',
		'ERROR_LIMIT_BAAS' => 'error_limit_baas',
	];

	protected function getTool(): string
	{
		return 'ai';
	}

	protected function getCategory(string $eventName): string
	{
		return 'chat_operations';
	}

	protected function setDefaultParams(?Engine $engine = null, ?string $promptCode = null): self
	{
		$this
			->setSection('copilot_tab')
			->setCopilotP2()
			->setCopilotP3()
			->setCopilotP4()
			->setCopilotP5()
		;

		return $this;
	}

	public function setCopilotStatus(Result $result): self
	{
		if ($result->isSuccess())
		{
			$this->status = self::ANALYTICS_STATUS['SUCCESS'];

			return $this;
		}

		$error = $result->getErrors()[0];

		if (!isset($error))
		{
			$this->status = self::ANALYTICS_STATUS['ERROR_B24'];

			return $this;
		}

		$this->status = match ($error->getCode()) {
			CopilotChatBot::AI_ENGINE_ERROR_PROVIDER => self::ANALYTICS_STATUS['ERROR_PROVIDER'],
			CopilotChatBot::LIMIT_IS_EXCEEDED_DAILY => self::ANALYTICS_STATUS['ERROR_LIMIT_DAILY'],
			CopilotChatBot::LIMIT_IS_EXCEEDED_MONTHLY => self::ANALYTICS_STATUS['ERROR_LIMIT_MONTHLY'],
			CopilotChatBot::ERROR_AGREEMENT => self::ANALYTICS_STATUS['ERROR_AGREEMENT'],
			CopilotChatBot::LIMIT_IS_EXCEEDED_BAAS => self::ANALYTICS_STATUS['ERROR_LIMIT_BAAS'],
			default => self::ANALYTICS_STATUS['ERROR_B24'],
		};

		return $this;
	}

	public function setCopilotP1(?string $promptCode): self
	{
		$this->p1 = isset($promptCode) ? ('1st-type_' . $this->convertUnderscore($promptCode)) : 'none';

		return $this;
	}

	protected function setCopilotP2(): self
	{
		$this->p2 = 'provider_' . (AIHelper::getProviderName() ?? 'none');

		return $this;
	}

	protected function setCopilotP3(): self
	{
		$this->p3 = $this->chat->getUserCount() > 2 ? 'chatType_multiuser' : 'chatType_private';

		return $this;
	}

	protected function setCopilotP4(): self
	{
		$role = (new RoleManager())->getMainRole($this->chat->getChatId()) ?? RoleManager::getDefaultRoleCode();
		$this->p4 = 'role_' . $this->convertUnderscore($role);

		return $this;
	}

	protected function setCopilotP5(): self
	{
		$this->p5 = 'chatId_' . $this->chat->getChatId();

		return $this;
	}
}
