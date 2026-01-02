<?php

namespace Bitrix\Im\V2\Integration\AI;

use Bitrix\AI\Context;
use Bitrix\AI\Engine;
use Bitrix\AI\Quality;
use Bitrix\AI\Tuning\Manager;
use Bitrix\Im\V2\Analytics\CopilotAnalytics;
use Bitrix\Im\V2\Chat;
use Bitrix\Im\V2\Pull\Event\ChangeEngine;
use Bitrix\Im\V2\Result;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

class EngineManager
{
	/**
	 * @see \Bitrix\AI\Engine::CATEGORIES
	 */
	protected const CATEGORY = 'text';

	/**
	 * @see \Bitrix\AI\Quality::QUALITIES
	 */
	protected const QUALITY = 'chat_talk';

	protected static ?string $defaultEngineCode = null;

	/**
	 * @var null|Engine\IEngine[]
	 */
	protected static ?array $availableEngines = null;

	protected function getAvailableEngineCodes(): array
	{
		static $availableEngineCodes = null;

		if (!isset($availableEngineCodes))
		{
			$codes = [];

			foreach ($this->getAvailableEngines() as $engine)
			{
				$codes[] = $engine->getCode();
			}

			$availableEngineCodes = $codes;
		}

		return $availableEngineCodes;
	}

	/**
	 * @return Engine\IEngine[]
	 */
	protected function getAvailableEngines(): array
	{
		if (!self::isAvailable())
		{
			return [];
		}

		if (!isset(self::$availableEngines))
		{
			self::$availableEngines = Engine::getListAvailable(self::CATEGORY);
		}

		return self::$availableEngines;
	}

	public function getAvailableEnginesForRest(): array
	{
		$result = [];
		$engines = $this->getAvailableEngines();

		foreach ($engines as $engine)
		{
			$result[] = [
				'code' => $engine->getCode(),
				'name' => $engine->getName(),
				'recommended' => $engine->isPreferredForQuality(new Quality(self::QUALITY)),
			];
		}

		return $result;
	}

	public function updateEngine(Chat $chat, string $engineCode): Result
	{
		$result = new Result();

		$error = match (true)
		{
			!self::isAvailable() => new CopilotError(CopilotError::AI_NOT_INSTALLED),
			!$chat instanceof Chat\CopilotChat => new CopilotError(CopilotError::WRONG_CHAT_TYPE),
			!$this->validateEngineCode($engineCode) => new CopilotError(CopilotError::ENGINE_NOT_FOUND),
			$chat->getEngineCode() === $engineCode => new CopilotError(CopilotError::IDENTICAL_ENGINE),
			default => null
		};

		if (isset($error))
		{
			return $result->addError($error);
		}

		$oldEngineName = $this->getEngineNameByCode($chat->getEngineCode());
		/**
		 * @var Chat\CopilotChat $chat
		 */
		$result = $chat->setEngineCode($engineCode)->save();

		if ($result->isSuccess())
		{
			$this->setLastSelectedEngineCode($engineCode, $chat->getContext()->getUserId());
			$this->sendUpdateEngineMessage($chat, $engineCode);
			(new CopilotAnalytics($chat))->addChangeEngine($oldEngineName ?? '');

			$engineName = $this->getEngineNameByCode($engineCode) ?? '';
			(new ChangeEngine($chat, $engineCode, $engineName))
				->setContext($chat->getContext())
				->send()
			;
		}

		return $result;
	}

	protected function sendUpdateEngineMessage(Chat $chat, string $engineCode): void
	{
		$engineName = $this->getEngineNameByCode($engineCode);
		$user = $chat->getContext()->getUser();
		$genderSuffix = $user->getGender() === 'F' ? '_F' : '';
		$userId = $user->getId();

		$message = Loc::getMessage(
			'IM_COPILOT_UPDATE_ENGINE' . $genderSuffix,
			['#USER_ID#' => $userId, '#ENGINE#' => $engineName]
		) ?? '';

		$messageFields = [
			'FROM_USER_ID' => $userId,
			'MESSAGE_TYPE' => $chat->getType(),
			'TO_CHAT_ID' => $chat->getChatId(),
			'MESSAGE' => $message,
			'SYSTEM' => 'Y',
			'PUSH' => 'N'
		];

		\CIMMessage::Add($messageFields);
	}

	public static function getDefaultEngineCode(): ?string
	{
		if (!self::isAvailable())
		{
			return null;
		}

		if (!isset(self::$defaultEngineCode))
		{
			self::$defaultEngineCode =
				(string)(new Manager())
					->getItem(Restriction::SETTING_COPILOT_CHAT_PROVIDER)
					?->getValue()
			;
		}

		return !empty(self::$defaultEngineCode) ? self::$defaultEngineCode : null;
	}

	public static function getDefaultEngineName(): ?string
	{
		if (!self::isAvailable())
		{
			return null;
		}

		return self::getDefaultEngine()?->getIEngine()->getName();
	}

	public static function getDefaultEngine(?Context $context = null): ?Engine
	{
		if (!self::isAvailable())
		{
			return null;
		}

		$engineCode = self::getDefaultEngineCode();
		$context ??= Context::getFake();

		if (!isset($engineCode))
		{
			return null;
		}


		return Engine::getByCode($engineCode, $context, self::CATEGORY);
	}

	public function validateEngineCode(?string $code): bool
	{
		return in_array($code, $this->getAvailableEngineCodes(), true);
	}

	public function getEngineByCode(?string $code, ?Context $context = null): ?Engine
	{
		if (!isset($code) || !self::isAvailable())
		{
			return null;
		}

		$context ??= Context::getFake();

		return Engine::getByCode($code, $context, self::CATEGORY);
	}

	/**
	 * @param string[] $codes
	 * @param Context|null $context
	 * @return Engine[]
	 */
	public function getEnginesByCodes(array $codes, ?Context $context = null): array
	{
		$result = [];

		foreach ($codes as $code)
		{
			$engine = $this->getEngineByCode($code, $context);
			if (isset($engine))
			{
				$result[] = $engine;
			}
		}

		return $result;
	}

	public function getEngineNameByCode(?string $code, ?Context $context = null): ?string
	{
		if (!isset($code) || !self::isAvailable())
		{
			return null;
		}

		$engine = $this->getEngineByCode($code, $context);

		return $engine?->getIEngine()->getName();
	}

	public function getLastSelectedEngineCode(int $userId): ?string
	{
		$code = null;
		$codeFromOption = \CUserOptions::GetOption(
			'im',
			'lastSelectedEngineCode',
			false,
			$userId
		);

		if (is_string($codeFromOption))
		{
			$code = $codeFromOption;
		}

		return $code;
	}

	protected function setLastSelectedEngineCode(string $code, int $userId): self
	{
		if ($this->validateEngineCode($code))
		{
			\CUserOptions::SetOption('im', 'lastSelectedEngineCode', $code, false, $userId);
		}

		return $this;
	}

	protected static function isAvailable(): bool
	{
		return Loader::includeModule('ai');
	}
}