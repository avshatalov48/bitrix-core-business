<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if($arParams["START_PAGE"] == '')
	$arParams["START_PAGE"] = 'new';

if($arParams["AJAX_MODE"] == "Y")
{
	$ajaxSession = CAjax::GetSession();
	if($ajaxSession && $arParams["AJAX_ID"] != $ajaxSession)
	{
		return;
	}
}

if (CModule::IncludeModule("form"))
{
	$componentPage = "";

	if ($arParams["SEF_MODE"] == "Y")
	{
		// SEF mode enabled

		$WEB_FORM_ID_TEMP = $arParams["WEB_FORM_ID"];

		$arDefaultUrlTemplates404 = array(
				"new"  => "#WEB_FORM_ID#/",
				"list" => "#WEB_FORM_ID#/list/",
				"edit" => "#WEB_FORM_ID#/edit/#RESULT_ID#/",
				"view" => "#WEB_FORM_ID#/view/#RESULT_ID#/",
				"success" => "",
		);

		$arDefaultVariableAliases404 = array(
		);

		$arDefaultVariableAliases = array();

		$arComponentVariables = array("WEB_FORM_ID", "RESULT_ID");

		$arVariables = array();
		$arComponentPage = array_keys($arDefaultUrlTemplates404);

		$arUrlTemplates = CComponentEngine::MakeComponentUrlTemplates($arDefaultUrlTemplates404, $arParams["SEF_URL_TEMPLATES"]);
		$arVariableAliases = CComponentEngine::MakeComponentVariableAliases($arDefaultVariableAliases404, $arParams["VARIABLE_ALIASES"]);

		// get current component page
		if (array_key_exists($arVariables["page"], $arDefaultUrlTemplates404))
			$componentPage = $arVariables["page"];

		if (empty($componentPage) || (!array_key_exists($componentPage, $arDefaultUrlTemplates404)))
		{
			// parse url to get page
			$componentPage = CComponentEngine::ParseComponentPath($arParams["SEF_FOLDER"], $arUrlTemplates, $arVariables);

			// if there's no page identefier - set it to start one
			if($componentPage == '')
			{
				$componentPage = $arParams["START_PAGE"];
			}

			// if page is disabled - set it to start one
			if ($componentPage != "new" && $arParams["SHOW_".mb_strtoupper($componentPage)."_PAGE"] != "Y")
			{
				$componentPage = $arParams["START_PAGE"];
			}
		}

		// get variables
		CComponentEngine::InitComponentVariables($componentPage, $arComponentVariables, $arVariableAliases, $arVariables);

		if (intval($arVariables["WEB_FORM_ID"]) > 0) $arParams["WEB_FORM_ID"] = intval($arVariables["WEB_FORM_ID"]);
		if (intval($arVariables["RESULT_ID"]) > 0) $arParams["RESULT_ID"] = intval($arVariables["RESULT_ID"]);

		// set component params for pages
		switch ($componentPage)
		{
			case "list":
				if ($arParams["SHOW_EDIT_PAGE"] == "Y")
					$arParams["EDIT_URL"] = CComponentEngine::MakePathFromTemplate($arParams["SEF_FOLDER"].$arParams["SEF_URL_TEMPLATES"]["edit"], array("WEB_FORM_ID" => $arParams["WEB_FORM_ID"]));
				if ($arParams["SHOW_VIEW_PAGE"] == "Y")
					$arParams["VIEW_URL"] = CComponentEngine::MakePathFromTemplate($arParams["SEF_FOLDER"].$arParams["SEF_URL_TEMPLATES"]["view"], array("WEB_FORM_ID" => $arParams["WEB_FORM_ID"]));

				$arParams["NEW_URL"] = CComponentEngine::MakePathFromTemplate($arParams["SEF_FOLDER"].$arParams["SEF_URL_TEMPLATES"]["new"], array("WEB_FORM_ID" => $arParams["WEB_FORM_ID"]));
			break;

			case "edit":
				if ($arParams["SHOW_VIEW_PAGE"] == "Y")
					$arParams["VIEW_URL"] = CComponentEngine::MakePathFromTemplate($arParams["SEF_FOLDER"].$arParams["SEF_URL_TEMPLATES"]["view"], array("WEB_FORM_ID" => $arParams["WEB_FORM_ID"], "RESULT_ID" => $arParams["RESULT_ID"]));

				if ($arParams["SHOW_LIST_PAGE"] == "Y")
					$arParams["LIST_URL"] = CComponentEngine::MakePathFromTemplate($arParams["SEF_FOLDER"].$arParams["SEF_URL_TEMPLATES"]["list"], array("WEB_FORM_ID" => $arParams["WEB_FORM_ID"]));

				$arParams["EDIT_URL"] = CComponentEngine::MakePathFromTemplate($arParams["SEF_FOLDER"].$arParams["SEF_URL_TEMPLATES"]["edit"], array("WEB_FORM_ID" => $arParams["WEB_FORM_ID"], "RESULT_ID" => $arParams["RESULT_ID"]));
			break;

			case "view":
				if ($arParams["SHOW_EDIT_PAGE"] == "Y")
					$arParams["EDIT_URL"] = CComponentEngine::MakePathFromTemplate($arParams["SEF_FOLDER"].$arParams["SEF_URL_TEMPLATES"]["edit"], array("WEB_FORM_ID" => $arParams["WEB_FORM_ID"], "RESULT_ID" => $arParams["RESULT_ID"]));
			break;

			case "new":
				if ($arParams["SHOW_LIST_PAGE"] == "Y")
					$arParams["LIST_URL"] = CComponentEngine::MakePathFromTemplate($arParams["SEF_FOLDER"].$arParams["SEF_URL_TEMPLATES"]["list"], array("WEB_FORM_ID" => $arParams["WEB_FORM_ID"]));
				if ($arParams["SHOW_EDIT_PAGE"] == "Y")
					$arParams["EDIT_URL"] = CComponentEngine::MakePathFromTemplate($arParams["SEF_FOLDER"].$arParams["SEF_URL_TEMPLATES"]["edit"], array("WEB_FORM_ID" => $arParams["WEB_FORM_ID"]));

			break;
		}

		if (!empty($WEB_FORM_ID_TEMP)) $arParams["WEB_FORM_ID"] = $WEB_FORM_ID_TEMP;
	}
	else
	{
		// SEF mode disabled

		$arDefaultVariableAliases = array(
			"WEB_FORM_ID" => "WEB_FORM_ID",
			"RESULT_ID" => "RESULT_ID",
			"action" => "action",
		);

		$arVariables = array();
		$arVariableAliases = CComponentEngine::MakeComponentVariableAliases($arDefaultVariableAliases, $arParams["VARIABLE_ALIASES"]);
		CComponentEngine::InitComponentVariables(False, [], $arVariableAliases, $arVariables);

		$arPages = array("NEW", "EDIT", "LIST", "VIEW");

		// get current page
		$sAction = mb_strtoupper(trim($arVariables["action"] ?? ''));

		// check current page
		if (!in_array($sAction, $arPages)) $sAction = "";
		elseif ($sAction != "NEW" && $arParams["SHOW_".$sAction."_PAGE"] != "Y") $sAction = "";

		// if current page is wrong or not set - get default value
		if (mb_strlen($sAction) <= "0") $sAction = $arParams["START_PAGE"];

		$componentPage = mb_strtolower($sAction);

		// prepare component parameters for pages
		foreach ($arPages as $page)
		{
			if ($page == "NEW" || $arParams["SHOW_".$page."_PAGE"] == "Y")
			{
				$arParams[$page."_URL"] = $APPLICATION->GetCurPageParam(
					mb_strtolower($page) == $arParams["START_PAGE"] ? "" : $arVariableAliases["action"]."=".mb_strtolower($page),
					array_merge(array_values($arVariableAliases), array("strFormNote", 'formresult')),
					false
				);
			}
		}

		$arParams["VARIABLE_ALIASES"] = $arVariableAliases;
	}

	$arParams['NAME_TEMPLATE'] = empty($arParams['NAME_TEMPLATE'])
		? (method_exists('CSite', 'GetNameFormat') ? CSite::GetNameFormat() : "#NAME# #LAST_NAME#")
		: $arParams["NAME_TEMPLATE"];

	$this->IncludeComponentTemplate($componentPage);
}
?>