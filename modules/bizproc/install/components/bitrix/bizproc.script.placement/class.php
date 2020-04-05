<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main;

class BizprocScriptPlacementComponent extends \CBitrixComponent
{
	public function onPrepareComponentParams($arParams)
	{
		$arParams["MODULE_ID"] = trim(empty($arParams["MODULE_ID"]) ? $_REQUEST["module_id"] : $arParams["MODULE_ID"]);
		$arParams["ENTITY"] = trim(empty($arParams["ENTITY"]) ? $_REQUEST["entity"] : $arParams["ENTITY"]);
		$arParams["DOCUMENT_TYPE"] = trim(empty($arParams["DOCUMENT_TYPE"]) ? $_REQUEST["document_type"] : $arParams["DOCUMENT_TYPE"]);
		$arParams["DOCUMENT_ID"] = trim(empty($arParams["DOCUMENT_ID"]) ? $_REQUEST["document_id"] : $arParams["DOCUMENT_ID"]);
		//$arParams["AUTO_EXECUTE_TYPE"] = isset($arParams["AUTO_EXECUTE_TYPE"]) ? (int)$arParams["AUTO_EXECUTE_TYPE"] : null;

		$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y");

		return $arParams;
	}

	public function executeComponent()
	{
		if (!self::canUse())
		{
			return false;
		}

		$this->includeComponentTemplate();
	}

	public static function getGridPanelButton($gridId, array $params)
	{
		if (!self::canUse())
		{
			return false;
		}

		$scriptList = \Bitrix\Bizproc\Automation\Script\Manager::getListByPlacement($params['PLACEMENT']);
		$items = [];

		if(count($scriptList) > 0)
		{
			$jsGridId = \CUtil::JSEscape($gridId);

			foreach($scriptList as $script)
			{
				$itemText = strlen($script['NAME']) > 0
					? $script['NAME']
					: $script['APP_NAME'];

				$jsScriptId = (int) $script['ID'];

				$items[] = [
					'NAME' => $itemText,
					'VALUE' => 'script:'.$script['ID'],
					'ONCHANGE' => [
						[
							'ACTION' => Bitrix\Main\Grid\Panel\Actions::CALLBACK,
							'DATA' => [
								[
									'JS' => "BX.Bizproc.ScriptPlacementMenu.onGridPanelButtonClick("
										."'{$jsGridId}', {$jsScriptId})"
								]
							]
						]
					]
				];
			}
		}

		return [
			"TYPE" => \Bitrix\Main\Grid\Panel\Types::DROPDOWN,
			"NAME" => 'start_bizproc_script',
			'ID' => 'start_bizproc_script',
			"ITEMS" => array_merge(
				[
					[
						'NAME' => Main\Localization\Loc::getMessage('BP_SCRIPT_PLACEMENT_GRID_BTN_TITLE'),
						'VALUE' => 'none'
					]
				],
				$items
			)
		];
	}

	private static function canUse()
	{
		if (!Main\Loader::includeModule('bizproc'))
		{
			return false;
		}

		return (COption::GetOptionString('bizproc', 'script_placement_enabled') === 'Y');
	}
}