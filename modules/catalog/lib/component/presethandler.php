<?php

namespace Bitrix\Catalog\Component;

use Bitrix\Catalog\Component\Preset\Enum;

class PresetHandler
{
	/**
	 * @method PresetHandler register() Registers handler for the main::OnProlog event
	 * Event cannot be subscribed from catalog module.
	 * Because catalog module may not be available immediately during installation
	 *
	 * @see \Bitrix\Bitrix24\Preset\PresetCrmStore::apply()
	 * @see \Bitrix\Bitrix24\Preset\PresetCrmStoreMenu::apply()
	 */

	/**
	 * Registers handler for the main::OnProlog event
	 * @return bool
	 */
	public static function register(): bool
	{
		$eventManager = \Bitrix\Main\EventManager::getInstance();
		$eventManager->registerEventHandler("main", "OnProlog", "catalog", "\Bitrix\Catalog\Component\PresetHandler", "onProlog");
		return true;
	}

	/**
	 * Unregisters handler for the main::OnProlog event
	 * @return bool
	 */
	public static function unRegister(): bool
	{
		$eventManager = \Bitrix\Main\EventManager::getInstance();
		$eventManager->unRegisterEventHandler("main", "OnProlog", "catalog", "\Bitrix\Catalog\Component\PresetHandler", "OnProlog");
		return true;
	}

	/**
	 * Insert inline-scripts into the page and execute the rest-request
	 *
	 * @throws \Bitrix\Main\LoaderException
	 */
	public static function onProlog(): void
	{
		\Bitrix\Main\UI\Extension::load(['catalog.store-use']);

		$preset = [
			Enum::TYPE_CRM,
			Enum::TYPE_MENU,
			Enum::TYPE_STORE
		];

		UseStore::enableWithPreset($preset);

		$content = "
			<script>
				var controller = new BX.Catalog.StoreUse.Controller();
				
				controller.inventoryManagementAnalyticsFromLanding({
					preset: ".\CUtil::PhpToJSObject($preset)."
				});
			</script>
		";

		global $APPLICATION;
		$APPLICATION->AddViewContent("inline-scripts", $content);
	}
}