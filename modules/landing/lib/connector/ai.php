<?php
namespace Bitrix\Landing\Connector;

use Bitrix\AI\Context;
use Bitrix\AI\Engine;
use Bitrix\AI\Engine\IEngine;
use Bitrix\AI\Tuning;
use Bitrix\AI\Tuning\Type;
use Bitrix\Landing\Manager;
use Bitrix\Main\Event;
use Bitrix\Main\Entity;
use Bitrix\Main\EventResult;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

class Ai
{
	private const TUNING_CODE_IMAGE = 'landing_allow_image_generate';
	private const TUNING_CODE_TEXT = 'landing_allow_text_generate';
	private const NOT_ALLOWED_ZONES_FOR_IMAGE = [];
	private const NOT_ALLOWED_ZONES_FOR_TEXT = ['cn'];

	/**
	 * Returns true if AI Image service is can be used. Not check activity for landing
	 * @return bool
	 */
	public static function isImageAvailable(): bool
	{
		if (!Loader::includeModule('ai'))
		{
			return false;
		}

		$engine = Engine::getByCategory('image', Context::getFake());
		if (!$engine)
		{
			return false;
		}

		if (in_array(Manager::getZone(), self::NOT_ALLOWED_ZONES_FOR_IMAGE))
		{
			return false;
		}

 		return true;
	}

	/**
	 * Returns true if AI Image service is available and activated for landing
	 * @return bool
	 */
	public static function isImageActive(): bool
	{
		if (!self::isImageAvailable())
		{
			return false;
		}

		$default = false;
		$setting = (new Tuning\Manager())->getItem(self::TUNING_CODE_IMAGE);

		return $setting ? (bool)$setting->getValue() : $default;
	}

	/**
	 * Returns true if AI Text service is can be used. Not check activity for landing
	 * @return bool
	 */
	public static function isTextAvailable(): bool
	{
		if (!Loader::includeModule('ai'))
		{
			return false;
		}

		$engine = Engine::getByCategory('text', Context::getFake());
		if (!$engine)
		{
			return false;
		}

		if (in_array(Manager::getZone(), self::NOT_ALLOWED_ZONES_FOR_TEXT))
		{
			return false;
		}

		return true;
	}

	/**
	 * Returns true if AI Text service is can be used. And option is ON.
	 * @return bool
	 */
	public static function isCopilotAvailable(): bool
	{
		if (!self::isTextAvailable())
		{
			return false;
		}

		return true;
	}

	/**
	 * Returns true if AI Text service is available and activated for landing
	 * @return bool
	 */
	public static function isTextActive(): bool
	{
		if (!self::isTextAvailable())
		{
			return false;
		}

		$default = false;
		$setting = (new Tuning\Manager())->getItem(self::TUNING_CODE_TEXT);

		return $setting ? (bool)$setting->getValue() : $default;
	}

	/**
	 * Fills tuning page of AI module.
	 * @return Entity\EventResult
	 */
	public static function onTuningLoad(): Entity\EventResult
	{
		$result = new Entity\EventResult;
		$items = [];
		$groups = [];

		if (Engine::getByCategory('image', Context::getFake()))
		{
			$items[self::TUNING_CODE_IMAGE] = [
				'group' => Tuning\Defaults::GROUP_IMAGE,
				'header' => Loc::getMessage('LANDING_CONNECTOR_AI_ALLOW_IMAGE_COPILOT_DESC'),
				'title' => Loc::getMessage('LANDING_CONNECTOR_AI_ALLOW_COPILOT_TITLE'),
				'type' => Type::BOOLEAN,
				'default' => true,
				'sort' => 300,
			];
		}

		if (Engine::getByCategory('text', Context::getFake()))
		{
			$items[self::TUNING_CODE_TEXT] = [
				'group' => Tuning\Defaults::GROUP_TEXT,
				'header' => Loc::getMessage('LANDING_CONNECTOR_AI_ALLOW_TEXT_COPILOT_DESC'),
				'title' => Loc::getMessage('LANDING_CONNECTOR_AI_ALLOW_COPILOT_TITLE'),
				'type' => Type::BOOLEAN,
				'default' => true,
				'sort' => 300,
			];
		}

		$result->modifyFields([
			'items' => $items,
			'groups' => $groups,
		]);

		return $result;
	}

	/**
	 * Checks whether engine is off or not.
	 * @see onTuningLoad
	 * @param Event $event Event instance.
	 * @return EventResult
	 */
	public static function onBeforeCompletions(Event $event): EventResult
	{
		/** @var IEngine $engine */
		$engine = $event->getParameter('engine');
		$category = $engine->getCategory();
		$module = $engine->getContext()->getModuleId();

		$config = new Tuning\Manager();
		$configItem = $config->getItem("{$module}_allow_{$category}_generate");

		if ($configItem && $configItem->getValue())
		{
			return new EventResult(EventResult::SUCCESS);
		}
		else
		{
			return new EventResult(EventResult::ERROR);
		}
	}
}
