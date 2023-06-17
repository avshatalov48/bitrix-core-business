<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Catalog\Access\AccessController;
use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Catalog\Url\InventoryManagementSourceBuilder;
use Bitrix\Main;
use \Bitrix\Catalog\Component\Preset;
use Bitrix\Main\Localization\Loc;
use Bitrix\UI\Toolbar\Facade\Toolbar;

Loc::loadLanguageFile(__FILE__);

\Bitrix\Main\Loader::includeModule('catalog');

/**
 * Class WarehouseMasterClear
 */
class WarehouseMasterClear extends CBitrixComponent implements Bitrix\Main\Engine\Contract\Controllerable
{
	const MODE_VIEW = 'view';
	const MODE_EDIT = 'edit';

	/**
	 * @param $arParams
	 * @return array
	 */
	public function onPrepareComponentParams($arParams)
	{
		$modes = [self::MODE_EDIT, self::MODE_VIEW];

		if (!isset($arParams['MODE']))
		{
			$arParams['MODE'] = $this->request->get('mode');

		}

		$arParams['MODE'] = in_array($arParams['MODE'], $modes) ? $arParams['MODE']: self::MODE_VIEW;

		return parent::onPrepareComponentParams($arParams);
	}

	/**
	 * @return void
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public function executeComponent()
	{
		if (Main\Loader::includeModule('crm'))
		{
			$this->arResult['IS_WITH_ORDERS_MODE'] = \CCrmSaleHelper::isWithOrdersMode();
			$this->arResult['IS_LEAD_ENABLED'] = \Bitrix\Crm\Settings\LeadSettings::isEnabled();
		}

		$this->arResult['IS_USED_ONEC'] = \Bitrix\Catalog\Component\UseStore::isUsedOneC();
		$this->arResult['IS_PLAN_RESTRICTED'] = \Bitrix\Catalog\Component\UseStore::isPlanRestricted();
		$this->arResult['IS_USED'] = \Bitrix\Catalog\Component\UseStore::isUsed();
		$this->arResult['IS_EMPTY'] = \Bitrix\Catalog\Component\UseStore::isEmpty();
		$this->arResult['IS_RESTRICTED_ACCESS'] = !$this->checkRights();
		$this->arResult['MODE'] = $this->arParams['MODE'];
		$this->arResult['PRESET_LIST'] = $this->getPresetList();
		$this->arResult['PREVIEW_LANG'] = $this->getPortalZone();
		$this->arResult['INVENTORY_MANAGEMENT_SOURCE'] =
			InventoryManagementSourceBuilder::getInstance()->getInventoryManagementSource()
		;

		$this->includeComponentTemplate();
	}

	protected function getPortalZone()
	{
		$zone = $this->getZone();

		if ($zone == 'ua')
		{
			$result = $zone;
		}
		else
		{
			$result = in_array($zone, ['ru','by','kz']) ? 'ru' : 'en';
		}

		return $result;
	}

	private function getZone()
	{
		if (Bitrix\Main\ModuleManager::isModuleInstalled('bitrix24'))
		{
			$zone = \CBitrix24::getPortalZone();
		}
		else
		{
			$iterator = Bitrix\Main\Localization\LanguageTable::getList([
				'select' => ['ID'],
				'filter' => [
					'=DEF' => 'Y',
					'=ACTIVE' => 'Y'
				]
			]);
			$row = $iterator->fetch();
			$zone = $row['ID'];
		}

		return $zone;
	}

	protected function presetIsAvailable(): string
	{
		if (!$this->checkRights())
		{
			return 'N';
		}

		if ($this->arParams['MODE'] == self::MODE_EDIT)
		{
			$result = 'Y';
		}
		else
		{
			$result = \Bitrix\Catalog\Component\UseStore::isUsed() ? 'N' : 'Y';
		}

		return $result;
	}

	protected function presetIsChecked($type, bool $needCheckEmpty = false): string
	{
		if ($needCheckEmpty)
		{
			$isOn = (Preset\Factory::create($type)->isOn() || $this->presetsIsEmpty() ? 'Y' : 'N');
		}
		else
		{
			$isOn =  (Preset\Factory::create($type)->isOn() ? 'Y' : 'N');
		}

		$forceOff = \Bitrix\Catalog\Component\UseStore::isUsed();

		if ($this->arParams['MODE'] == self::MODE_EDIT)
		{
			$result = $isOn;
		}
		else
		{
			$result = ($forceOff ? 'N' : $isOn);
		}

		return $result;
	}

	protected static function getMessagesStoreResolve($codes) :array
	{
		$result = [];

		foreach ($codes as $code)
		{
			$result[] = Loc::getMessage('CAT_WAREHOUSE_MASTER_CLEAR_PRESET_STORE_'.$code);
		}
		return $result;
	}


	public function getPresetList(): array
	{
		$result[] = [
				'code' => Preset\Enum::TYPE_MENU,
				'checked' => $this->presetIsChecked(Preset\Enum::TYPE_MENU, $this->arParams['MODE'] === self::MODE_VIEW),
				'available' => $this->presetIsAvailable(),
				'title' => Loc::getMessage('CAT_WAREHOUSE_MASTER_CLEAR_PRESET_1'),
				'description' => Loc::getMessage('CAT_WAREHOUSE_MASTER_CLEAR_PRESET_2'),
				'soon' => 'N',
				'hint' => Loc::getMessage('CAT_WAREHOUSE_MASTER_CLEAR_PRESET_HINT_8')
			];

		$result[] = [
				'code' => Preset\Enum::TYPE_CRM,
				'checked' => $this->presetIsChecked(Preset\Enum::TYPE_CRM, $this->arParams['MODE'] === self::MODE_VIEW),
				'available' => $this->presetIsAvailable(),
				'title' => Loc::getMessage('CAT_WAREHOUSE_MASTER_CLEAR_PRESET_3'),
				'description' => Loc::getMessage('CAT_WAREHOUSE_MASTER_CLEAR_PRESET_4'),
				'soon' => 'N',
				'hint' => Loc::getMessage('CAT_WAREHOUSE_MASTER_CLEAR_PRESET_HINT_9')
			];

		$codes = Bitrix\Catalog\Component\UseStore::getCodesStoreByZone();
		if (count($codes)>0)
		{
			$messages = self::getMessagesStoreResolve($codes);

			$result[] = [
				'code' => Preset\Enum::TYPE_STORE,
				'checked' => $this->presetIsChecked(Preset\Enum::TYPE_STORE),
				'available' => $this->presetIsAvailable(),
				'title' => Loc::getMessage('CAT_WAREHOUSE_MASTER_CLEAR_PRESET_5'),
				'description' => Loc::getMessage('CAT_WAREHOUSE_MASTER_CLEAR_PRESET_6', ['#STORE_CODES#'=> implode(', ', $messages)]),
				'soon' => 'N',
				'hint' => Loc::getMessage('CAT_WAREHOUSE_MASTER_CLEAR_PRESET_HINT_10')
			];
		}

		$result[] = [
				'code' => Preset\Enum::TYPE_MATERIAL,
				'checked' => $this->presetIsChecked(Preset\Enum::TYPE_MATERIAL),
				'available' => 'N',
				'title' => Loc::getMessage('CAT_WAREHOUSE_MASTER_CLEAR_PRESET_7'),
				'description' => '',
				'soon' => 'Y',
				'hint' => ''
			];

		return $result;
	}

	protected function presetsIsEmpty(): bool
	{
		foreach (Preset\Enum::getAllType() as $type)
		{
			if (Preset\Factory::create($type)->isOn() === true)
			{
				return false;
			}
		}
		return true;
	}

	protected function checkRights()
	{
		return AccessController::getCurrent()->check(ActionDictionary::ACTION_CATALOG_SETTINGS_ACCESS);
	}

	/**
	 * @inheritDoc
	 */
	public function configureActions()
	{
		return [];
	}
}
