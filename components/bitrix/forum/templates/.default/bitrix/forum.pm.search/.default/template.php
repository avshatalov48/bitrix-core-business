<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$arParams["SEO_USER"] = (in_array($arParams["SEO_USER"], array("Y", "N", "TEXT")) ? $arParams["SEO_USER"] : "Y");
$arParams["USER_TMPL"] = '<noindex><a rel="nofollow" href="#URL#" title="'.GetMessage("F_USER_PROFILE").'">#NAME#</a></noindex>';
if ($arParams["SEO_USER"] == "N") $arParams["USER_TMPL"] = '<a href="#URL#" title="'.GetMessage("F_USER_PROFILE").'">#NAME#</a>';
elseif ($arParams["SEO_USER"] == "TEXT") $arParams["USER_TMPL"] = '#NAME#';

if ($arResult["SHOW_SELF_CLOSE"] == "Y")
{
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"><html><body>
<script type="text/javascript">
<?=($_REQUEST["search_insert"] == "Y" ? "opener" : "top")?>.document.getElementById("div_USER_ID").innerHTML = '<?=(
	$arResult["SHOW_MODE"] == "none" ?
		"<i>".GetMessageJS("PM_NOT_FINED")."</i>" : (
		$arResult["SHOW_MODE"] == "light" ?
			GetMessageJS("PM_IS_FINED") :
			"[".Cutil::JSEscape(str_replace(array("#URL#", "#NAME#"), array($arResult["profile_view"], $arResult["SHOW_NAME"]), $arParams["USER_TMPL"]))."]"
		)
)?>';
<?=($_REQUEST["search_insert"] == "Y" ? "opener" : "top")?>.document.getElementById('USER_ID').value = '<?=$arResult["UID"]?>';
self.close();
</script>
</body>
</html>
<?
	die();
}

if (!$this->__component->__parent || empty($this->__component->__parent->__name)):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/themes/blue/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/forum/templates/.default/styles/additional.css');
endif;
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"><html>
<head>
	<meta  http-equiv="Content-Type" content="text/html; charset='<?=$arResult["SITE_CHARSET"]?>'">
	<title><?=GetMessage("PM_TITLE")?></title>
	<?$APPLICATION->ShowHead()?>
	<style type=text/css>
		body{background-color:white;}
		div.forum-pmessage-search-user label{
			width:4em;}
	</style>
</head>
<body class="forum-popup-body">
<div class="forum-info-box forum-filter">
	<div class="forum-info-box-inner">
<form action="<?=$APPLICATION->GetCurPageParam("", array(BX_AJAX_PARAM_ID))?>" method=GET>
	<input type="hidden" name="PAGE_NAME" value="pm_search" />
	<input type=hidden value="Y" name="do_search" />
	<?=bitrix_sessid_post()?>
	<?/*?><?=GetMessage("PM_SEARCH_PATTERN")?><?*/?>
	<div class="forum-filter-field forum-pmessage-search-user search-input">
		<label class="forum-filter-field-title" for="<?=$res["ID"]?>"><?=GetMessage("PM_SEARCH_INSERT")?>:</label>
		<span class="forum-filter-field-item"><input type="text" class="search-input" name="search_template" id="search_template" value="<?=$arResult["search_template"]?>" />
		<input type=submit value="<?=GetMessage("PM_SEARCH")?>" name="do_search1" class="inputbutton" /></span>
	</div>
<?/*?>	
	<div class="forum-filter-field forum-filter-footer">
			
			<input type=button value="<?=GetMessage("PM_CANCEL")?>" onclick='self.close();' class=inputbutton>
		<div class="forum-clear-float"></div>
	</div><?*/?>
	<div class="forum-clear-float"></div>
</form>
	</div>
</div>
<?
if ($arResult["SHOW_SEARCH_RESULT"] == "Y"):
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
	<div class="forum-header-title"><span><?=GetMessage("PM_TITLE")?></span></div>
</div>
<?
?>
<div class="forum-info-box forum-info-box-pmsearch">
	<div class="forum-info-box-inner">
<?
if (!empty($arResult["SEARCH_RESULT"])):
$iStartNumber = (($arResult["NAV_RESULT"]->NavPageNomer-1)*$arResult["NAV_RESULT"]->NavPageSize);
$iStartNumber = ($iStartNumber > 0 ? $iStartNumber : 1);
?>
	<ol start="<?=$iStartNumber?>">
<?
	foreach ($arResult["SEARCH_RESULT"] as $res):
?>
	<li>
		<a href="<?=$res["link"]?>"><?=$res["SHOW_ABC"]?></a>
	</li>
<?
	endforeach;
?>
	</ol>
<?
else:
?>
	<?=GetMessage("PM_SEARCH_NOTHING")?>
<?
endif;
?>
	</div>
</div><?
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
?>
</body>
</html>
