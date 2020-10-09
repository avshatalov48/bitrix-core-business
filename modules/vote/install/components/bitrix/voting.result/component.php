<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
if (!IsModuleInstalled("vote")):
	ShowError(GetMessage("VOTE_MODULE_IS_NOT_INSTALLED"));
	return;
elseif ($arParams["VOTE_ID"] <= 0):
	ShowError(GetMessage("VOTE_EMPTY"));
	return false;
endif;

require_once($_SERVER["DOCUMENT_ROOT"].$componentPath."/functions.php");
global $arrSaveColor;
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/img.php");
/********************************************************************
				Input params
********************************************************************/
/************** BASE ***********************************************/
	$arParams["VOTE_ID"] = intval($arParams["VOTE_ID"]);
	$arParams["PERMISSION"] = (isset($arParams["PERMISSION"]) && ($arParams["PERMISSION"] > 0 || $arParams["PERMISSION"] === 0) ?
		intval($arParams["PERMISSION"]) : false);
/************** URL ************************************************/
	$URL_NAME_DEFAULT = array(
		"vote_form" => "PAGE_NAME=vote_new&VOTE_ID=#VOTE_ID#",
		"vote_result" => "PAGE_NAME=vote_result&VOTE_ID=#VOTE_ID#");
	foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE):
		if (trim($arParams[mb_strtoupper($URL)."_TEMPLATE"]) == '')
			$arParams[mb_strtoupper($URL)."_TEMPLATE"] = $APPLICATION->GetCurPage()."?".$URL_VALUE;
		$arParams["~".mb_strtoupper($URL)."_TEMPLATE"] = $arParams[mb_strtoupper($URL)."_TEMPLATE"];
		$arParams[mb_strtoupper($URL)."_TEMPLATE"] = htmlspecialcharsbx($arParams["~".mb_strtoupper($URL)."_TEMPLATE"]);
	endforeach;
/************** ADDITIONAL *****************************************/
	$arParams["NEED_SORT"] = ($arParams["NEED_SORT"] == "N" ? "N" : "Y");
/************** CACHE **********************************************/
	if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
		$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
	else
		$arParams["CACHE_TIME"] = 0;

	$arParams["ADDITIONAL_CACHE_ID"] = (isset($arParams["ADDITIONAL_CACHE_ID"]) && $arParams["ADDITIONAL_CACHE_ID"] <> '' ?
		$arParams["ADDITIONAL_CACHE_ID"] : $USER->GetGroups() );
/********************************************************************
				/Input params
********************************************************************/

/********************************************************************
				Default values
********************************************************************/
$arResult["CHANNEL"] = array();
$arResult["VOTE"] = array();
$arResult["QUESTIONS"] = array();
$arResult["GROUP_ANSWERS"] = array();
$arResult["LAST_VOTE"] = false;
$arResult["~CURRENT_PAGE"] = $APPLICATION->GetCurPageParam("", array("VOTE_ID","VOTING_OK","VOTE_SUCCESSFULL"));
$arResult["CURRENT_PAGE"] = htmlspecialcharsbx($arResult["~CURRENT_PAGE"]);
/********************************************************************
				/Default values
********************************************************************/

/********************************************************************
				Data
 ********************************************************************/
$obCache = new CPHPCache;
$cache_id = "vote_result_".serialize(array(
	$arParams["VOTE_ID"],
	$arParams["ADDITIONAL_CACHE_ID"],
	$arParams["VOTE_ALL_RESULTS"],
	(isset($arParams["PERMISSION"]) ? $arParams["PERMISSION"] : array()))).
	((($tzOffset = CTimeZone::GetOffset()) <> 0) ? "_".$tzOffset : "");
$cache_path = str_replace(array(":", "//"), "/", "/".SITE_ID."/".$componentName."/".$arParams["VOTE_ID"]);
if ($obCache->InitCache($arParams["CACHE_TIME"], $cache_id, $cache_path))
{
	$arVars = $obCache->GetVars();
	$arResult = $arVars["arResult"];
}
elseif (CModule::IncludeModule("vote"))
{
	$arAnswers = $arDropDown = $arMultiSelect = array();
	$arParams["VOTE_ID"] = GetVoteDataByID(
		$arParams["VOTE_ID"],
		$arResult["CHANNEL"],
		$arResult["VOTE"],
		$arResult["QUESTIONS"],
		$arAnswers, $arDropDown, $arMultiSelect,
		$arResult["GROUP_ANSWERS"],
		array(
			"bGetMemoStat" => $arParams["VOTE_ALL_RESULTS"]
		)
	);
	if ($arParams["VOTE_ID"] <= 0):
		ShowError(GetMessage("VOTE_NOT_FOUND"));
		return;
	endif;

	$counter = intval($arResult["VOTE"]["COUNTER"]);
	$counter = ($counter <= 0 ? 1 : $counter);

	if ($arParams["VOTE_ALL_RESULTS"] == "Y" && !empty($arResult["GROUP_ANSWERS"]))
	{
		foreach ($arResult["GROUP_ANSWERS"] as $answerId => $answerOptions)
		{
			$userAnswerSum = 0;
			foreach ($answerOptions as $answerOption)
			{
				$userAnswerSum += $answerOption["COUNTER"];
			}

			$ucolor = "\n";
			foreach ($answerOptions as $aID => $answerOption)
			{
				$ucolor = GetNextRGB($ucolor, count($answerOptions));
				$arResult["GROUP_ANSWERS"][$answerId][$aID]["COLOR"] = $ucolor;
				$arResult["GROUP_ANSWERS"][$answerId][$aID]["PERCENT"] = ($userAnswerSum > 0 ? round($answerOption["COUNTER"]*100/$userAnswerSum) : 0);
			}
		}
	}
	foreach ($arResult["QUESTIONS"] as $qID => $arQuestion)
	{
		//Include in the result chart
		if ($arQuestion["DIAGRAM"] == "N")
		{
			unset($arAnswers[$qID]);
			unset($arResult["QUESTIONS"][$qID]);
			continue;
		}
		elseif (empty($arQuestion["ANSWERS"]))
		{
			unset($arResult["QUESTIONS"][$qID]);
			continue;
		}

		//Calculating the sum and maximum value
		$counterSum = $counterMax = 0;
		foreach ($arQuestion["ANSWERS"] as $aID => $arAnswer)
		{
			if ($arAnswer["MESSAGE"] == "")
				unset($arQuestion["ANSWERS"][$aID]);

			$counterSum += $arAnswer["COUNTER"];
			$counterMax = max(intval($arAnswer["COUNTER"]), $counterMax);
		}

		if ($arParams["NEED_SORT"] != "N")
			uasort($arQuestion["ANSWERS"], "_vote_answer_sort");
		$color = "";

		$sum1 = $sum2 = $sum3 = 0;
		foreach ($arQuestion["ANSWERS"] as $aID => $arAnswer)
		{
			$arResult["LAST_VOTE"] = ($arResult["LAST_VOTE"] === false ? $arAnswer["LAST_VOTE"] : $arResult["LAST_VOTE"]);
			$arResult["LAST_VOTE"] = min($arResult["LAST_VOTE"], $arAnswer["LAST_VOTE"]);
			$arAnswer["PERCENT"] = $arAnswer["PERCENT2"] = $arAnswer["PERCENT3"];
			if ($counterSum > 0)
			{
				$arAnswer["PERCENT"] = $arAnswer["PERCENT2"] = $arAnswer["PERCENT3"] = $percentage = ($arAnswer["COUNTER"]*100/$counter);
				if (is_float($percentage))
				{
					$arAnswer["PERCENT"] = number_format($percentage, 0, ".", "");
					$arAnswer["PERCENT2"] = number_format($percentage, 1, ".", "");
					if ($arAnswer["PERCENT2"] != $percentage)
						$arAnswer["PERCENT3"] = number_format($percentage, 2, ".", "");
				}
				$sum1 += $arAnswer["PERCENT"];
				$sum2 += $arAnswer["PERCENT2"];
				$sum3 += $arAnswer["PERCENT3"];
			}
			$arAnswer["BAR_PERCENT"] = round($arAnswer["PERCENT"]);
			$arAnswer["COLOR"] = (empty($arAnswer["COLOR"]) && ($color = GetNextRGB($color, count($arQuestion["ANSWERS"]))) ?
				$color : TrimEx($arAnswer["COLOR"], "#"));
			$arQuestion["ANSWERS"][$aID] = $arAnswer;
		}

		$var = ($sum1 == 100 ? 1 : ($sum2 == 100 ? 2 : 3));
		if ($var > 1)
		{
			foreach ($arQuestion["ANSWERS"] as $aID => $arAnswer)
			{
				$arQuestion["ANSWERS"][$aID]["PERCENT"] = $arQuestion["ANSWERS"][$aID]["PERCENT".$var];
			}
		}
		$arResult["QUESTIONS"][$qID]["COUNTER_SUM"] = $counterSum;
		$arResult["QUESTIONS"][$qID]["COUNTER_MAX"] = $counterMax;

		//Images
		$arResult["QUESTIONS"][$qID]["IMAGE"] = CFile::GetFileArray($arResult["QUESTIONS"][$qID]["IMAGE_ID"]);

		//Diagram type
		if (!empty($arParams["QUESTION_DIAGRAM_".$qID]) && $arParams["QUESTION_DIAGRAM_".$qID]!="-")
			$arResult["QUESTIONS"][$qID]["DIAGRAM_TYPE"] = trim($arParams["QUESTION_DIAGRAM_".$qID]);

		//Answers
		$arResult["QUESTIONS"][$qID]["ANSWERS"] = $arQuestion["ANSWERS"];
	}

	//Vote Image
	$arResult["VOTE"]["IMAGE"] = CFile::GetFileArray($arResult["VOTE"]["IMAGE_ID"]);

	$obCache->StartDataCache();
	CVoteCacheManager::SetTag($cache_path, array(
		"C" => $arResult["VOTE"]["CHANNEL_ID"],
		"V" => $arResult["VOTE"]["ID"],
		"Q" => array_keys($arResult["QUESTIONS"])));
	$obCache->EndDataCache(array("arResult" => $arResult));
}

$arParams["PERMISSION"] = (($arParams["PERMISSION"] === false && CModule::IncludeModule("vote")) ?
	CVoteChannel::GetGroupPermission($arResult["CHANNEL"]["ID"]) : $arParams["PERMISSION"]);
if ($arParams["PERMISSION"] < 1):
	ShowError(GetMessage("VOTE_ACCESS_DENIED"));
	return false;
endif;

if ($_REQUEST["VOTE_ID"] == $arParams["VOTE_ID"])
{
	$arError = array(); $arNote = array();
	if ($GLOBALS["VOTING_OK"] == "Y" || $_REQUEST["VOTE_SUCCESSFULL"] == "Y")
		$arNote[] = array("id" => "ok", "text" => GetMessage("VOTE_OK"));
	if ($GLOBALS["USER_ALREADY_VOTE"] == "Y")
		$arError[] = array("id" => "already vote", "text" => GetMessage("VOTE_ALREADY_VOTE"));
	if ($GLOBALS["VOTING_LAMP"] == "red")
		$arError[] = array("id" => "red lamp", "text" => GetMessage("VOTE_RED_LAMP"));

	if (!empty($arNote)):
		$e = new CAdminException($arNote);
		$arResult["OK_MESSAGE"] = $e->GetString();
	endif;

	if (!empty($arError)):
		$e = new CAdminException($arError);
		$arResult["ERROR_MESSAGE"] = $e->GetString();
	endif;
}

/********************************************************************
				/Data
********************************************************************/
if ($this->__templateName == "main_page.blue"):
	$this->__templateName = "main_page";
	$arParams["THEME"] = "blue";
elseif ($this->__templateName == "main_page.green"):
	$this->__templateName = "main_page";
	$arParams["THEME"] = "green";
endif;
if ($this->__parent)
	$this->__parent->arResult["VOTING.RESULT"] = array('arParams' => $arParams, 'arResult' => $arResult);
$this->IncludeComponentTemplate();
if (!empty($arParams["RETURN"]))
	return $arParams["RETURN"];
?>