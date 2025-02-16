<?php

namespace Bitrix\Catalog\Integration\AI;

use Bitrix\AI\Context;
use Bitrix\AI\Engine;
use Bitrix\AI\Tuning;
use Bitrix\Main\Entity;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

class Settings
{
	private const TEXT_CATEGORY = 'text';
	private const TUNING_CODE_TEXT_PRODUCT_CARD = 'catalog_product_card_allow_text_generate';

	public static function isTextProductCardAvailable(): bool
	{
		if (!self::checkEngineAvailable(self::TEXT_CATEGORY))
		{
			return false;
		}

		$item = (new Tuning\Manager())->getItem(self::TUNING_CODE_TEXT_PRODUCT_CARD);

		return $item ? $item->getValue() : true;
	}

	private static function checkEngineAvailable(string $type): bool
	{
		if (!Loader::includeModule('ai'))
		{
			return false;
		}

		$engine = Engine::getByCategory($type, Context::getFake());

		if (!$engine)
		{
			return false;
		}

		return true;
	}

	public static function onTuningLoad(): Entity\EventResult
	{
		$result = new Entity\EventResult();

		$items = [];
		$groups = [];

		if (Engine::getByCategory(self::TEXT_CATEGORY, Context::getFake()))
		{
			$items[self::TUNING_CODE_TEXT_PRODUCT_CARD] = [
				'group' => Tuning\Defaults::GROUP_TEXT,
				'header' => Loc::getMessage('CATALOG_AI_SETTINGS_ALLOW_TEXT_PROSUCT_CARD_COPILOT_DESC'),
				'title' => Loc::getMessage('CATALOG_AI_SETTINGS_COPILOT_PRODUCT_CARD_TITLE'),
				'type' => Tuning\Type::BOOLEAN,
				'default' => true,
				'sort' => 700,
			];
		}

		$result->modifyFields([
			'items' => $items,
			'groups' => $groups,
		]);

		return $result;
	}
}