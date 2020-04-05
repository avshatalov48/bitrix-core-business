<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!$this->__component->__parent || empty($this->__component->__parent->__name)):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/themes/blue/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/styles/additional.css');
endif;
/********************************************************************
				Input params
********************************************************************/
$arParams["SEO_USER"] = (in_array($arParams["SEO_USER"], array("Y", "N", "TEXT")) ? $arParams["SEO_USER"] : "Y");
$arParams["USER_TMPL"] = '<noindex><a rel="nofollow" href="#URL#" title="'.GetMessage("F_USER_PROFILE").'">#NAME#</a></noindex>';
if ($arParams["SEO_USER"] == "N") $arParams["USER_TMPL"] = '<a href="#URL#" title="'.GetMessage("F_USER_PROFILE").'">#NAME#</a>';
elseif ($arParams["SEO_USER"] == "TEXT") $arParams["USER_TMPL"] = '#NAME#';
/********************************************************************
				/Input params
********************************************************************/

if (in_array("USERS_ONLINE", $arParams["SHOW"]))
{
	$arMsg = array();
	if (!empty($arResult["GUEST"]))
		$arMsg[] = GetMessage("F_NOW_ONLINE_1", array("#GUESTS#" => "<span>".intVal($arResult["GUEST"])."</span>"));
	if (!empty($arResult["REGISTER"]))
		$arMsg[] = GetMessage("F_NOW_ONLINE_2", array("#USERS#" => "<span>".intVal($arResult["REGISTER"])."</span>"));
	if (!empty($arResult["USERS_HIDDEN"]))
		$arMsg[] = GetMessage("F_NOW_ONLINE_3", array("#HIDDEN_USERS#" => "<span>".count($arResult["USERS_HIDDEN"])."</span>"));

	$text = ($arParams["TID"] > 0 ? GetMessage("F_NOW_TOPIC_READ") : GetMessage("F_NOW_FORUM")).
		(!empty($arMsg) ? " (".implode(", ", $arMsg).") " : "");
?>
<div class="forum-info-box forum-users-online">
	<div class="forum-info-box-inner">
		<span class="forum-users-online"><?=$text?></span><?
$first = true;
foreach ($arResult["USERS"] as $res)
{
	if($arParams["WORD_WRAP_CUT"] > 0 && strLen($res["~SHOW_NAME"])>$arParams["WORD_WRAP_CUT"])
		$res["SHOW_NAME"] = htmlspecialcharsbx(subStr($res["~SHOW_NAME"], 0, $arParams["WORD_WRAP_CUT"]))."...";
	?><?=(!$first ? ", ": "")?><span class="forum-user-online"><?
		?><?=str_replace(array("#URL#", "#NAME#"), array($res["profile_view"], $res["SHOW_NAME"]), $arParams["USER_TMPL"])
	?></span><?
	$first = false;
}
if (CForumUser::IsAdmin() && !empty($arResult["USERS_HIDDEN"]))
{
	foreach ($arResult["USERS_HIDDEN"] as $res)
	{
		if($arParams["WORD_WRAP_CUT"] > 0 && strLen($res["~SHOW_NAME"])>$arParams["WORD_WRAP_CUT"])
			$res["SHOW_NAME"] = htmlspecialcharsbx(subStr($res["~SHOW_NAME"], 0, $arParams["WORD_WRAP_CUT"]))."...";
		?><?=(!$first ? ", ": "")?><span class="forum-user-online-hidden"><?
			?><?=str_replace(array("#URL#", "#NAME#"), array($res["profile_view"], $res["SHOW_NAME"]), $arParams["USER_TMPL"])
		?></span><?
		$first = false;
	}
}
		?>
	</div>
</div>
<?
}

if (in_array("BIRTHDAY", $arParams["SHOW"]) && !empty($arResult["USERS_BIRTHDAY"])):
?>
<div class="forum-info-box forum-users-birthday">
	<div class="forum-info-box-inner">
		<span class="forum-users-birthday"><?=GetMessage("F_TODAY_BIRTHDAY")?> <?
$first = true;
foreach ($arResult["USERS_BIRTHDAY"] as $res)
{
	?><?=((!$first)? ", ":"")?><?
	?><?=str_replace(array("#URL#", "#NAME#"), array($res["profile_view"], $res["SHOW_NAME"]), $arParams["USER_TMPL"])
	?>(<span><?=$res["AGE"]?></span>)<?
	$first = false;
}
		?></span>
	</div>
</div>
<?
endif;

if (in_array("STATISTIC", $arParams["SHOW"])):
?>
<div class="forum-info-box forum-statistics">
	<div class="forum-info-box-inner">
<?
	if (empty($arParams["FID"])):
?>
		<div class="forum-statistics-allusers"><?=GetMessage("F_REGISTER_USERS")?>:&nbsp;<span><?=intVal($arResult["STATISTIC"]["USERS_ON_FORUM"])?></span></div>
		<div class="forum-statistics-users"><?=GetMessage("F_ACTIVE_USERS")?>:&nbsp;<span><?=intVal($arResult["STATISTIC"]["USERS_ON_FORUM_ACTIVE"])?></span></div>
<?/*?>		<div class="forum-statistics-forums"><?=GetMessage("F_FORUMS_ALL")?>:&nbsp;<span><?=$arResult["STATISTIC"]["FORUMS"]?></span></div><?*/?>
<?
	endif;
?>
		<div class="forum-statistics-topics"><?=GetMessage("F_TOPICS_ALL")?>:&nbsp;<span><?=intVal($arResult["STATISTIC"]["TOPICS"])?></span></div>
		<div class="forum-statistics-replies"><?=GetMessage("F_POSTS_ALL")?>:&nbsp;<span><?=intVal($arResult["STATISTIC"]["POSTS"])?></span></div>
		<div class="forum-clear-float"></div>
	</div>
	
</div>
<?
endif;
?>