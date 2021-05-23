<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sale
 * @copyright 2001-2016 Bitrix
 */

use Bitrix\Main,
	Bitrix\Main\Localization\Loc,
	Bitrix\Main\Loader;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

class PersonalProfile extends CBitrixComponent
{
	const E_SALE_MODULE_NOT_INSTALLED 		= 10000;

	public function executeComponent()
	{
		$arDefaultUrlTemplates404 = array(
			"list" => "profile_list.php",
			"detail" => "profile_detail.php?ID=#ID#",
		);

		$arDefaultVariableAliases404 = array();

		$arComponentVariables = array("ID", "del_id");

		$arVariables = array();

		$this->setFrameMode(false);

		if ($this->arParams["SEF_MODE"] == "Y")
		{
			$arUrlTemplates = CComponentEngine::makeComponentUrlTemplates($arDefaultUrlTemplates404, $this->arParams["SEF_URL_TEMPLATES"]);
			$arVariableAliases = CComponentEngine::makeComponentVariableAliases($arDefaultVariableAliases404, $this->arParams["VARIABLE_ALIASES"]);

			$componentPage = CComponentEngine::parseComponentPath(
				$this->arParams["SEF_FOLDER"],
				$arUrlTemplates,
				$arVariables
			);

			CComponentEngine::initComponentVariables($componentPage, $arComponentVariables, $arVariableAliases, $arVariables);

			foreach ($arUrlTemplates as $url => $value)
				$this->arResult["PATH_TO_".ToUpper($url)] = $this->arParams["SEF_FOLDER"].$value;

			if ($componentPage != "detail")
				$componentPage = "list";

			$this->arResult = array_merge(
				Array(
					"SEF_FOLDER" => $this->arParams["SEF_FOLDER"],
					"URL_TEMPLATES" => $arUrlTemplates,
					"VARIABLES" => $arVariables,
					"ALIASES" => $arVariableAliases,
				),
				$this->arResult
			);
		}
		else
		{
			$arVariableAliases = CComponentEngine::makeComponentVariableAliases($arDefaultVariableAliases404, $this->arParams["VARIABLE_ALIASES"]);
			CComponentEngine::initComponentVariables(false, $arComponentVariables, $arVariableAliases, $arVariables);

			if ((int)($_REQUEST["ID"]) > 0)
				$componentPage = "detail";
			else
				$componentPage = "list";

			$this->arResult = array(
				"VARIABLES" => $arVariables,
				"ALIASES" => $arVariableAliases
			);
		}
		$this->includeComponentTemplate($componentPage);

	}

	/**
	 * Function checks if required modules installed. If not, throws an exception
	 * @throws Main\SystemException
	 * @return void
	 */
	protected function checkRequiredModules()
	{
		if (!Loader::includeModule('sale'))
		{
			throw new Main\SystemException(Loc::getMessage("SALE_MODULE_NOT_INSTALL"), self::E_SALE_MODULE_NOT_INSTALLED);
		}
	}
}