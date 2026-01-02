<?php

namespace Bitrix\Landing\Connector;

use Bitrix\AI\Context;
use Bitrix\AI\Engine;
use Bitrix\AI\Engine\IEngine;
use Bitrix\AI\Quality;
use Bitrix\AI\Tuning;
use Bitrix\Landing\Manager;
use Bitrix\Landing\Copilot;
use Bitrix\Main\Event;
use Bitrix\Main\Entity;
use Bitrix\Main\EventResult;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\SystemException;

class Ai
{
	public const TUNING_CODE_GROUP = 'landing';

	private const TUNING_CODE_ALLOW_COPILOT = 'landing_allow_copilot';
	private const TUNING_CODE_IMAGE_PROVIDER = 'landing_image_provider';
	private const TUNING_CODE_TEXT_PROVIDER = 'landing_text_provider';

	public const TUNING_CODE_ALLOW_SITE_COPILOT = 'landing_allow_site';
	public const TUNING_CODE_SITE_IMAGE_PROVIDER = 'landing_site_image_provider';
	public const TUNING_CODE_SITE_TEXT_PROVIDER = 'landing_site_text_provider';

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

		if (in_array(Manager::getZone(), self::NOT_ALLOWED_ZONES_FOR_IMAGE, true))
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
		$setting = (new Tuning\Manager())->getItem(self::TUNING_CODE_ALLOW_COPILOT);

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

		if (in_array(Manager::getZone(), self::NOT_ALLOWED_ZONES_FOR_TEXT, true))
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
		$setting = (new Tuning\Manager())->getItem(self::TUNING_CODE_ALLOW_COPILOT);

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
		$relations = [];

		$groups[self::TUNING_CODE_GROUP] = [
			'title' => Loc::getMessage('LANDING_CONNECTOR_AI_GROUP_TITLE'),
			'description' => Loc::getMessage('LANDING_CONNECTOR_AI_GROUP_DESC'),
			'helpdesk' => 24409174,
		];

		// region ai site
		if (Copilot\Manager::isAvailable())
		{
			$items[self::TUNING_CODE_ALLOW_SITE_COPILOT] = [
				'group' => self::TUNING_CODE_GROUP,
				'title' => Loc::getMessage('LANDING_CONNECTOR_AI_ALLOW_SITE'),
				'header' => Loc::getMessage('LANDING_CONNECTOR_AI_ALLOW_SITE_DESC'),
				'type' => Tuning\Type::BOOLEAN,
				'default' => true,
				'sort' => 100,
			];

			try
			{
				$quality = new Quality([
					Quality::QUALITIES['ai_site'],
				]);
			}
			catch (SystemException)
			{
				$quality = null;
			}

			if (Copilot\Manager::isFeatureEnabled())
			{
				$items[self::TUNING_CODE_SITE_IMAGE_PROVIDER] = array_merge(
					Tuning\Defaults::getProviderSelectFieldParams(Engine::CATEGORIES['image']),
					[
						'group' => self::TUNING_CODE_GROUP,
						'title' => Loc::getMessage('LANDING_CONNECTOR_AI_SITE_IMAGE_PROVIDER'),
						'sort' => 110,
					],
				);

				$items[self::TUNING_CODE_SITE_TEXT_PROVIDER] = array_merge(
					Tuning\Defaults::getProviderSelectFieldParams(Engine::CATEGORIES['text'], $quality),
					[
						'group' => self::TUNING_CODE_GROUP,
						'title' => Loc::getMessage('LANDING_CONNECTOR_AI_SITE_TEXT_PROVIDER'),
						'sort' => 120,
					],
				);

				$relations[self::TUNING_CODE_ALLOW_SITE_COPILOT] = [
					self::TUNING_CODE_SITE_IMAGE_PROVIDER,
					self::TUNING_CODE_SITE_TEXT_PROVIDER,
				];
			}
			else
			{
				$items[self::TUNING_CODE_ALLOW_SITE_COPILOT]['additional'] = [
					'bannerCode' => 'limit_copilot',
					'helpMessage' => Loc::getMessage('LANDING_CONNECTOR_AI_SITE_UNAVAILABLE_MESSAGE'),
				];
			}
		}
		// endregion

		// region standart copilot
		$items[self::TUNING_CODE_ALLOW_COPILOT] = [
			'group' => self::TUNING_CODE_GROUP,
			'title' => Loc::getMessage('LANDING_CONNECTOR_AI_ALLOW_TITLE'),
			'header' => Loc::getMessage('LANDING_CONNECTOR_AI_ALLOW_DESC'),
			'type' => Tuning\Type::BOOLEAN,
			'default' => true,
			'sort' => 200,
		];

		$items[self::TUNING_CODE_IMAGE_PROVIDER] = array_merge(
			Tuning\Defaults::getProviderSelectFieldParams(Engine::CATEGORIES['image']),
			[
				'group' => self::TUNING_CODE_GROUP,
				'title' => Loc::getMessage('LANDING_CONNECTOR_AI_IMAGE_PROVIDER_TITLE'),
				'sort' => 210,
			],
		);

		$items[self::TUNING_CODE_TEXT_PROVIDER] = array_merge(
			Tuning\Defaults::getProviderSelectFieldParams(Engine::CATEGORIES['text']),
			[
				'group' => self::TUNING_CODE_GROUP,
				'title' => Loc::getMessage('LANDING_CONNECTOR_AI_TEXT_PROVIDER_TITLE'),
				'sort' => 220,
			],
		);

		$relations[self::TUNING_CODE_ALLOW_COPILOT] = [
			self::TUNING_CODE_IMAGE_PROVIDER,
			self::TUNING_CODE_TEXT_PROVIDER,
		];
		// endregion

		$result->modifyFields([
			'items' => $items,
			'groups' => $groups,
			'itemRelations' => [
				self::TUNING_CODE_GROUP => $relations,
			],
		]);

		return $result;
	}

	/**
	 * Checks whether engine is off or not.
	 * @param Event $event Event instance.
	 * @return EventResult
	 * @see onTuningLoad
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

		return new EventResult(EventResult::ERROR);
	}
}
