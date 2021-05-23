<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if(!CModule::IncludeModule("subscribe"))
{
	ShowError(GetMessage("CC_BSS_MODULE_NOT_INSTALLED"));
	return;
}

if(!isset($arParams["CACHE_TIME"]))
	$arParams["CACHE_TIME"] = 3600;
if($arParams["SHOW_HIDDEN"]!="Y")
	$arParams["SHOW_HIDDEN"] = "N";
if($arParams["SET_TITLE"]!="N")
	$arParams["SET_TITLE"] = "Y";

$obSubscription = new CSubscription;
$arResult["ERRORS"] = array();
$bVarsFromForm = false;

if(is_object($USER) && $USER->GetID() > 0)
{
	$USER_ID = $USER->GetID();
}
else
{
	ShowError(GetMessage("CC_BSS_NOT_AUTHORIZED"));
	return;
}

if($USER_ID && $_SERVER["REQUEST_METHOD"] == "POST" && array_key_exists("Update", $_POST) && check_bitrix_sessid())
{
	//Find out what rubrics was chosen by the user
	$arNewRubrics = array();
	if(is_array($_POST["RUB_ID"]))
	{
		foreach($_POST["RUB_ID"] as $rub_id)
		{
			$rub_id = intval($rub_id);
			if($rub_id > 0)
				$arNewRubrics[$rub_id] = $rub_id;
		}
	}

	//Get his subscription
	$rsSubscription = $obSubscription->GetList(array(), array("USER_ID" => $USER_ID));
	$arSubscription = $rsSubscription->Fetch();

	//And when hidden rubrics and rubrics from another site
	//not displayed we'll save their subscription (if exists)
	if(is_array($arSubscription))
	{
		$rsRubrics = CSubscription::GetRubricList($arSubscription["ID"]);
		while($arRubric = $rsRubrics->Fetch())
		{
			if($arRubric["LID"] != SITE_ID)
			{
				$arNewRubrics[$arRubric["ID"]] = $arRubric["ID"];
			}
			else
			{
				if($arParams["SHOW_HIDDEN"] == "N" && $arRubric["VISIBLE"] == "N")
					$arNewRubrics[$arRubric["ID"]] = $arRubric["ID"];
			}
		}
	}

	//No rubrics was checked so delete subscription
	if(count($arNewRubrics) <= 0)
	{
		if(is_array($arSubscription))
		{
			$rs = $obSubscription->Delete($arSubscription["ID"]);

			if(!$rs)
				$arResult["ERRORS"][] = GetMessage("CC_BSS_DELETE_ERROR");
			else
				$_SESSION["subscribe.simple.message"] = GetMessage("CC_BSS_UPDATE_SUCCESS");
		}
	}
	else //Add or change
	{
		if(is_array($arSubscription))
		{
			$rs = $obSubscription->Update(
				$arSubscription["ID"],
				array(
					"FORMAT" => ($_POST["FORMAT"] !== "html"? "text": "html"),
					"RUB_ID" => $arNewRubrics,
				),
				false
			);

			if(!$rs)
				$arResult["ERRORS"][] = $obSubscription->LAST_ERROR;
			else
				$_SESSION["subscribe.simple.message"] = GetMessage("CC_BSS_UPDATE_SUCCESS");
		}
		else
		{
			$ID = $obSubscription->Add(array(
				"USER_ID" => $USER_ID,
				"ACTIVE" => "Y",
				"EMAIL" => $USER->GetEmail(),
				"FORMAT" => ($_POST["FORMAT"] !== "html"? "text": "html"),
				"CONFIRMED" => "Y",
				"SEND_CONFIRM" => "N",
				"RUB_ID" => $arNewRubrics,
			));

			if(!$ID)
				$arResult["ERRORS"][] = $obSubscription->LAST_ERROR;
			else
				$_SESSION["subscribe.simple.message"] = GetMessage("CC_BSS_UPDATE_SUCCESS");
		}
	}

	if(count($arResult["ERRORS"]) <= 0)
	{
		LocalRedirect($APPLICATION->GetCurPageParam());
	}
	else
	{
		$bVarsFromForm = true;
	}
}

if(array_key_exists("subscribe.simple.message", $_SESSION))
{
	$arResult["MESSAGE"] = $_SESSION["subscribe.simple.message"];
	unset($_SESSION["subscribe.simple.message"]);
}
else
{
	$arResult["MESSAGE"] = "";
}

if($arParams["SET_TITLE"]=="Y")
	$APPLICATION->SetTitle(GetMessage("CC_BSS_TITLE"), array('COMPONENT_NAME' => $this->GetName()));

$arResult["FORM_ACTION"] = $APPLICATION->GetCurPage();

$arResult["FORMAT"] = false;
$arResult["RUB_ID"] = array();
if($bVarsFromForm)
{
	$arResult["FORMAT"] = $_POST["FORMAT"] == "html"? "html": "text";
	$arResult["RUB_ID"] = $arNewRubrics;
}
elseif($USER_ID)
{
	$rsSubscription = $obSubscription->GetList(array(), array("USER_ID" => $USER_ID));
	$arSubscription = $rsSubscription->Fetch();
	if($arSubscription)
	{
		$arResult["FORMAT"] = $arSubscription["FORMAT"];
		$rsRubrics = CSubscription::GetRubricList($arSubscription["ID"]);
		while($arRubric = $rsRubrics->Fetch())
			$arResult["RUB_ID"][$arRubric["ID"]] = $arRubric["ID"];
	}
}

$obCache = new CPHPCache;
$strCacheID = LANG.$arParams["SHOW_HIDDEN"].$this->GetRelativePath();
if($obCache->StartDataCache($arParams["CACHE_TIME"], $strCacheID, "/".SITE_ID.$this->GetRelativePath()))
{
	$arFilter = array(
		"ACTIVE" => "Y",
		"LID" => SITE_ID,
	);
	if($arParams["SHOW_HIDDEN"] == "N")
		$arFilter["VISIBLE"] = "Y";

	$rsRubrics = CRubric::GetList(array("SORT"=>"ASC", "NAME"=>"ASC"), $arFilter);
	$arRubrics = array();
	while($arRubric = $rsRubrics->GetNext())
	{
		$arRubrics[] = $arRubric;
	}
	$obCache->EndDataCache($arRubrics);
}
else
{
	$arRubrics = $obCache->GetVars();
}

$arResult["RUBRICS"] = array();
foreach($arRubrics as $arRubric)
{
	$arResult["RUBRICS"][] = array(
		"ID" => $arRubric["ID"],
		"NAME" => $arRubric["NAME"],
		"DESCRIPTION" => $arRubric["DESCRIPTION"],
		"CHECKED" => array_key_exists($arRubric["ID"], $arResult["RUB_ID"]),
	);
}

$this->IncludeComponentTemplate();
?>
