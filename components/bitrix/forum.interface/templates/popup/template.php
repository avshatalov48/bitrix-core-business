<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$GLOBALS['APPLICATION']->AddHeadScript("/bitrix/js/main/utils.js");
$arParams["RETURN"] = ($arParams["RETURN"] == "Y" ? "Y" : "N");
if (!empty($arResult["data"])):
?><script>
//<![CDATA[
	var oForum = (oForum||{});
	oForum['_<?=$arResult["id"]?>'] = <?=CUtil::PhpToJSObject($arResult["data"])?>;
//]]>
</script>
<?if (empty($arResult["head"])):?>
	<div id="_<?=$arResult["id"]?>" onclick="if(typeof(window.fMenu)=='object'){fMenu.ShowMenu(this, oForum['_<?=$arResult["id"]?>'])}"  title="<?=GetMessage("FMI_SHOW")?>" class="icon-more"></div>
<?else:?>
<table cellpadding="0" cellspacing="0" border="0" class="forum-menu-item" onmouseover="this.className+=' forum-menu-item-over';" <?
	?>onmouseout="this.className=this.className.replace(' forum-menu-item-over', '');" <?
	?>onclick="if(typeof(window.fMenu)=='object'){fMenu.ShowMenu(this, oForum['_<?=$arResult["id"]?>'], document.getElementById('_<?=$arResult["id"]?>'))}" <?
	?>title="<?=$arResult["head"]["CONTENT"]?>" id="_table_<?=$arResult["id"]?>"><tr>
<?
	if (!empty($arResult["head"]["ICON"])):?>
	<td class="icon"><div class="<?=$arResult["head"]["ICON"]?>"></div></td><?
	endif;?>
	<td class="content"><?=$arResult["head"]["CONTENT"]?></td>
	<td class="switcher"><div id="_<?=$arResult["id"]?>" class="icon-more" title="<?=GetMessage("FMI_SHOW")?>"></div></td><?
?>
</tr></table>
<?endif;

if ($arParams["RETURN"] == "Y")
	$this->__component->arParams["RETURN_DATA"] = array("id" => "_".$arResult["id"]);
endif;?>
