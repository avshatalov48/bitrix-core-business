<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!$this->__component->__parent || empty($this->__component->__parent->__name)):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/themes/blue/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/styles/additional.css');
endif;
/********************************************************************
				Input params
********************************************************************/
/***************** BASE ********************************************/
$arParams["SHOW_TAGS"] = ($arParams["SHOW_TAGS"] == "N" ? "N" : "Y");
$res =  $_COOKIE[COption::GetOptionString("main", "cookie_name", "BITRIX_SM")."_FORUM_INFO"];
$arResult["USER"] = array(
	"SHOW_FILTER" => (strpos($res, "searchf=Y") !== false ? "Y" : "N")); 
if ($arResult["USER"]["SHOW_FILTER"] == "N")
{
	$arResult["USER"]["SHOW_FILTER"] = (!empty($_REQUEST["FORUM_ID"]) || !empty($_REQUEST["DATE_CHANGE"]) || 
		$_REQUEST["order"] != "relevance" ? "Y" : "N");
}
/********************************************************************
				/Input params
********************************************************************/
	$filter_value_fid = array(
		"0" => GetMessage("F_ALL_FORUMS"), 
		"separator" => array("NAME" => " ", "TYPE" => "OPTGROUP"));
if (is_array($arResult["GROUPS_FORUMS"])):
	foreach ($arResult["GROUPS_FORUMS"] as $key => $res):
		if ($res["TYPE"] == "GROUP"):
			$filter_value_fid["GROUP_".$res["ID"]] = array(
				"NAME" => str_pad("", ($res["DEPTH"] - 1)*6, "&nbsp;").$res["~NAME"], 
				"CLASS" => "forums-selector-optgroup level".$res["DEPTH"], 
				"TYPE" => "OPTGROUP");
		else:
			$filter_value_fid[$res["ID"]] = array(
				"NAME" => ($res["DEPTH"] > 0 ? str_pad("", $res["DEPTH"]*6, "&nbsp;")."&nbsp;" : "").$res["~NAME"], 
				"CLASS" => "forums-selector-option level".$res["DEPTH"], 
				"TYPE" => "OPTION");
		endif;
	endforeach;
endif;
?>
<div class="forum-info-box forum-filter">
	<div class="forum-info-box-inner">
<?
	$APPLICATION->IncludeComponent("bitrix:forum.interface", "filter_simple", 
		array(
			"FORM_METHOD_GET" => 'Y',
			"FIELDS" => array(
				array(
					"NAME" => "PAGE_NAME",
					"TYPE" => "HIDDEN",
					"VALUE" => "search"),
				array(
					"NAME" => "tags",
					"TYPE" => "HIDDEN",
					"VALUE" => $_REQUEST["tags"]),
				array(
					"TITLE" => GetMessage("F_KEYWORDS"),
					"NAME" => "q",
					"CLASS" => "search-input", 
					"TYPE" => "TEXT",
					"VALUE" => $_REQUEST["q"]),
				array(
					"TITLE" => GetMessage("F_FORUM"),
					"NAME" => "FORUM_ID[]",
					"TYPE" => "SELECT",
					"MULTIPLE" => "Y", 
					"CLASS" => "forums-selector-multiple forum-filter-forums", 
					"VALUE" => $filter_value_fid,
					"ACTIVE" => $_REQUEST["FORUM_ID"]),
				array(
					"TITLE" => GetMessage("F_INTERVAL"),
					"NAME" => "DATE_CHANGE",
					"TYPE" => "SELECT",
					"VALUE" => 	array("0" => GetMessage("F_INTERVAL_ALL"), "1" => GetMessage("F_INTERVAL_TODAY"), "7" => "7 ".GetMessage("F_INTERVAL_DAYS"), 
						"30" => "30 ".GetMessage("F_INTERVAL_DAYS"), "60" => "60 ".GetMessage("F_INTERVAL_DAYS"), "90" => "90 ".GetMessage("F_INTERVAL_DAYS"), 
						"180" => "180 ".GetMessage("F_INTERVAL_DAYS"), "365" => "365 ".GetMessage("F_INTERVAL_DAYS")), 
					"ACTIVE" => $_REQUEST["DATE_CHANGE"]),
				array(
					"TITLE" => GetMessage("F_SORT"),
					"NAME" => "order",
					"TYPE" => "SELECT",
					"VALUE" => 	array("relevance" => GetMessage("F_RELEVANCE"), "date" => GetMessage("F_DATE"), "topic" => GetMessage("F_TOPIC")), 
					"ACTIVE" => $_REQUEST["order"])),
			"BUTTONS" => array(
				array(
					"NAME" => "s",
					"VALUE" => GetMessage("F_DO_SEARCH")))),
			$component,
			array(
				"HIDE_ICONS" => "Y"));?><?
?>
	</div>
</div>

<br/>
<?
if ($arResult["NAV_RESULT"] && $arResult["NAV_RESULT"]->NavPageCount > 0):
?><div class="forum-navigation-box forum-navigation-top">
	<div class="forum-page-navigation">
		<?=$arResult["NAV_STRING"]?>
	</div>
	<div class="forum-clear-float"></div>
</div>
<?
endif;

if ($_GET["show_help"] == "Y" || $arResult["ERROR_MESSAGE"] != "" || $arResult["EMPTY"] == "Y" || $arResult["SHOW_RESULT"] != "N"):
?>
<div class="forum-header-box">
	<div class="forum-header-title"><span><?=GetMessage("F_TITLE")?></span></div>
</div>
<?
endif;

if ($_GET["show_help"] == "Y"):
?>
<div class="forum-info-box forum-search-help">
	<div class="forum-info-box-inner">
	<?=GetMessage("F_PHRASE_ERROR_CORRECT")?><br />
	<?=GetMessage("F_PHRASE_ERROR_SYNTAX")?><br />
	<?=GetMessage("F_SEARCH_DESCR")?>
	</div>
</div>
<?
elseif ($arResult["ERROR_MESSAGE"] != ""):
?>
<div class="forum-info-box forum-search-help">
	<div class="forum-info-box-inner">
<?
if (!empty($arResult["ERROR_MESSAGE"])): 
?>
<div class="forum-note-box forum-note-error">
	<div class="forum-note-box-text"><?=ShowError($arResult["ERROR_MESSAGE"], "forum-note-error");?></div>
</div>
<?
endif;
?>
		<?=GetMessage("F_PHRASE_ERROR_CORRECT")?><br />
		<?=GetMessage("F_PHRASE_ERROR_SYNTAX")?><br />
		<?=GetMessage("F_SEARCH_DESCR")?>
	</div>
</div>
<?
elseif ($arResult["EMPTY"] == "Y"):
?>
<div class="forum-info-box forum-search-help">
	<div class="forum-info-box-inner">
		<?=ShowNote(GetMessage("F_EMPTY"), "forum-note")?>
	</div>
</div>
<?
elseif ($arResult["SHOW_RESULT"] != "N"):
?>
<div class="forum-block-container forum-search-block-container">
	<div class="forum-block-outer">
		<div class="forum-block-inner"><?
	$iNumber = 0; $iCount = count($arResult["TOPICS"]);
	foreach ($arResult["TOPICS"] as $res):
	$iNumber++;
			?><div class="forum-info-box <?
				?><?=($iNumber%2 == 1 ? "forum-info-box-odd " : "forum-info-box-even ")?><?
				?><?=($iNumber == 1 ? "forum-info-box-first " : "")?><?
				?><?=($iNumber == $iCount ? "forum-info-box-last" : "")?>">
				<div class="forum-info-box-inner">
					<noindex><a href="<?=$res["URL"]?>" class="forum-name" rel="nofollow"><?=$res["TITLE_FORMATED"]?></a></noindex>
					<div class="forum-text"><?=$res["BODY_FORMATED"]?></div>

<?
		if (!empty($res["TAGS"])):
?>
						<div class="forum-tags"><?=GetMessage("F_TAGS")?>: <?
							$first = true;
							foreach ($res["TAGS"] as $tags):
								if (!$first)
								{
									?>, <?
								}
								?><a href="<?=$tags["URL"]?>"><?=$tags["TAG_NAME"]?></a><?
								$first = false;
							endforeach;
?>
						</div>
<?
		endif;
?>
						<div class="forum-date"><?=GetMessage("F_CHANGE")?> <?=$res["DATE_CHANGE"]?></div>
<?
		if ($res["~URL"] != $res["SITE_URL"]):
?>
						<?=str_replace(array("#MESSAGE_URL#", "#SITE_URL#"), 
							array($res["URL"], $res["SITE_URL"]), GetMessage("F_DIFF_URLS"))?><br />
<?
		endif;
?>
				</div>
			</div><?
	endforeach;
		?></div>
	</div>
</div>
<?
if ($arResult["NAV_RESULT"]->NavPageCount > 0):
?>
<div class="forum-navigation-box forum-navigation-bottom">
	<div class="forum-page-navigation">
		<?=$arResult["NAV_STRING"]?>
	</div>
	<div class="forum-clear-float"></div>
</div>
<?
endif;

endif;
?>
