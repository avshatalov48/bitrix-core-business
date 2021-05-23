<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!$this->__component->__parent || empty($this->__component->__parent->__name)):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/photogallery/templates/.default/style.css');
endif;

$arParams["GALLERY_AVATAR_SIZE"] = intVal(intVal($arParams["GALLERY_AVATAR_SIZE"]) > 0 ? $arParams["GALLERY_AVATAR_SIZE"] : 50);
$arParams["SHOW_PAGE_NAVIGATION"] = (in_array($arParams["SHOW_PAGE_NAVIGATION"], array("none", "top", "bottom", "both")) ? 
		$arParams["SHOW_PAGE_NAVIGATION"] : "none");
?><div class="empty-clear"></div><?
if (($arParams["SHOW_PAGE_NAVIGATION"] == "top" || $arParams["SHOW_PAGE_NAVIGATION"] == "both") && !empty($arResult["NAV_STRING"])):
?><div class="photo-navigation"><?=$arResult["NAV_STRING"]?></div>
<div class="empty-clear"></div><?
endif;
?><div class="photo-galleries-ascetic"><?
foreach($arResult["GALLERIES"] as $res):
?>
	<div class="photo-gallery-ascetic">
		<a href="<?=$res["LINK"]["VIEW"]?>" title="<?=
				str_replace("#GALLERY#", $res["NAME"], GetMessage("P_GALLERY_VIEW_TITLE"))?>">
<?
?>
			<div class="photo-gallery-avatar" style="width:<?=$arParams["GALLERY_AVATAR_SIZE"]?>px; <?
							?>height:<?=$arParams["GALLERY_AVATAR_SIZE"]?>px;<?
						if (!empty($res["PICTURE"]["SRC"])):
							?>background-image:url('<?=$res["PICTURE"]["SRC"]?>');<?
						endif;?>"></div>
<?
?>
		</a>
		<div class="photo-gallery-name">
			<a href="<?=$res["LINK"]["VIEW"]?>" title="<?=
				str_replace("#GALLERY#", $res["NAME"], GetMessage("P_GALLERY_VIEW_TITLE"))?>"><?
				?><?=$res["NAME"]?>
			</a>
		</div>
		<div class="photo-gallery-description"><?=$res["DESCRIPTION"]?></div>
	</div><?
endforeach;
?></div><?
if (($arParams["SHOW_PAGE_NAVIGATION"] == "bottom" || $arParams["SHOW_PAGE_NAVIGATION"] == "both") && !empty($arResult["NAV_STRING"])):
?><div class="empty-clear"></div>
<div class="photo-navigation"><?=$arResult["NAV_STRING"]?></div><?
endif;
?><div class="empty-clear"></div>