<?php
namespace Bitrix\Landing\Connector;

use Bitrix\AI\Context;
use Bitrix\AI\Engine;
use Bitrix\AI\Engine\IEngine;
use Bitrix\AI\Tuning;
use Bitrix\AI\Tuning\Type;
use Bitrix\Main\Event;
use Bitrix\Main\Entity;
use Bitrix\Main\EventResult;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

class Ai
{
	private const TUNING_CODE_IMAGE = 'landing_allow_image_generate';
	private const TUNING_CODE_TEXT = 'landing_allow_text_generate';

	/**
	 * Returns true if AI Image creation is available.
	 * @return bool
	 */
	public static function isImageAvailable(): bool
	{
		if (!Loader::includeModule('ai'))
		{
			return false;
		}

		$engine = Engine::getByCategory('image', new Context('landing', ''));
		if (!$engine)
		{
			return false;
		}

		return (new Tuning\Manager())->getItem(self::TUNING_CODE_IMAGE)->getValue();
	}

	/**
	 * Returns true if AI Text creation is available.
	 * @return bool
	 */
	public static function isTextAvailable(): bool
	{
		if (!Loader::includeModule('ai'))
		{
			return false;
		}

		$engine = Engine::getByCategory('text', new Context('landing', ''));
		if (!$engine)
		{
			return false;
		}

		return (new Tuning\Manager())->getItem(self::TUNING_CODE_TEXT)->getValue();
	}

	/**
	 * Returns true if AI Image or Text creation is available.
	 * @return bool
	 */
	public static function isAnyAvailable(): bool
	{
		if (!Loader::includeModule('ai'))
		{
			return false;
		}

		return Engine::getByCategory('text', new Context('landing', ''))
				|| Engine::getByCategory('image', new Context('landing', ''));
	}

	/**
	 * Fills tuning page of AI module.
	 * @return Entity\EventResult
	 */
	public static function onTuningLoad(): Entity\EventResult
	{
		$result = new Entity\EventResult;
		$items = [];

		if (Engine::getByCategory('image', new Context('landing', '')))
		{
			$items[self::TUNING_CODE_IMAGE] = [
				'header' => 'ImageAssistant AI',
				'title' => Loc::getMessage('LANDING_CONNECTOR_AI_ALLOW_IMAGE_GENERATE'),
				'type' => Type::BOOLEAN,
				'default' => true,
			];
		}

		if (Engine::getByCategory('text', new Context('landing', '')))
		{
			$items[self::TUNING_CODE_TEXT] = [
				'header' => 'TextAssistant AI',
				'title' => Loc::getMessage('LANDING_CONNECTOR_AI_ALLOW_TEXT_GENERATE'),
				'type' => Type::BOOLEAN,
				'default' => true,
			];
		}

		$result->modifyFields([
			'items' => $items,
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
