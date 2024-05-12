<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if ($_SERVER['REQUEST_METHOD'] == "GET" && isset($_GET["ACTION"]) && $_GET["ACTION"] == "FORUM_SUBSCRIBE"):
/********************************************************************
				Input params
********************************************************************/
/***************** URL *********************************************/
	$res = $arResult;
	$URL_NAME_DEFAULT = array(
			"profile_view" => "PAGE_NAME=profile_view&UID=#UID#",
			"subscr_list" => "PAGE_NAME=subscr_list");
	foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
	{
		if (trim($res["URL_TEMPLATES_".mb_strtoupper($URL)]) == '')
			$res["URL_TEMPLATES_".mb_strtoupper($URL)] = $APPLICATION->GetCurPage()."?".$URL_VALUE;
		$res["~URL_TEMPLATES_".mb_strtoupper($URL)] = $res["URL_TEMPLATES_".mb_strtoupper($URL)];
		$res["URL_TEMPLATES_".mb_strtoupper($URL)] = htmlspecialcharsbx($res["~URL_TEMPLATES_".mb_strtoupper($URL)]);
	}
/***************** ADDITIONAL **************************************/
/********************************************************************
				/Input params
********************************************************************/
$res["URL"] = array(
	"PROFILE" => CComponentEngine::MakePathFromTemplate($res["URL_TEMPLATES_PROFILE_VIEW"], array("UID" => $GLOBALS["USER"]->GetID())),
	"~PROFILE" => CComponentEngine::MakePathFromTemplate($res["~URL_TEMPLATES_PROFILE_VIEW"], array("UID" => $GLOBALS["USER"]->GetID())),
	"SUBSCRIBES" => CComponentEngine::MakePathFromTemplate($res["URL_TEMPLATES_SUBSCR_LIST"], array()),
	"~SUBSCRIBES" => CComponentEngine::MakePathFromTemplate($res["~URL_TEMPLATES_SUBSCR_LIST"], array()));
?>
<div class="forum-header-box">
	<div class="forum-header-title"><span><?=GetMessage("F_TITLE_SUBSCRIBE")?></span></div>
</div>
<div class="forum-info-box forum-subscribes">
	<div class="forum-info-box-inner">
<form class="forum-form" action="<?=POST_FORM_ACTION_URI?>" method="POST">
	<?=bitrix_sessid_post()?>
	<input type="hidden" name="PAGE_NAME" value="list" />
	<input type="hidden" name="FID" value="<?=$arResult["FID"]?>" />
	<?=GetMessage("F_SUBSCRIBE_0")?>: <br />

	<div>
		<input type="radio" name="ACTION" value="FORUM_SUBSCRIBE" id="FORUM_SUBSCRIBE" />
		<label for="FORUM_SUBSCRIBE"><?=GetMessage("F_SUBSCRIBE_1")?></label><br />
		<input type="radio" name="ACTION" value="FORUM_SUBSCRIBE_TOPICS" id="FORUM_SUBSCRIBE_TOPICS" />
		<label for="FORUM_SUBSCRIBE_TOPICS"><?=GetMessage("F_SUBSCRIBE_2")?></label><br />
	</div>
	<div class="forum-group-buttons">
		<input type="submit" value="<?=GetMessage("F_SUBSCRIBE")?>" />
	</div>
</form>
	</div>
</div>
<?
if ($arParams["SET_TITLE"] == "Y"):
	$GLOBALS["APPLICATION"]->SetTitle(GetMessage("F_TITLE_SUBSCRIBE"));
endif;
if ($arParams["SET_NAVIGATION"] != "N"):
	$name = trim($_SESSION["FORUM"]["SHOW_NAME"] == "Y" ? $USER->GetFullName() : "");
	$name = trim(empty($name) ? $USER->GetLogin() : $name);
	$APPLICATION->AddChainItem($name, $res["URL"]["~PROFILE"]);
	$APPLICATION->AddChainItem(GetMessage("F_TITLE_SUBSCRIBE"));
endif;
	return false;
endif;

?><?$APPLICATION->IncludeComponent("bitrix:forum.topic.list", "",
	Array(
		"FID"	=>	$arResult["FID"] ?? null,
		"USE_DESC_PAGE"	=>	$arParams["USE_DESC_PAGE_TOPIC"] ?? null,

		"URL_TEMPLATES_INDEX"	=>	$arResult["URL_TEMPLATES_INDEX"] ?? null,
		"URL_TEMPLATES_FORUMS"	=>	$arResult["URL_TEMPLATES_FORUMS"] ?? null,
		"URL_TEMPLATES_LIST"	=>	$arResult["URL_TEMPLATES_LIST"] ?? null,
		"URL_TEMPLATES_READ"	=>	$arResult["URL_TEMPLATES_READ"] ?? null,
		"URL_TEMPLATES_MESSAGE" =>  $arResult["URL_TEMPLATES_MESSAGE"] ?? null,
		"URL_TEMPLATES_PROFILE_VIEW"	=>	$arResult["URL_TEMPLATES_PROFILE_VIEW"] ?? null,
		"URL_TEMPLATES_MESSAGE_APPR"	=>	$arResult["URL_TEMPLATES_MESSAGE_APPR"] ?? null,
		"URL_TEMPLATES_TOPIC_NEW"	=>	$arResult["URL_TEMPLATES_TOPIC_NEW"] ?? null,
		"URL_TEMPLATES_SUBSCR_LIST"	=>	$arResult["URL_TEMPLATES_SUBSCR_LIST"] ?? null,
		"URL_TEMPLATES_TOPIC_MOVE"	=>	$arResult["URL_TEMPLATES_TOPIC_MOVE"] ?? null,
		"URL_TEMPLATES_RSS" => $arResult["URL_TEMPLATES_RSS"] ?? null,

		"PAGEN" => intval($GLOBALS["NavNum"] + 1),
		"TOPICS_PER_PAGE"	=>	$arParams["TOPICS_PER_PAGE"] ?? null,
		"MESSAGES_PER_PAGE"	=>	$arParams["MESSAGES_PER_PAGE"] ?? null,
		"DATE_FORMAT"	=>	$arParams["DATE_FORMAT"] ?? null,
		"DATE_TIME_FORMAT"	=>	$arParams["DATE_TIME_FORMAT"] ?? null,
		"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"] ?? null,
		"PAGE_NAVIGATION_TEMPLATE" =>  $arParams["PAGE_NAVIGATION_TEMPLATE"] ?? null,
		"PAGE_NAVIGATION_WINDOW" =>  $arParams["PAGE_NAVIGATION_WINDOW"] ?? null,
		"WORD_LENGTH"	=>	$arParams["WORD_LENGTH"] ?? null,
		"SHOW_FORUM_ANOTHER_SITE" => $arParams["SHOW_FORUM_ANOTHER_SITE"] ?? null,
		"SET_NAVIGATION"	=>	$arParams["SET_NAVIGATION"] ?? null,
		"DISPLAY_PANEL" => $arParams["DISPLAY_PANEL"] ?? null,
		"SET_TITLE"	=>	$arParams["SET_TITLE"] ?? null,
		"CACHE_TYPE" => $arParams["CACHE_TYPE"] ?? null,
		"CACHE_TIME" => $arParams["CACHE_TIME"] ?? null,

		"TMPLT_SHOW_ADDITIONAL_MARKER"	=>	$arParams["~TMPLT_SHOW_ADDITIONAL_MARKER"] ?? null,
		"SHOW_RSS" => $arParams["USE_RSS"] ?? null,
		"SHOW_AUTHOR_COLUMN" => $arParams["SHOW_AUTHOR_COLUMN"] ?? null,
		"SEO_USER" => $arParams["SEO_USER"] ?? null
	), $component
);?>
<?
if (in_array("USERS_ONLINE", $arParams["SHOW_STATISTIC_BLOCK"])):
?>
<?$APPLICATION->IncludeComponent("bitrix:forum.statistic", "",
	Array(
		"FID"	=>	$arResult["FID"] ?? null,
		"TID"	=>	0,
		"PERIOD"	=>	$arParams["TIME_INTERVAL_FOR_USER_STAT"] ?? null,
		"SHOW"	=>	array("USERS_ONLINE"),
		"URL_TEMPLATES_PROFILE_VIEW"	=>	$arResult["URL_TEMPLATES_PROFILE_VIEW"] ?? null,

		"CACHE_TYPE" => $arParams["CACHE_TYPE"] ?? null,
		"CACHE_TIME" => $arParams["CACHE_TIME"] ?? null,
		"CACHE_TIME_USER_STAT" => $arParams["CACHE_TIME_USER_STAT"] ?? null,
		"WORD_LENGTH"	=>	$arParams["WORD_LENGTH"] ?? null,
		"WORD_WRAP_CUT" => $arParams["WORD_WRAP_CUT"] ?? null,
		"SEO_USER" => $arParams["SEO_USER"] ?? null,
		"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"] ?? null
	), $component
);?>
<?
endif;
@include_once(str_replace(array("\\", "//"), "/", __DIR__."/footer.php"));
?>
