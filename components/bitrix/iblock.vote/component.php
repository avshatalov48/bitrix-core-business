<?php
//Следуйте комментариям вида Число* для отслеживания пути исполнения.

//21*
//В случае AJAX запроса попадем сюда
if(!defined("B_PROLOG_INCLUDED") && isset($_REQUEST["AJAX_CALL"]) && $_REQUEST["AJAX_CALL"]=="Y")
{
	define('PUBLIC_AJAX_MODE', true);
	define('NOT_CHECK_PERMISSIONS', true);

	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
	/** @global CMain $APPLICATION */
	global $APPLICATION;

	//22*
	//Проверям: ключ подошел?
	if(CModule::IncludeModule("iblock"))
	{
		$arCache = null;
		if (isset($_REQUEST["SESSION_PARAMS"]) && is_string($_REQUEST["SESSION_PARAMS"]) && $_REQUEST["SESSION_PARAMS"] !== '')
		{
			$arCache = CIBlockRSS::GetCache($_REQUEST["SESSION_PARAMS"]);
		}
		if (is_array($arCache) && ($arCache["VALID"] ?? '') === "Y")
		{
			//23*
			//Да!
			//Забираем параметры "подключения"
			$arParams = [];
			if (CheckSerializedData($arCache["CACHE"]))
			{
				$arParams = unserialize($arCache["CACHE"], ['allowed_classes' => false]);
			}
			if (!is_array($arParams))
			{
				$arParams = [];
			}
			//18*
			//Добиваем теми, которые доступны "снаружи"
			if (!empty($arParams["PAGE_PARAMS"]) && is_array($arParams["PAGE_PARAMS"]))
			{
				foreach ($arParams["PAGE_PARAMS"] as $param_name)
				{
					if (!array_key_exists($param_name, $arParams))
					{
						if (isset($_REQUEST["PAGE_PARAMS"][$param_name]))
						{
							$arParams[$param_name] = $_REQUEST["PAGE_PARAMS"][$param_name];
						}
					}
				}
			}
			//24*
			//Эта магия позволяет нам правильно определить
			//текущий шаблон компонента (с учетом темы)
			if(array_key_exists("PARENT_NAME", $arParams))
			{
				$component = new CBitrixComponent();
				$component->InitComponent($arParams["PARENT_NAME"], $arParams["PARENT_TEMPLATE_NAME"]);
				$component->InitComponentTemplate($arParams["PARENT_TEMPLATE_PAGE"]);
			}
			else
			{
				$component = null;
			}
			//25*
			//Подключаем компонент
			//Результат его работы (div) заменит тот, что сейчас у клиента в браузере
			$arParams["AJAX_CALL"] = "Y";
			$APPLICATION->IncludeComponent($arParams["COMPONENT_NAME"], $arParams["TEMPLATE_NAME"], $arParams, $component);
		}
	}

	CMain::FinalActions();
}

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $componentTemplate */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

/** @global CCacheManager $CACHE_MANAGER */
global $CACHE_MANAGER;

if(!CModule::IncludeModule("iblock"))
{
	ShowError(GetMessage("IBLOCK_MODULE_NOT_INSTALLED"));
	return;
}
/************************************************
	Processing of received parameters
*************************************************/

$arParams['IBLOCK_ID'] ??= 0;
$arParams['ELEMENT_ID'] ??= 0;
$arParams['~ELEMENT_CODE'] ??= '';
$arParams['ELEMENT_CODE'] ??= '';

$arParams['DISPLAY_AS_RATING'] ??= 'rating';
$arParams['READ_ONLY'] ??= '';
$arParams['SHOW_RATING'] ??= 'N';
$arParams['SET_STATUS_404'] ??= 'N';
$arParams['MESSAGE_404'] ??= '';

$bAjax = isset($arParams["AJAX_CALL"]) && $arParams["AJAX_CALL"] == "Y";
$arParams = array(
	"IBLOCK_ID" => (int)$arParams["IBLOCK_ID"],
	"ELEMENT_ID" => (int)$arParams["ELEMENT_ID"],
	"MAX_VOTE" => intval($arParams["MAX_VOTE"])<=0? 5: intval($arParams["MAX_VOTE"]),
	"VOTE_NAMES" => isset($arParams["VOTE_NAMES"]) && is_array($arParams["VOTE_NAMES"])? $arParams["VOTE_NAMES"]: array(),
	"CACHE_TYPE" => $arParams["CACHE_TYPE"],
	"CACHE_TIME" => $arParams["CACHE_TIME"],
	"DISPLAY_AS_RATING" => $arParams["DISPLAY_AS_RATING"]=="vote_avg"? "vote_avg": "rating",
	"READ_ONLY" => $arParams["READ_ONLY"],
	"ELEMENT_CODE" => $arParams["ELEMENT_CODE"],
	"~ELEMENT_CODE" => $arParams["~ELEMENT_CODE"],
	"SHOW_RATING" => $arParams["SHOW_RATING"]=="Y"? "Y": "N",
	"SET_STATUS_404" => $arParams["SET_STATUS_404"]=="Y"? "Y": "N",
	"MESSAGE_404" => $arParams["MESSAGE_404"],
);

//Handle case when ELEMENT_CODE used
if($arParams["ELEMENT_ID"] <= 0)
{
	$arParams["ELEMENT_ID"] = CIBlockFindTools::GetElementID(
		$arParams["ELEMENT_ID"],
		$arParams["~ELEMENT_CODE"],
		false,
		false,
		array(
			"IBLOCK_ID" => $arParams["IBLOCK_ID"],
			"IBLOCK_LID" => SITE_ID,
			"IBLOCK_ACTIVE" => "Y",
			"ACTIVE_DATE" => "Y",
			"CHECK_PERMISSIONS" => "Y",
		)
	);
}
/****************************************
	Any actions without cache
*****************************************/
//26*
//Сюда дошел в том числе и AJAX запрос
if(
	$_SERVER["REQUEST_METHOD"] == "POST"
	&& !empty($_REQUEST["vote"])
	&& ($_REQUEST["AJAX_CALL"]=="Y" || check_bitrix_sessid())
	&& $arParams["READ_ONLY"]!=="Y"
)
{
	if(!is_array($_SESSION["IBLOCK_RATING"]))
		$_SESSION["IBLOCK_RATING"] = Array();
	$RATING = intval($_REQUEST["rating"] ?? 0)+1;
	if($RATING>0 && $RATING<=$arParams["MAX_VOTE"])
	{
		$ELEMENT_ID = intval($_REQUEST["vote_id"]);
		if($ELEMENT_ID>0 && !array_key_exists($ELEMENT_ID, $_SESSION["IBLOCK_RATING"]))
		{
			$_SESSION["IBLOCK_RATING"][$ELEMENT_ID]=true;
			$rsProperties = CIBlockElement::GetProperty($arParams["IBLOCK_ID"], $ELEMENT_ID, "value_id", "asc", array("ACTIVE"=>"Y"));
			$arProperties = array();
			while($arProperty = $rsProperties->Fetch())
			{
				if($arProperty["CODE"]=="vote_count")
					$arProperties["vote_count"] = $arProperty;
				elseif($arProperty["CODE"]=="vote_sum")
					$arProperties["vote_sum"] = $arProperty;
				elseif($arProperty["CODE"]=="rating")
					$arProperties["rating"] = $arProperty;
			}

			$obProperty = new CIBlockProperty;
			$res = true;
			if(!array_key_exists("vote_count", $arProperties))
			{
				$res = $obProperty->Add(array(
					"IBLOCK_ID" => $arParams["IBLOCK_ID"],
					"ACTIVE" => "Y",
					"PROPERTY_TYPE" => "N",
					"MULTIPLE" => "N",
					"NAME" => GetMessage("CC_BIV_VOTE_COUNT"),
					"CODE" => "vote_count",
				));
				if($res)
					$arProperties["vote_count"] = array("VALUE"=>0);
			}
			if($res && !array_key_exists("vote_sum", $arProperties))
			{
				$res = $obProperty->Add(array(
					"IBLOCK_ID" => $arParams["IBLOCK_ID"],
					"ACTIVE" => "Y",
					"PROPERTY_TYPE" => "N",
					"MULTIPLE" => "N",
					"NAME" => GetMessage("CC_BIV_VOTE_SUM"),
					"CODE" => "vote_sum",
				));
				if($res)
					$arProperties["vote_sum"] = array("VALUE"=>0);
			}
			if($res && !array_key_exists("rating", $arProperties))
			{
				$res = $obProperty->Add(array(
					"IBLOCK_ID" => $arParams["IBLOCK_ID"],
					"ACTIVE" => "Y",
					"PROPERTY_TYPE" => "N",
					"MULTIPLE" => "N",
					"NAME" => GetMessage("CC_BIV_VOTE_RATING"),
					"CODE" => "rating",
				));
				if($res)
					$arProperties["rating"] = array("VALUE"=>0);
			}
			if($res)
			{
				$arProperties["vote_count"]["VALUE"] = intval($arProperties["vote_count"]["VALUE"])+1;
				$arProperties["vote_sum"]["VALUE"] = intval($arProperties["vote_sum"]["VALUE"])+$RATING;
				//rating = (SUM(vote)+31.25) / (COUNT(*)+10)
				$arProperties["rating"]["VALUE"] = round(($arProperties["vote_sum"]["VALUE"]+31.25/5*$arParams["MAX_VOTE"])/($arProperties["vote_count"]["VALUE"]+10),2);
				$DB->StartTransaction();
				CIBlockElement::SetPropertyValuesEx($ELEMENT_ID, $arParams["IBLOCK_ID"], array(
					"vote_count" => array(
						"VALUE" => $arProperties["vote_count"]["VALUE"],
						"DESCRIPTION" => $arProperties["vote_count"]["DESCRIPTION"],
					),
					"vote_sum" => array(
						"VALUE" => $arProperties["vote_sum"]["VALUE"],
						"DESCRIPTION" => $arProperties["vote_sum"]["DESCRIPTION"],
					),
					"rating" => array(
						"VALUE" => $arProperties["rating"]["VALUE"],
						"DESCRIPTION" => $arProperties["rating"]["DESCRIPTION"],
					),
				));
				$DB->Commit();
				$this->ClearResultCache(array($USER->GetGroups(), 1));
				$this->ClearResultCache(array($USER->GetGroups(), 0));
				CIBlock::clearIblockTagCache($arParams['IBLOCK_ID']);
			}
		}
	}
	//27*
	//Нам нет необходимости делать редирект для обновления данных
	//в аякс режиме
	//да и не приведет это ни к чему
	if($_REQUEST["AJAX_CALL"]!="Y")
		LocalRedirect(!empty($_REQUEST["back_page"])?$_REQUEST["back_page"]:$APPLICATION->GetCurPageParam());
}
//28*
//Начинаем исполнять "шаблон"

$bVoted = (isset($_SESSION["IBLOCK_RATING"]) && is_array($_SESSION["IBLOCK_RATING"]) && array_key_exists($arParams["ELEMENT_ID"], $_SESSION["IBLOCK_RATING"]))? 1: 0;
if($this->StartResultCache(false, array($USER->GetGroups(), $bVoted)))
{
	if ($bAjax)
		$arParams["AJAX_CALL"] = "Y";

	if($arParams["ELEMENT_ID"]>0)
	{
		//SELECT
		$arSelect = array(
			"ID",
			"IBLOCK_ID",
			"PROPERTY_*",
		);
		//WHERE
		$arFilter = array(
			"ID" => $arParams["ELEMENT_ID"],
			"IBLOCK_ACTIVE" => "Y",
			"IBLOCK_ID" => $arParams["IBLOCK_ID"],
			"CHECK_PERMISSIONS" => "Y",
		);
		//ORDER BY
		$arSort = array(
		);
		//EXECUTE
		$rsElement = CIBlockElement::GetList($arSort, $arFilter, false, false, $arSelect);
		if($obElement = $rsElement->GetNextElement())
		{
			$arResult = $obElement->GetFields();
			$arResult["PROPERTIES"] = $obElement->GetProperties();
		}
		$arResult["BACK_PAGE_URL"] = htmlspecialcharsbx($APPLICATION->GetCurPageParam());
		$arResult["VOTE_NAMES"] = array();
		foreach($arParams["VOTE_NAMES"] as $k=>$v)
		{
			if($v <> '')
				$arResult["VOTE_NAMES"][]=htmlspecialcharsbx($v);
			if(count($arResult["VOTE_NAMES"])>=$arParams["MAX_VOTE"])
				break;
		}
		for($i=0;$i<$arParams["MAX_VOTE"];$i++)
			if(!array_key_exists($i, $arResult["VOTE_NAMES"]))
				$arResult["VOTE_NAMES"][$i]=$i+1;

		$arResult["VOTED"] = $bVoted;

		// Hack for custom templates
		if ($arParams['DISPLAY_AS_RATING'] === 'rating')
		{
			if (!isset($arResult["PROPERTIES"]["rating"]))
			{
				$arResult["PROPERTIES"]["rating"] = [
					'VALUE' => 0,
				];
			}
		}

		$this->SetResultCacheKeys(array(
			"AJAX",
		));
		$this->IncludeComponentTemplate();
	}
	else
	{
		$this->AbortResultCache();
		\Bitrix\Iblock\Component\Tools::process404(
			trim($arParams["MESSAGE_404"]) ?: GetMessage("PHOTO_ELEMENT_NOT_FOUND")
			,true
			,$arParams["SET_STATUS_404"] === "Y"
		);
	}
}

if(array_key_exists("AJAX", $arResult) && ($_REQUEST["AJAX_CALL"] ?? '') !== "Y")
{
	//13*
	//Сохраняем в БД кеш
	if(!isset($_SESSION["iblock.vote"]) || !is_array($_SESSION["iblock.vote"]))
		$_SESSION["iblock.vote"] = array();
	if(!array_key_exists($arResult["AJAX"]["SESSION_KEY"], $_SESSION["iblock.vote"]))
	{
		$arCache = CIBlockRSS::GetCache($arResult["AJAX"]["SESSION_KEY"]);
		if(!$arCache || ($arCache["VALID"] != "Y"))
		{
			CIBlockRSS::UpdateCache($arResult["AJAX"]["SESSION_KEY"], serialize($arResult["AJAX"]["SESSION_PARAMS"]), 24*30, is_array($arCache));
		}
		$_SESSION["iblock.vote"][$arResult["AJAX"]["SESSION_KEY"]] = true;
	}

	if(!defined("ADMIN_SECTION") || (ADMIN_SECTION !== true))
	{
		//14*
		//Подключаем поддержку (библиотеку)
		IncludeAJAX();
	}
	//15*
	//Продолжение экскурсии в файле jscript.php
}
