<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use \Bitrix\Main\Localization\Loc;

$file = trim(preg_replace("'[\\\\/]+'", "/", (__DIR__."/lang/".LANGUAGE_ID."/footer.php")));
if (!file_exists($file))
	$file = trim(preg_replace("'[\\\\/]+'", "/", (__DIR__."/lang/en/footer.php")));
if(file_exists($file)):
	global $MESS;
	include_once($file);
endif;
if ($arParams["SHOW_FORUMS"] == "Y")
	$arParams["SHOW_FORUMS"] = (in_array($this->__page, array("forums", "list", "read")) ? "Y" : "N");

if (($arParams["SHOW_FORUMS"] == "Y" && !empty($arResult["GROUPS_FORUMS"]))/* || IsModuleInstalled("search")*/):
?>
<div class="forum-info-box forum-main-footer">
	<div class="forum-info-box-inner">
<?
	if (false && IsModuleInstalled("search")):
?>
		<div class="forum-search-input">
			<form action="<?=$arResult["URL_TEMPLATES"]["SEARCH"]?>" method="GET" class="forum-form">
				<input type="hidden" name="PAGE_NAME" value="search" /> 
				<input type="hidden" name="FORUM_ID" value="<?=$arResult["FID"]?>" /> 
				<input type="q" value="<?=Loc::getMessage("F_SEARCH_TEXT")?>" onfocus="this.value='';this.onfocus=function(){};this.form.search_submit.onclick=function(){}" />
				<input type="submit" value="OK" name="search_submit" onclick="" />
			</form>
		</div>
<?		
	endif;

	if ($arParams["SHOW_FORUMS"] == "Y" && !empty($arResult["GROUPS_FORUMS"])):
		$iGid = 0;
		$iFid = 0;
		if ($this->__page == "index" || $this->__page == "forums")
			$iGid = intval($arResult["GID"]);
		else 
			$iFid = intval($arResult["FID"]);
?>
		<div class="forum-rapid-access-items">
			<form action="<?=$arResult["URL_TEMPLATES"]["INDEX"]?>" method="GET" class="forum-form">
			<input type="hidden" name="buf_fid" value="<?=($iFid > 0 ? $iFid : "GID_".$iGid)?>" />
			<input type="hidden" name="rapid_access" value="Y" />
			<select name="FID" class="forum-selector-single" onclick="if(this.value!=this.form.buf_fid.value){this.form.submit()}">
<?
		foreach ($arResult["GROUPS_FORUMS"] as $key => $res):
			if ($res["TYPE"] == "GROUP"):
				$str = str_pad("", ($res["DEPTH"] - 1)*12, "&nbsp;");
?>
				<option value="GID_<?=$res["ID"]?>" <?=($iGid == $res["ID"] ? "selected='selected'" : "")?> <?
					?>class="groups level<?=$res["DEPTH"]?><?=($iGid == $res["ID"] ? " active" : "")?>"><?=$str.$res["NAME"]?></option>
<?
			else:
			$str = ($res["DEPTH"] > 0 ? str_pad("", $res["DEPTH"]*12, "&nbsp;")."&nbsp;" : "");
?>
				<option value="<?=$res["ID"]?>" <?=($iFid == $res["ID"] ? "selected='selected'" : "")?> <?
					?>class="forum level<?=$res["DEPTH"]?><?=($iFid == $res["ID"] ? " active" : "")?>"><?=$str.$res["NAME"]?></option>
<?
			endif;
		endforeach;
?>
			</select>
			<input type="submit" value="OK" />
			</form>
		</div>
<?
	endif;
?>
		<div class="forum-clear-float"></div>
	</div>
</div>
<?
endif;
	if ($arParams["SHOW_LEGEND"] == "Y" && in_array($this->__page, array("index", "forums", "list"))):
?>
<div class="forum-info-box forum-main-footer">
	<div class="forum-info-box-inner">
		<div class="forum-legend-info">
			<div class="forum-legend-item"><div class="forum-icon-container"><div class="forum-icon forum-icon-newposts"><!-- ie --></div></div>
				<span><?=Loc::getMessage("F_INFO_NEW_MESS")?></span></div>
			<div class="forum-legend-item"><div class="forum-icon-container"><div class="forum-icon forum-icon-default"><!-- ie --></div></div>
				<span><?=Loc::getMessage("F_INFO_NO_MESS")?></span></div>
<?
		if ($this->__page == "list"):
?>
			<div class="forum-legend-item"><div class="forum-icon-container"><div class="forum-icon forum-icon-moved"><!-- ie --></div></div>
				<span><?=Loc::getMessage("F_INFO_MOVED")?></span></div>
			<div class="forum-legend-item"><div class="forum-icon-container"><div class="forum-icon forum-icon-sticky"><!-- ie --></div></div>
				<span><?=Loc::getMessage("F_INFO_PINNED")?></span></div>
			<div class="forum-legend-item"><div class="forum-icon-container"><div class="forum-icon forum-icon-closed"><!-- ie --></div></div>
				<span><?=Loc::getMessage("F_INFO_CLOSED")?></span></div>
<?
		endif;
?>
			<div class="forum-clear-float"></div>
		</div>
	</div>
</div>
<?
	endif;

?>