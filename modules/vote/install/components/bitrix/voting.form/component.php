<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/**
 * Bitrix Framework
 * @package bitrix
 * @copyright 2001-2016 Bitrix
 *
 * Bitrix vars
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponentTemplate $this
 * @global CUser $USER
 * @global CMain $APPLICATION
 * @global CBitrixComponent $component
 */

if (!IsModuleInstalled("vote")):
	ShowError(GetMessage("VOTE_MODULE_IS_NOT_INSTALLED"));
	return;
elseif (intval($arParams["VOTE_ID"]) <= 0):
	ShowError(GetMessage("VOTE_EMPTY"));
	return;
endif;

if (!function_exists("_GetAnswerArray1"))
{
	function _GetAnswerArray1($FieldType, $arAnswers)
	{
		$arReturn = Array();
		foreach ($arAnswers as $arAnswer)
		{
			if ($arAnswer["FIELD_TYPE"] == $FieldType)
				$arReturn[] = $arAnswer;
		}
		return $arReturn;
	}
}
/********************************************************************
				Input params
********************************************************************/
/************** BASE ***********************************************/
	$arParams["VOTE_ID"] = intval($arParams["VOTE_ID"]);
	$arParams["PERMISSION"] = (isset($arParams["PERMISSION"]) && ($arParams["PERMISSION"] > 0 || $arParams["PERMISSION"] === 0 ? intval($arParams["PERMISSION"]) : false));
/************** URL ************************************************/
	$URL_NAME_DEFAULT = array(
			"vote_result" => "PAGE_NAME=vote_result&VOTE_ID=#VOTE_ID#");
	foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE):
		if (strlen(trim($arParams[strtoupper($URL)."_TEMPLATE"])) <= 0)
			$arParams[strtoupper($URL)."_TEMPLATE"] = $APPLICATION->GetCurPage()."?".$URL_VALUE;
		$arParams["~".strtoupper($URL)."_TEMPLATE"] = $arParams[strtoupper($URL)."_TEMPLATE"];
		$arParams[strtoupper($URL)."_TEMPLATE"] = htmlspecialcharsbx($arParams["~".strtoupper($URL)."_TEMPLATE"]);
	endforeach;
/************** CACHE **********************************************/
	if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
		$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
	else
		$arParams["CACHE_TIME"] = 0;

	$arParams["ADDITIONAL_CACHE_ID"] = (isset($arParams["ADDITIONAL_CACHE_ID"]) && strlen($arParams["ADDITIONAL_CACHE_ID"]) > 0 ?
		$arParams["ADDITIONAL_CACHE_ID"] : $USER->GetGroups());
/********************************************************************
				/Input params
********************************************************************/
if ($GLOBALS["VOTING_OK"] == "Y"  && ($GLOBALS["VOTING_ID"] == $arParams["VOTE_ID"]))
{
	$strNavQueryString = DeleteParam(array("VOTE_ID", "VOTING_OK", "VOTE_SUCCESSFULL", "view_result", "view_form"));
	$strNavQueryString = ($strNavQueryString <> "" ? "&" : "").$strNavQueryString;
	$delimiter = (strpos($arParams["VOTE_RESULT_TEMPLATE"], "?") === false) ? "?":"&";
	if (strpos($arParams["VOTE_RESULT_TEMPLATE"], "#VOTE_ID#") === false)
	{
		$arParams["VOTE_RESULT_TEMPLATE"] .= $delimiter."VOTE_ID=".$_REQUEST["VOTE_ID"];
		$url = CComponentEngine::makePathFromTemplate(
			$arParams["VOTE_RESULT_TEMPLATE"]."&VOTE_SUCCESSFULL=Y".$strNavQueryString);
	}
	else
	{
		$url = CComponentEngine::makePathFromTemplate(
			$arParams["VOTE_RESULT_TEMPLATE"].$delimiter."VOTE_SUCCESSFULL=Y".$strNavQueryString,
			array("VOTE_ID" => $arParams["VOTE_ID"]));
	}
	LocalRedirect($url);
}
/********************************************************************
				Default values
********************************************************************/
$arResult["URL"] = array(
	"RESULT" => CComponentEngine::MakePathFromTemplate(
		$arParams["VOTE_RESULT_TEMPLATE"].
			(strpos($arParams["VOTE_RESULT_TEMPLATE"], "?") === false ? "?" : "&").
			(strpos($arParams["VOTE_RESULT_TEMPLATE"], "#VOTE_ID#") === false ? "VOTE_ID=#VOTE_ID#&" : "").
			"view_result=Y",
		array("VOTE_ID" => $arParams["VOTE_ID"]))
);

$arResult["OK_MESSAGE"] = "";
$arResult["ERROR_MESSAGE"] = "";

$arResult["CHANNEL"] = array();
$arResult["VOTE"] = array();
$arResult["QUESTIONS"] = array();
$arResult["GROUP_ANSWERS"] = array();

$arResult["~CURRENT_PAGE"] = $APPLICATION->GetCurPageParam("", array("VOTE_ID","VOTING_OK","VOTE_SUCCESSFULL"));
$arResult["CURRENT_PAGE"] = htmlspecialcharsbx($arResult["~CURRENT_PAGE"]);

$arError = array(); $arNote = array();

if ($_REQUEST["VOTE_ID"] == $arParams["VOTE_ID"])
{
	if ($GLOBALS["VOTING_OK"]=="Y" || $_REQUEST["VOTE_SUCCESSFULL"] == "Y")
	{
		$arNote[] = array("id" => "ok", "text" => GetMessage("VOTE_OK"));
	}
	elseif ($GLOBALS["VOTING_OK"] == "N")
	{
		$eO = $APPLICATION->ERROR_STACK + array($APPLICATION->LAST_ERROR);
		$e = reset($eO);
		do {
			if ($e && ($e->GetID()=="CVote::KeepVoting"))
				break;
		} while ($e = next($eO));
		$arError[] = array("id" => "vote error", "text" => ($e ? preg_replace("/\\<br(.*?)\\>/", " ", $e->GetString()) : GetMessage("VOTE_ERROR")));
	}
}
/********************************************************************
				/Default values
********************************************************************/

/********************************************************************
				Data
********************************************************************/
$obCache = new CPHPCache;
$cache_id = "vote_form_".serialize(array($arParams["VOTE_ID"], $arParams["ADDITIONAL_CACHE_ID"],
	(isset($arParams["PERMISSION"]) ? $arParams["PERMISSION"] : array()))).
	((($tzOffset = CTimeZone::GetOffset()) <> 0) ? "_".$tzOffset : "");
$cache_path = str_replace(array(":", "//"), "/", "/".SITE_ID."/".$componentName);

if ($obCache->InitCache($arParams["CACHE_TIME"], $cache_id, $cache_path) && !$_SESSION["VOTE"]["VOTES"][$arParams["VOTE_ID"]])
{
	$arVars = $obCache->GetVars();
	$arResult["VOTE"] = $arVars["arResult"]["VOTE"];
	$arResult["CHANNEL"] = $arVars["arResult"]["CHANNEL"];
	$arResult["QUESTIONS"] = $arVars["arResult"]["QUESTIONS"];
}
elseif (CModule::IncludeModule("vote"))
{
	$tmp = array("bGetMemoStat" => array("bGetMemoStat" => "N", "bRestoreVotedData" => "Y"));
	$arParams["VOTE_ID"] = GetVoteDataByID($arParams["VOTE_ID"],
		$arChannel, $arVote, $arQuestions, $arAnswers,
		$tmp["DropDown"], $tmp["MultiSelect"],
		$tmp["arGroupAnswers"], $tmp["bGetMemoStat"]);
	$permission = ($arParams["PERMISSION"] === false ? CVoteChannel::GetGroupPermission($arChannel["ID"]) : $arParams["PERMISSION"]);

	if ($permission < 2)
	{
		$arError[] = array(
			"id" => "access denied", 
			"text" => GetMessage("VOTE_ACCESS_DENIED"));
	}
	else
	{
		//Vote Image
		$arVote["IMAGE"] = CFile::GetFileArray($arVote["IMAGE_ID"]);
		$arResult["VOTE"] = $arVote;

		$arResult["CHANNEL"] = $arChannel;

		$defaultWidth = "10"; $defaultHeight = "5";
		foreach ($arQuestions as $key => $arQuestion)
		{
			if (empty($arQuestion["ANSWERS"]))
				continue;
			//Images
			$arQuestion["IMAGE"] = CFile::GetFileArray($arQuestion["IMAGE_ID"]);

			$foundMultiselect = $foundDropdown = false;
			$arQs = array();
			foreach ($arQuestion["ANSWERS"] as $keya => $arAnswer)
			{
				$arAnswer += array("DROPDOWN" => array(), "MULTISELECT" => array());
				if ($arAnswer["FIELD_TYPE"] == 2)
				{
					if (!$foundDropdown)
					{
						$arAnswer["DROPDOWN"] = _GetAnswerArray1(2, $arQuestion["ANSWERS"]);
						$arQs[$keya] = $arAnswer;
						$foundDropdown = true;
					}
				}
				elseif ($arAnswer["FIELD_TYPE"] == 3)
				{
					if (!$foundMultiselect)
					{
						$arAnswer["MULTISELECT"] = _GetAnswerArray1(3, $arQuestion["ANSWERS"]);
						$arQs[$keya] = $arAnswer;
						$foundMultiselect = true;
					}
				}
				else
				{
					if ($arAnswer["FIELD_TYPE"] == 4 || $arAnswer["FIELD_TYPE"] == 5)
					{
						$arAnswer["FIELD_WIDTH"] = (!!$arAnswer["FIELD_WIDTH"] ? intval($arAnswer["FIELD_WIDTH"]) : $defaultWidth);
						if ($arAnswer["FIELD_TYPE"] == 5)
							$arAnswer["FIELD_HEIGHT"] = (!!$arAnswer["FIELD_HEIGHT"] ? intval($arAnswer["FIELD_HEIGHT"]) : $defaultHeight);
					}
					$arQs[$keya] = $arAnswer;
				}
			}
			$arQuestion["ANSWERS"] = $arQs;
			$arResult["QUESTIONS"][$key] = $arQuestion;
		}

		$obCache->StartDataCache();
		CVoteCacheManager::SetTag($cache_path, "C", $arChannel["ID"]);
		CVoteCacheManager::SetTag($cache_path, "V", $arVote["ID"]);
		CVoteCacheManager::SetTag($cache_path, "Q", array_keys($arResult["QUESTIONS"]));
		$obCache->EndDataCache(
			array(
				"arResult" => array(
					"VOTE" => $arResult["VOTE"],
					"CHANNEL" => $arResult["CHANNEL"],
					"QUESTIONS" => $arResult["QUESTIONS"]
				)
			)
		);
	}
}

if ($arResult["CHANNEL"]["USE_CAPTCHA"] == "Y" && !$GLOBALS["USER"]->IsAuthorized())
{
	include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/captcha.php");
	$cpt = new CCaptcha();
	$captchaPass = COption::GetOptionString("main", "captcha_password", "");
	if (empty($captchaPass))
	{
		$captchaPass = randString(10);
		COption::SetOptionString("main", "captcha_password", $captchaPass);
	}
	$cpt->SetCodeCrypt($captchaPass);
	$arResult["CAPTCHA_CODE"] = htmlspecialcharsbx($cpt->GetCodeCrypt());
}
if (!empty($arNote)):
	$e = new CAdminException($arNote);
	$arResult["OK_MESSAGE"] = $e->GetString();
endif;
if (!empty($arError)):
	$e = new CAdminException($arError);
	$arResult["ERROR_MESSAGE"] = $e->GetString();
endif;
/********************************************************************
				/Data
********************************************************************/
unset($arQuestions);
unset($arChannel);
unset($arVote);
unset($arAnswers);
unset($arDropDown);
unset($arMultiSelect);
if ($this->__parent)
	$this->__parent->arResult["VOTING.FORM"] = array('arParams' => $arParams, 'arResult' => $arResult);
$this->IncludeComponentTemplate();

//if (!empty($arParams["RETURN"]))
	return $arParams["RETURN"];
?>