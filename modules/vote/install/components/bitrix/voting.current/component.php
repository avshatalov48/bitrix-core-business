<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
if (!CModule::IncludeModule("vote")):
	ShowError(GetMessage("VOTE_MODULE_IS_NOT_INSTALLED"));
	return;
endif;
$this->setFrameMode(false);
/********************************************************************
				Input params
********************************************************************/
/************** BASE ***********************************************/
	$arParams["VOTE_ID"] = (isset($arParams["VOTE_ID"]) && !empty($arParams["VOTE_ID"]) ? intval($arParams["VOTE_ID"]) : false);
	$arParams["CHANNEL_SID"] = trim($arParams["CHANNEL_SID"]);
	$arParams["PERMISSION"] = (isset($arParams["PERMISSION"]) && ($arParams["PERMISSION"] > 0 || $arParams["PERMISSION"] === 0) ?
		intval($arParams["PERMISSION"]) : false);
/************** ADDITIONAL *****************************************/
	$arParams["SHOW_RESULTS"] = ($arParams["SHOW_RESULTS"] == "Y" ? "Y" : "N");

/************** CACHE **********************************************/
if (!isset($arParams["CACHE_TIME"]))
	$arParams["CACHE_TIME"] = 3600;
if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
	$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
else
	$arParams["CACHE_TIME"] = 0;
/********************************************************************
				/Input params
********************************************************************/


$obCache = new CPHPCache;
$cache_path = str_replace(array(":", "//"), "/", "/".SITE_ID."/".$componentName."/".$arParams["CHANNEL_SID"]."/");
$cache_id = "vote_current_".serialize($arParams).(($tzOffset = CTimeZone::GetOffset()) <> 0 ? "_".$tzOffset : "");

if (!$obCache->InitCache($arParams["CACHE_TIME"], $cache_id, $cache_path))
{
	$db_res = false;
	if ($arParams["VOTE_ID"] > 0)
	{
		$db_res = CVote::GetByIDEx($arParams["VOTE_ID"]);
	}
	else
	{
		$obChannel = CVoteChannel::GetList($by, $order,
			array("SID"=> $arParams["CHANNEL_SID"], "SID_EXACT_MATCH" => "Y", "SITE" => SITE_ID, "ACTIVE" => "Y", "HIDDEN" => "N"), $is_filtered);
		if ($obChannel && ($arChannel = $obChannel->Fetch()))
		{
			$db_res = CVote::GetList($by, $order, array("CHANNEL_ID"=>$arChannel["ID"], "LAMP" => "green"), $is_filtered);
		}
	}
	$arVote = ($db_res ? $db_res->Fetch() : array());
	if (empty($arVote) || $arVote["CHANNEL_ACTIVE"] != "Y" || $arVote["ACTIVE"] != "Y")
		return false;

	$arResult = array(
		"VOTE" => $arVote,
		"VOTE_ID" => $arVote["ID"],
		"VOTE_RESULT_TEMPLATE" => $APPLICATION->GetCurPageParam("", array("VOTE_SUCCESSFULL", "VOTE_ID", "view_form")),
		"ADDITIONAL_CACHE_ID" => "current_vote");
	$obCache->StartDataCache();
	CVoteCacheManager::SetTag($cache_path, array("C" => $arVote["CHANNEL_ID"], "V" => $arVote["ID"]));
	$obCache->EndDataCache(array("arResult" => $arResult));
}
else
{
	$arVars = $obCache->GetVars();
	$arResult = $arVars["arResult"];
	$this->SetTemplateCachedData($arVars["templateCachedData"]);
}
$arParams["PERMISSION"] = ($arParams["PERMISSION"] === false ? CVoteChannel::GetGroupPermission($arResult["VOTE"]["CHANNEL_ID"]) : $arParams["PERMISSION"]);
if ($arParams["PERMISSION"] <= 0)
{
	return false;
}
elseif ($GLOBALS["VOTING_OK"] == "Y" && $GLOBALS["VOTING_ID"] == $arParams["VOTE_ID"] && !empty($arParams["VOTE_RESULT_TEMPLATE"]))
{
	$var = array("VOTE_ID", "VOTING_OK", "VOTE_SUCCESSFULL", "view_result", "view_form");
	$url = CComponentEngine::MakePathFromTemplate($arParams["VOTE_RESULT_TEMPLATE"], array("VOTE_ID" => $arResult["VOTE"]["ID"]));
	if (strpos($url, "?") === false)
	{
		$url .= "?";
	}
	elseif (($token = substr($url, (strpos($url, "?") + 1))) && !empty($token) &&
		preg_match_all("/(?<=^|\&)\w+(?=$|\=)/is", $token, $matches))
	{
		$var = array_merge($var, $matches[0]);
	}
	$strNavQueryString = DeleteParam($var);
	LocalRedirect($url."&VOTE_SUCCESSFULL=Y&VOTE_ID=".intval($_REQUEST["VOTE_ID"]).($strNavQueryString <> "" ? "&" : "").$strNavQueryString);
}
else if ($arParams["PERMISSION"] >= 4 && $arParams["VOTE_ID"] > 0 && check_bitrix_sessid())
{
	if ($this->request->getPost("stopVoting") == $arParams["VOTE_ID"])
	{
		\Bitrix\Vote\Vote::loadFromId($arParams["VOTE_ID"])->stop();
		$arResult["VOTE"]["LAMP"] = "red";
	}
	else if ($this->request->getPost("resumeVoting") == $arParams["VOTE_ID"])
	{
		\Bitrix\Vote\Vote::loadFromId($arParams["VOTE_ID"])->resume();
		$arResult["VOTE"]["LAMP"] = "green";
	}
	else if ($this->request->getQuery("exportVoting") == $arParams["VOTE_ID"])
	{
		\Bitrix\Vote\Vote::loadFromId($arParams["VOTE_ID"])->exportExcel();
	}
}
$arParams["VOTED"] = \Bitrix\Vote\User::getCurrent()->isVotedFor($arResult["VOTE"]["ID"]);
$isUserCanVote = ($arParams["VOTED"] == false);
$arParams["CAN_VOTE"] = $arResult["CAN_VOTE"] = ($isUserCanVote && $arParams["PERMISSION"] > 1 ? "Y" : "N");
$arParams["CAN_REVOTE"] = ($arParams["VOTED"] == 8 && $USER->IsAuthorized() && $arParams["PERMISSION"] > 1 ? "Y" : "N");
$bShowResult = ($arResult["VOTE"]["LAMP"] != "green" || ($arParams["CAN_VOTE"] != "Y" && $arParams["CAN_REVOTE"] != "Y"));

if (!$bShowResult)
{
	$bShowResult = ($_REQUEST["view_result"] == "Y" ||
		$GLOBALS["VOTING_OK"] == "Y" && $GLOBALS["VOTING_ID"] == $arResult["VOTE_ID"] ||
		$GLOBALS["USER_ALREADY_VOTE"] == "Y" && $arParams["CAN_REVOTE"] != "Y" ||
		$_REQUEST["VOTE_SUCCESSFULL"] == "Y" && $_REQUEST["VOTE_ID"] == $arResult["VOTE_ID"]);
	if ($_REQUEST["view_form"] == "Y")
		$bShowResult = false;
	else if (!$bShowResult)
	{
		$bShowResult = ($arParams["CAN_REVOTE"] == "Y");
		if ($bShowResult && $GLOBALS["VOTING_ID"] == $arResult["VOTE"]["ID"] && $GLOBALS["VOTING_OK"] != "Y")
			$bShowResult = false;
	}
}
$componentPage = ($bShowResult ? "result" : "form");

$this->IncludeComponentTemplate($componentPage);
?>