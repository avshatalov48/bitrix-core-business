<?php

namespace Bitrix\Im\V2\Integration\AI;

use Bitrix\AI\Engine;
use Bitrix\AI\Facade\Bitrix24;
use Bitrix\AI\Quality;
use Bitrix\AI\Tuning\Defaults;
use Bitrix\AI\Tuning\Manager;
use Bitrix\AI\Tuning\Type;
use Bitrix\Main\Application;
use Bitrix\Main\Entity\EventResult;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

class Restriction
{
	public const AI_TEXT_CATEGORY = 'text';
	public const AI_IMAGE_CATEGORY = 'image';
	public const AI_TEXTAREA = 'textarea';
	public const AI_COPILOT_CHAT = 'copilot_chat';
	public const SETTING_COPILOT_CHAT_PROVIDER = 'im_chat_answer_provider';
	public const AI_TEXT_ERROR = 'AI_TEXT_NOT_ACTIVE';
	public const AI_AVAILABLE_ERROR = 'AI_NOT_AVAILABLE';
	public const AI_IMAGE_ERROR = 'AI_IMAGE_NOT_ACTIVE';

	private const CATEGORIES_BY_TYPE = [
		self::AI_TEXTAREA => self::AI_TEXT_CATEGORY,
		self::AI_COPILOT_CHAT => self::AI_TEXT_CATEGORY,
	];
	private const SETTING_COPILOT_CHAT = 'im_allow_chat_answer_generate';
	private const SETTING_TEXTAREA_CHAT = 'im_allow_chat_textarea_generate';
	private const SETTINGS_BY_TYPE = [
		self::AI_COPILOT_CHAT => self::SETTING_COPILOT_CHAT,
		self::AI_TEXTAREA => self::SETTING_TEXTAREA_CHAT,
	];
	private const PORTAL_ZONE_BLACKLIST = [
		'cn',
	];

	private static array $activeListByType = [];
	private static ?bool $isAvailable = null;
	private string $type;

	public function __construct(string $type)
	{
		$this->type = $type;
	}

	public function isActive(): bool
	{
		self::$activeListByType[$this->type] ??= $this->isActiveInternal();

		return self::$activeListByType[$this->type];
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

		if (!empty(Engine::getListAvailable(self::CATEGORIES_BY_TYPE[self::AI_COPILOT_CHAT])))
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
				'default' => true,
			];

			$items[self::SETTING_COPILOT_CHAT_PROVIDER] = array_merge(
				[
					'group' => 'im_copilot_chat',
					'title' => Loc::getMessage('IM_RESTRICTION_COPILOT_PROVIDER_TITLE'),
				],
				Defaults::getProviderSelectFieldParams(self::CATEGORIES_BY_TYPE[self::AI_COPILOT_CHAT])
			);
		}

		$result->modifyFields([
			'items' => $items,
			'groups' => $groups,
			'itemRelations' => [
				'im_copilot_chat' => [
					self::SETTING_COPILOT_CHAT => [
						self::SETTING_COPILOT_CHAT_PROVIDER,
					],
				],
			],
		]);

		return $result;
	}

	private function isActiveInternal(): bool
	{
		if (
			!Loader::includeModule('ai')
			|| !$this->isAvailable()
			|| !isset(self::CATEGORIES_BY_TYPE[$this->type])
		)
		{
			return false;
		}

		$category = self::CATEGORIES_BY_TYPE[$this->type];
		$engine = Engine::getListAvailable($category);
		if (empty($engine))
		{
			return false;
		}

		$copilotSetting = (new Manager())->getItem(self::SETTINGS_BY_TYPE[$this->type]);
		if (!isset($copilotSetting))
		{
			return false;
		}

		return $copilotSetting->getValue();
	}

	private function isAvailableInternal(): bool
	{
		// todo: need to support changes
		$portalZone = Application::getInstance()->getLicense()->getRegion() ?? 'ru';

		return !in_array($portalZone, self::PORTAL_ZONE_BLACKLIST, true);
	}
}
