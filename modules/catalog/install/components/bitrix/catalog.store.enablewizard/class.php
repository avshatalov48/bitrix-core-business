<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Catalog\Config\Feature;
use Bitrix\Main\Localization\Loc;
use Bitrix\Catalog\Product\Store\CostPriceCalculator;
use Bitrix\Main\Application;
use Bitrix\Catalog\Store\EnableWizard\ModeList;
use Bitrix\Catalog\Store\EnableWizard\Manager;
use Bitrix\Main\Loader;
use Bitrix\Catalog\Store\EnableWizard\OnecVersionList;
use Bitrix\Catalog\Store\EnableWizard\ConditionsChecker;
use Bitrix\Catalog\Url\InventoryManagementSourceBuilder;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Catalog\Store\EnableWizard\OnecAppManager;
use Bitrix\Catalog\Store\EnableWizard\TariffChecker;

Loc::loadLanguageFile(__FILE__);

Loader::includeModule('catalog');

class CatalogStoreEnableWizardComponent extends CBitrixComponent implements Controllerable
{
	public function executeComponent()
	{
		$request = Application::getInstance()->getContext()->getRequest();
		$currentMode = Manager::getCurrentMode();

		$this->arResult = [
			'options' => [
				'currentMode' => Manager::getCurrentMode(),
				'initEnableMode' => $request->get('initEnableMode'),
				'availableModes' => self::getModesOptions(),
				'hasConductedDocumentsOrQuantities' => ConditionsChecker::hasConductedDocumentsOrQuantities(),
				'areTherePublishedShops' => ConditionsChecker::areTherePublishedShops(),
				'areThereActiveProducts' => ConditionsChecker::areThereActiveProducts(),
				'inventoryManagementSource' => InventoryManagementSourceBuilder::getInstance()
					->getInventoryManagementSource(),
			],
			'analytics' => [
				'tool' => 'inventory',
				'category' => 'enable_wizard',
				'type' => count(self::getModesOptions()) > 1 ? 'slider_1С' : 'slider_B24',
				'c_section' => $request->get('analyticsContextSection'),
				'p1' => 'mode_' . ($currentMode ?? 'None'),
			],
		];

		$this->includeComponentTemplate();
	}

	private static function getModesOptions(): array
	{
		$result = [];

		$allModesOptions = [
			ModeList::B24 => [
				'costPriceMethodList' => CostPriceCalculator::getMethodList(),
				'isPlanRestricted' => !Feature::isInventoryManagementEnabled(),
			],
			ModeList::ONEC => [
				'versionList' => OnecVersionList::getList(),
				'installUrl' => OnecAppManager::getInstallUrl(),
				'isPlanRestricted' => TariffChecker::isOnecInventoryManagementRestricted(),
			],
		];

		$availableModes = Manager::getAvailableModes();
		foreach ($availableModes as $availableMode)
		{
			if (!isset($allModesOptions[$availableMode]))
			{
				continue;
			}

			$result[$availableMode] = $allModesOptions[$availableMode];
		}

		return $result;
	}

	public function getOnecAppAction(): array
	{
		return [
			'isInstalled' => OnecAppManager::isAppInstalled(),
		];
	}

	public function configureActions()
	{
		return [];
	}
}
