<?php

namespace Bitrix\Im\V2\Integration\AI;

use Bitrix\AI\Engine;
use Bitrix\AI\Quality;
use Bitrix\AI\Tuning\Defaults;
use Bitrix\AI\Tuning\Manager;
use Bitrix\AI\Tuning\Type;
use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\EventResult;

class Restriction
{
	public const SETTING_COPILOT_CHAT_PROVIDER = 'im_chat_answer_provider';
	public const SETTING_TRANSCRIPTION_PROVIDER = 'im_file_transcription_provider';

	private const AI_TEXT_CATEGORY = 'text'; /** @see Engine::CATEGORIES */
	private const AI_AUDIO_CATEGORY = 'audio'; /** @see Engine::CATEGORIES */
	private const DEFAULT_COPILOT_ENABLED = true;
	private const DEFAULT_TRANSCRIPTION_ENABLED = true;
	private const SETTING_COPILOT_CHAT = 'im_allow_chat_answer_generate';
	private const SETTING_TRANSCRIPTION = 'im_allow_file_transcription_generate';
	private const PORTAL_ZONE_BLACKLIST = ['cn'];
	private const TRANSCRIPTION_QUALITY = 'transcribe_chat_voice_messages'; /** @see Quality::QUALITIES */

	private static ?bool $isCopilotActive = null;
	private static ?bool $isTranscriptionActive = null;
	private static ?bool $isAvailable = null;

	public function isCopilotActive(): bool
	{
		self::$isCopilotActive ??= $this->isActiveInternal(self::AI_TEXT_CATEGORY);

		return self::$isCopilotActive;
	}

	public function isTranscriptionActive(): bool
	{
		self::$isTranscriptionActive ??= $this->isActiveInternal(self::AI_AUDIO_CATEGORY);

		return self::$isTranscriptionActive;
	}

	public function isAvailable(): bool
	{
		self::$isAvailable ??= $this->isAvailableInternal();

		return self::$isAvailable;
	}

	public static function onTuningLoad(): EventResult
	{
		$result = new EventResult;
		$items = [];
		$groups = [];
		$itemRelations = [];

		if (!empty(Engine::getListAvailable(self::AI_TEXT_CATEGORY)))
		{
			$groups['im_copilot_chat'] = [
				'title' => Loc::getMessage('IM_RESTRICTION_COPILOT_GROUP_MSGVER_1'),
				'description' => Loc::getMessage('IM_RESTRICTION_COPILOT_DESCRIPTION'),
				'helpdesk' => '18505482',
			];

			$items[self::SETTING_COPILOT_CHAT] = [
				'group' => 'im_copilot_chat',
				'title' => Loc::getMessage('IM_RESTRICTION_COPILOT_TITLE'),
				'header' => Loc::getMessage('IM_RESTRICTION_COPILOT_HEADER'),
				'type' => Type::BOOLEAN,
				'default' => self::DEFAULT_COPILOT_ENABLED,
				'sort' => 100,
			];

			$items[self::SETTING_COPILOT_CHAT_PROVIDER] = array_merge(
				[
					'group' => 'im_copilot_chat',
					'title' => Loc::getMessage('IM_RESTRICTION_COPILOT_PROVIDER_TITLE'),
					'sort' => 120,
				],
				Defaults::getProviderSelectFieldParams(self::AI_TEXT_CATEGORY)
			);

			$itemRelations[self::SETTING_COPILOT_CHAT] = [self::SETTING_COPILOT_CHAT_PROVIDER];

			if (Option::get('im', 'file_transcription_available', 'N') === 'Y')
			{
				$items[self::SETTING_TRANSCRIPTION] = [
					'group' => 'im_copilot_chat',
					'title' => Loc::getMessage('IM_RESTRICTION_TRANSCRIPTION_TITLE'),
					'header' => Loc::getMessage('IM_RESTRICTION_TRANSCRIPTION_HEADER'),
					'type' => Type::BOOLEAN,
					'default' => self::DEFAULT_TRANSCRIPTION_ENABLED,
				];

				$items[self::SETTING_TRANSCRIPTION_PROVIDER] = array_merge(
					[
						'group' => 'im_copilot_chat',
						'title' => Loc::getMessage('IM_RESTRICTION_TRANSCRIPTION_PROVIDER_TITLE'),
					],
					Defaults::getProviderSelectFieldParams(
						self::AI_AUDIO_CATEGORY,
						new Quality(self::TRANSCRIPTION_QUALITY)
					)
				);

				$itemRelations[self::SETTING_TRANSCRIPTION] = [self::SETTING_TRANSCRIPTION_PROVIDER];
			}
		}

		$result->modifyFields([
			'items' => $items,
			'groups' => $groups,
			'itemRelations' => [
				'im_copilot_chat' => $itemRelations,
			],
		]);

		return $result;
	}

	private function isActiveInternal(string $aiCategory): bool
	{
		if (
			!Loader::includeModule('ai')
			|| !AIHelper::getCopilotBotId()
			|| !$this->isAvailable()
		)
		{
			return false;
		}

		$engine = Engine::getListAvailable($aiCategory);
		if (empty($engine))
		{
			return false;
		}

		return match ($aiCategory)
		{
			self::AI_TEXT_CATEGORY => $this->isCopilotOptionEnabled(),
			self::AI_AUDIO_CATEGORY => $this->isTranscriptionOptionEnabled(),
		};
	}

	private function isCopilotOptionEnabled(): bool
	{
		$settings = Manager::getTuningStorage();

		return (bool)($settings[self::SETTING_COPILOT_CHAT] ?? self::DEFAULT_COPILOT_ENABLED);
	}

	private function isTranscriptionOptionEnabled(): bool
	{
		$settings = Manager::getTuningStorage();

		return (bool)($settings[self::SETTING_TRANSCRIPTION] ?? self::DEFAULT_TRANSCRIPTION_ENABLED);
	}

	private function isAvailableInternal(): bool
	{
		// todo: need to support changes
		$portalZone = Application::getInstance()->getLicense()->getRegion() ?? 'ru';

		return !in_array($portalZone, self::PORTAL_ZONE_BLACKLIST, true);
	}
}
