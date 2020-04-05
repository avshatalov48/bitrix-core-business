<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
if (!$this->__component->__parent || empty($this->__component->__parent->__name)):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/themes/blue/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/styles/additional.css');
endif;
// ************************* Input params***************************************************************
$arParams["AJAX_CALL"] = ($_REQUEST["AJAX_CALL"] == "Y" ? "Y" : "N");
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
// ************************* Input params***************************************************************
if ($arParams["AJAX_CALL"] == "Y"):
	if ($arResult["TOPIC"] == "L")
	{
		?><?=CUtil::PhpToJSObject(array(
			"TOPIC_ID" => $arResult["TID"],
			"TOPIC_TITLE" => '&laquo;<a href="'.$arResult["TOPIC"]["LINK"].'">'.htmlspecialcharsbx($arResult["TOPIC"]["~TITLE"]).
				'</a>&raquo; ( '.GetMessage("FMM_ON_FORUM").': <a href="'.$arResult["FORUM"]["LINK"].'">'.$arResult["FORUM"]["NAME"].'</a>)'));
		?><?
		
	}
	elseif (!empty($arResult["TOPIC"]))
	{
		?><?=CUtil::PhpToJSObject(array(
			"TOPIC_ID" => $arResult["TID"],
			"TOPIC_TITLE" => '&laquo;<a href="'.$arResult["TOPIC"]["LINK"].'">'.htmlspecialcharsbx($arResult["TOPIC"]["~TITLE"]).
				'</a>&raquo; ( '.GetMessage("FMM_ON_FORUM").': <a href="'.$arResult["FORUM"]["LINK"].'">'.$arResult["FORUM"]["NAME"].'</a>)'));
		?><?
	}
	die();
endif;
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=<?=SITE_CHARSET?>" />
	<?$APPLICATION->ShowHead()?>
	<style type=text/css>
		body{background-color:white;}
	</style>
	<title><?=GetMessage("FMM_SEARCH_TITLE")?></title>
</head>
<body class="forum-popup-body">
<?if ($arResult["SELF_CLOSE"] == "Y"):
?><script type="text/javascript"><?
	if (!empty($arResult["TOPIC"])):
	?>
		opener.document.MESSAGES['newTID'].value = '<?=$arResult["TID"]?>';
		opener.document.getElementById('TOPIC_INFO').innerHTML = '<?=CUtil::JSEscape('&laquo;<a href="'.$arResult["TOPIC"]["LINK"].'">'.htmlspecialcharsbx($arResult["TOPIC"]["~TITLE"]).
			'</a>&raquo; ( '.GetMessage("FMM_ON_FORUM").': <a href="'.$arResult["FORUM"]["LINK"].'">'.$arResult["FORUM"]["NAME"].'</a>)')?>';
<?
	endif;
?>
	self.close();
</script>
<?else:

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
					"VALUE" => "topic_search"),
				array(
					"NAME" => "do_search",
					"TYPE" => "HIDDEN",
					"VALUE" => "Y"),
				array(
					"TITLE" => GetMessage("FMM_SEARCH"),
					"NAME" => "search_template",
					"CLASS" => "search-input",
					"TYPE" => "TEXT",
					"VALUE" => $_REQUEST["search_template"]),
				array(
					"TITLE" => GetMessage("F_FORUM"),
					"NAME" => "FID",
					"TYPE" => "SELECT",
					"MULTIPLE" => "N", 
					"CLASS" => "forums-selector-single", 
					"VALUE" => $filter_value_fid,
					"ACTIVE" => $_REQUEST["FID"]),
				array(
					"TITLE" => GetMessage("F_SEARCH_OBJECT"),
					"NAME" => "search_field",
					"TYPE" => "SELECT",
					"VALUE" => 	array("" => GetMessage("FMM_ALL"), "title" => GetMessage("FMM_TITLE"), "description" => GetMessage("FMM_DESCRIPTION")), 
					"ACTIVE" => $_REQUEST["search_field"])),
			"BUTTONS" => array(
				array(
					"NAME" => "s",
					"VALUE" => GetMessage("FMM_SEARCH_GO")))),
			$component,
			array(
				"HIDE_ICONS" => "Y"));?><?
?>
	</div>
</div>
<?
	if ($arResult["SHOW_RESULT"] == "Y"):
if ($arResult["NAV_RESULT"] && $arResult["NAV_RESULT"]->NavPageCount > 0):
?>
<div class="forum-navigation-box forum-navigation-top">
	<div class="forum-page-navigation">
		<?=$arResult["NAV_STRING"]?>
	</div>
	<div class="forum-clear-float"></div>
</div>
<?
endif;
?>
<div class="forum-header-box">
	<div class="forum-header-title"><span><?=GetMessage("FMM_SEARCH_TITLE")?></span></div>
</div>
<?
$iStartNumber = (($arResult["NAV_RESULT"]->NavPageNomer-1)*$arResult["NAV_RESULT"]->NavPageSize);
$iStartNumber = ($iStartNumber > 0 ? $iStartNumber : 1);

?>
<div class="forum-info-box forum-topics">
	<div class="forum-info-box-inner">
	<ol start="<?=$iStartNumber?>">
<?
foreach ($arResult["TOPIC"] as $res):
?>
	<li>
		<a class='tableheadtext' href="<?=$res["topic_id_search"]?>"><?=$res["TITLE"]?></a>
<?
	if (strLen(trim($res["DESCRIPTION"])) > 0)
	{
		?>, <?=$res["DESCRIPTION"]?><?
	}
?>
	</li>
<?
endforeach;
?>
	</ol>
	</div>
</div>
<?
if ($arResult["NAV_RESULT"] && $arResult["NAV_RESULT"]->NavPageCount > 0):
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
endif;
?>
</body>
</html>
<?
die();
?>
