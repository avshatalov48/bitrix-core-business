<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentName */
/** @var string $componentPath */
/** @var string $componentTemplate */
/** @var string $parentComponentName */
/** @var string $parentComponentPath */
/** @var string $parentComponentTemplate */
$this->setFrameMode(false);

if(!CModule::IncludeModule("subscribe"))
{
	ShowError(GetMessage("SUBSCR_MODULE_NOT_INSTALLED"));
	return;
}

if(!isset($arParams["CACHE_TIME"]))
	$arParams["CACHE_TIME"] = 3600;

if(!isset($arParams["PAGE"]) || strlen($arParams["PAGE"])<=0)
	$arParams["PAGE"] = COption::GetOptionString("subscribe", "subscribe_section")."subscr_edit.php";
$arParams["SHOW_HIDDEN"]=$arParams["SHOW_HIDDEN"]=="Y";
$arParams["SHOW_COUNT"]=$arParams["SHOW_COUNT"]=="Y";
$arParams["SET_TITLE"]=$arParams["SET_TITLE"]!="N";

//get current user subscription from cookies
$arSubscription = CSubscription::GetUserSubscription();

//get user's newsletter categories
$arSubscriptionRubrics = CSubscription::GetRubricArray(intval($aSubscr["ID"]));

//get site's newsletter categories
$obCache = new CPHPCache;
$strCacheID = LANG.$arParams["SHOW_HIDDEN"].$this->GetRelativePath();
if($obCache->StartDataCache($arParams["CACHE_TIME"], $strCacheID, "/".SITE_ID.$this->GetRelativePath()))
{
	$arFilter = array("ACTIVE"=>"Y", "LID"=>LANG);
	if(!$arParams["SHOW_HIDDEN"])
		$arFilter["VISIBLE"]="Y";
	$rsRubric = CRubric::GetList(array("SORT"=>"ASC", "NAME"=>"ASC"), $arFilter);
	$arRubrics = array();
	while($arRubric = $rsRubric->GetNext())
	{
		$arRubric["SUBSCRIBER_COUNT"]=$arParams["SHOW_COUNT"]?CRubric::GetSubscriptionCount($arRubric["ID"]):0;
		$arRubrics[]=$arRubric;
	}
	$obCache->EndDataCache($arRubrics);
}
else
{
	$arRubrics = $obCache->GetVars();
}

if(count($arRubrics)<=0)
{
	ShowError(GetMessage("SUBSCR_NO_RUBRIC_FOUND"));
	return;
}

$arResult["FORM_ACTION"] = htmlspecialcharsbx(str_replace("#SITE_DIR#", LANG_DIR, $arParams["PAGE"]));
$arResult["SHOW_COUNT"] = $arParams["SHOW_COUNT"];

if(strlen($arSubscription["EMAIL"])>0)
	$arResult["EMAIL"] = htmlspecialcharsbx($arSubscription["EMAIL"]);
else
	$arResult["EMAIL"] = htmlspecialcharsbx($USER->GetParam("EMAIL"));

//check whether already authorized
$arResult["SHOW_PASS"] = true;
if($arSubscription["ID"] > 0)
{
	//try to authorize user account's subscription
	if($arSubscription["USER_ID"]>0 && !CSubscription::IsAuthorized($arSubscription["ID"]))
		CSubscription::Authorize($arSubscription["ID"], "");
	//check authorization
	if(CSubscription::IsAuthorized($arSubscription["ID"]))
		$arResult["SHOW_PASS"] = false;
}

$arResult["RUBRICS"] = array();
foreach($arRubrics as $arRubric)
{
	$bChecked = (
		// user is already subscribed
		!is_array($_REQUEST["sf_RUB_ID"]) && in_array($arRubric["ID"], $arSubscriptionRubrics) ||
		// or there is no information about user subscription
		!is_array($_REQUEST["sf_RUB_ID"]) && intval($arSubscription["ID"])==0 ||
		// or user has checked the category and posted the form
		is_array($_REQUEST["sf_RUB_ID"]) && in_array($arRubric["ID"], $_REQUEST["sf_RUB_ID"])
	);

	$arResult["RUBRICS"][]=array(
		"ID"=>$arRubric["ID"],
		"NAME"=>$arRubric["NAME"],
		"DESCRIPTION"=>$arRubric["DESCRIPTION"],
		"CHECKED"=>$bChecked,
		"SUBSCRIBER_COUNT"=>$arRubric["SUBSCRIBER_COUNT"],
	);
}

if($arParams["SET_TITLE"]=="Y")
	$APPLICATION->SetTitle(GetMessage("SUBSCR_PAGE_TITLE"), array('COMPONENT_NAME' => $this->GetName()));

$this->IncludeComponentTemplate();
?>
