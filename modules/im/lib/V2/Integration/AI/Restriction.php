<?php

namespace Bitrix\Im\V2\Integration\AI;

use Bitrix\AI\Context;
use Bitrix\AI\Engine;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

class Restriction
{
	public const AI_TEXT_CATEGORY = 'text';
	public const AI_IMAGE_CATEGORY = 'image';
	public const AI_TEXTAREA = 'textarea';
	public const AI_COPILOT_CHAT = 'copilot_chat';

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

	public const AI_TEXT_ERROR = 'AI_TEXT_NOT_AVAILABLE';
	public const AI_IMAGE_ERROR = 'AI_IMAGE_NOT_AVAILABLE';

	private string $type;

	public function __construct(string $type)
	{
		$this->type = $type;
	}

	public function isAvailable(): bool
	{
		if (!Loader::includeModule('ai'))
		{
			return false;
		}

		if (!isset(self::CATEGORIES_BY_TYPE[$this->type]))
		{
			return false;
		}

		$category = self::CATEGORIES_BY_TYPE[$this->type];
		$engine = Engine::getByCategory($category, new Context('im', ''));

		if (is_null($engine))
		{
			return false;
		}

		if (Option::get('im', 'ai_copilot_chat_m1_available', 'N') === 'N')
		{
			return false;
		}

		$copilotSetting = (new \Bitrix\AI\Tuning\Manager())->getItem(self::SETTINGS_BY_TYPE[$this->type]);
		if (!isset($copilotSetting))
		{
			return false;
		}

		return $copilotSetting->getValue();
	}

	public static function onTuningLoad(): \Bitrix\Main\Entity\EventResult
	{
		$result = new \Bitrix\Main\Entity\EventResult;
		$items = [];
		$groups = [];

		if (Option::get('im', 'ai_copilot_chat_m1_available', 'N') === 'N')
		{
			return $result;
		}

		if (\Bitrix\AI\Engine::getByCategory(self::CATEGORIES_BY_TYPE[self::AI_COPILOT_CHAT], \Bitrix\AI\Context::getFake()))
		{
			$groups['im_copilot_chat'] = [
				'title' => Loc::getMessage('IM_RESTRICTION_COPILOT_TITLE'),
				'description' => Loc::getMessage('IM_RESTRICTION_COPILOT_DESCRIPTION'),
				'helpdesk' => '18505482',
			];

			$items[self::SETTING_COPILOT_CHAT] = [
				'group' => 'im_copilot_chat',
				'title' => Loc::getMessage('IM_RESTRICTION_COPILOT_TITLE'),
				'header' => Loc::getMessage('IM_RESTRICTION_COPILOT_HEADER'),
				'type' => \Bitrix\AI\Tuning\Type::BOOLEAN,
				'default' => true,
			];
		}

		$result->modifyFields([
			'items' => $items,
			'groups' => $groups,
		]);

		return $result;
	}
}