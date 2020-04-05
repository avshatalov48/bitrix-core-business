<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (empty($arResult["GALLERIES"])):
	return false;
elseif (!$this->__component->__parent || strpos($this->__component->__parent->__name, "photogallery") === false):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/photogallery/templates/.default/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/photogallery/templates/.default/themes/gray/style.css');
endif;
/********************************************************************
				Input params
********************************************************************/
$arParams["GALLERY_AVATAR_SIZE"] = intVal(intVal($arParams["GALLERY_AVATAR_SIZE"]) > 0 ? $arParams["GALLERY_AVATAR_SIZE"] : 50);
$arParams["SHOW_PAGE_NAVIGATION"] = (in_array($arParams["SHOW_PAGE_NAVIGATION"], array("none", "top", "bottom", "both")) ? $arParams["SHOW_PAGE_NAVIGATION"] : "bottom");
/********************************************************************
				/Input params
********************************************************************/
if (!$this->__component->__parent || strpos($this->__component->__parent->__name, "photogallery") === false):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/photogallery/templates/.default/style.css');
?>
<style>
div.photo-gallery-avatar{
	width:<?=$arParams["GALLERY_AVATAR_SIZE"]?>px;
	height:<?=$arParams["GALLERY_AVATAR_SIZE"]?>px;}
</style>
<?
endif;

if (($arParams["SHOW_PAGE_NAVIGATION"] == "top" || $arParams["SHOW_PAGE_NAVIGATION"] == "both") && !empty($arResult["NAV_STRING"])):
?>
<div class="photo-navigation-top">
	<?=$arResult["NAV_STRING"]?>
</div>
<?
endif;
?>
<ul class="photo-items-list photo-galleries-list-ascetic">
<?
foreach($arResult["GALLERIES"] as $res):
?>
	<li class="photo-item photo-gallery-item">
		<div class="photo-item photo-gallery-item">
			<div class="photo-gallery-avatar-box">
				<a href="<?=$res["LINK"]["VIEW"]?>" class="photo-gallery-avatar" <?
					?>title="<?=str_replace("#GALLERY#", $res["NAME"], GetMessage("P_GALLERY_VIEW_TITLE"))?>">
					<div class="photo-gallery-avatar <?=(empty($res["PICTURE"]["SRC"])? "photo-gallery-avatar-empty" : "")?>"<?
				if (!empty($res["PICTURE"]["SRC"])):
						?> style="background-image:url('<?=$res["PICTURE"]["SRC"]?>');"<?
				endif;
				?>></div></a>
			</div>
			<div class="photo-gallery-name <?
				?><?=(intVal($res["UF_GALLERY_SIZE"]) > 0 ? "photo-gallery-nonempty" : "photo-gallery-empty")?>">
				<a href="<?=$res["LINK"]["VIEW"]?>" title="<?=str_replace("#GALLERY#", $res["NAME"], GetMessage("P_GALLERY_VIEW_TITLE"))?>"><?=$res["NAME"]?></a>
			</div>
<?
			if (!empty($res["DESCRIPTION"])):
?>
			<div class="photo-gallery-description"><?=$res["DESCRIPTION"]?></div>
<?
			endif;
?>
			<div class="empty-clear"></div>
		</div>
	</li>
<?
endforeach;
?></ul><?

if (($arParams["SHOW_PAGE_NAVIGATION"] == "bottom" || $arParams["SHOW_PAGE_NAVIGATION"] == "both") && !empty($arResult["NAV_STRING"])):
?>
<div class="photo-navigation-bottom">
	<?=$arResult["NAV_STRING"]?>
</div>
<?
endif;
?>