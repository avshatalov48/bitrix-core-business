<?php

declare(strict_types=1);

namespace Bitrix\Im\V2\Analytics;

use Bitrix\AI\Context;
use Bitrix\AI\Engine;
use Bitrix\AI\Tuning\Manager;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Integration\AI\Restriction;
use Bitrix\Im\V2\Integration\AI\RoleManager;
use Bitrix\Imbot\Bot\CopilotChatBot;
use Bitrix\Main\Analytics\AnalyticsEvent;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Engine\Response\Converter;
use Bitrix\Main\Result;

class CopilotAnalytics extends AbstractAnalytics
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

	public static function getCopilotStatusByResult(Result $result): string
	{
		if ($result->isSuccess())
		{
			return self::ANALYTICS_STATUS['SUCCESS'];
		}

		$error = $result->getErrors()[0];

		if (!isset($error))
		{
			return self::ANALYTICS_STATUS['ERROR_B24'];
		}

		return match ($error->getCode()) {
			CopilotChatBot::AI_ENGINE_ERROR_PROVIDER => self::ANALYTICS_STATUS['ERROR_PROVIDER'],
			CopilotChatBot::LIMIT_IS_EXCEEDED_DAILY => self::ANALYTICS_STATUS['ERROR_LIMIT_DAILY'],
			CopilotChatBot::LIMIT_IS_EXCEEDED_MONTHLY => self::ANALYTICS_STATUS['ERROR_LIMIT_MONTHLY'],
			CopilotChatBot::ERROR_AGREEMENT => self::ANALYTICS_STATUS['ERROR_AGREEMENT'],
			CopilotChatBot::LIMIT_IS_EXCEEDED_BAAS => self::ANALYTICS_STATUS['ERROR_LIMIT_BAAS'],
			default => self::ANALYTICS_STATUS['ERROR_B24'],
		};
	}

	protected function convertUnderscoreForAnalytics(string $string): string
	{
		return (new Converter(Converter::TO_CAMEL | Converter::LC_FIRST))->process($string);
	}

	protected function getTool(): string
	{
		return 'ai';
	}

	/**
	 * @throws ArgumentException
	 */
	protected function createCopilotEvent(
		string $eventName,
		Chat $chat,
		?Engine $engine,
		?string $promptCode = null,
	): AnalyticsEvent
	{
		$chatId = $chat->getChatId();
		$event = $this
			->createEvent($eventName, 'chat_operations')
			->setSection('copilot_tab')
			->setP1((null === $promptCode) ? 'none' : ('1st-type_' . $this->convertUnderscoreForAnalytics($promptCode)))
			->setP2('provider_' . (isset($engine) ? $engine->getIEngine()->getName() : 'none'))
			->setP3(($chat->getUserCount() > 2) ? 'chatType_multiuser' : 'chatType_private')
			->setP4('role_' . $this->convertUnderscoreForAnalytics(
				(new RoleManager())->getMainRole($chatId) ?? RoleManager::getDefaultRoleCode()
				)
			)
		;

		if (null !== $chatId)
		{
			$event->setP5('chatId_' . $chatId);
		}

		return $event;
	}

	protected function isCopilot(Chat $chat): bool
	{
		return $chat->getType() === Chat::IM_TYPE_COPILOT;
	}

	protected function getEngine(): ?Engine
	{
		$context = new Context(
			CopilotChatBot::CONTEXT_MODULE,
			CopilotChatBot::CONTEXT_SUMMARY,
		);
		$engineItem = (new Manager())->getItem( Restriction::SETTING_COPILOT_CHAT_PROVIDER);

		return Engine::getByCode(
			isset($engineItem) ? $engineItem->getValue() : '',
			$context,
			Engine::CATEGORIES['text'],
		);
	}

	public function addGenerate(
		Chat $chat,
		Result $result,
		?Engine $engine = null,
		?string $promptCode = null,
	): void
	{
		if (!$this->isCopilot($chat))
		{
			return;
		}

		$status = self::getCopilotStatusByResult($result);
		$params = [$chat, $engine, $promptCode];
		$this->async(function () use ($params, $status)
		{
			try
			{
				$this
					->createCopilotEvent('generate',...$params)
					->send()
				;
				$this
					->createCopilotEvent('received_result',...$params)
					->setStatus($status)
					->send()
				;
			}
			catch (ArgumentException $e)
			{
				$this->logException($e);
			}
		});
	}

	public function addAddUser(Chat $chat): void
	{
		if (!$this->isCopilot($chat))
		{
			return;
		}

		try
		{
			$this
				->createCopilotEvent('add_user', $chat, $this->getEngine())
				->send()
			;
		}
		catch (ArgumentException $e)
		{
			$this->logException($e);
		}
	}

	public function addDeleteUser(Chat $chat): void
	{
		if (!$this->isCopilot($chat))
		{
			return;
		}

		try
		{
			$this
				->createCopilotEvent('delete_user', $chat, $this->getEngine())
				->send()
			;
		}
		catch (ArgumentException $e)
		{
			$this->logException($e);
		}
	}
}