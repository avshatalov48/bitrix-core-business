<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main;

class BizprocScriptEditComponent extends \CBitrixComponent
{
	protected function listKeysSignedParameters()
	{
		return ['SCRIPT_ID'];
	}

	public function onPrepareComponentParams($params)
	{
		$params["SCRIPT_ID"] = (int) $params["SCRIPT_ID"];
		$params["SET_TITLE"] = ($params["SET_TITLE"] == "N" ? "N" : "Y");

		return $params;
	}

	public function executeComponent()
	{
		global $APPLICATION;

		if (!Main\Loader::includeModule('bizproc'))
		{
			return false;
		}

		if ($this->arParams['SET_TITLE'] === 'Y')
		{
			$APPLICATION->SetTitle(GetMessage("BP_SCR_ED_CMP_TITLE"));
		}

		$script = \Bitrix\Bizproc\Automation\Script\Manager::getById($this->arParams['SCRIPT_ID']);

		if (!$script)
		{
			ShowError(GetMessage("BP_SCR_ED_CMP_SCRIPT_NOT_FOUND"));
			return;
		}

		$this->arResult['SCRIPT'] = $script;

		$this->includeComponentTemplate();
	}
}