<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/********************************************************************
				Input params
********************************************************************/
$temp = array("STRING" => preg_replace("/[^0-9]/is", "/", $arParams["THUMBS_SIZE"]));
list($temp["WIDTH"], $temp["HEIGHT"]) = explode("/", $temp["STRING"]);
$arParams["THUMBS_SIZE"] = (intVal($temp["WIDTH"]) > 0 ? intVal($temp["WIDTH"]) : 300);
$arParams["SHOW_TAGS"] = ($arParams["SHOW_TAGS"] == "Y" ? "Y" : "N");
/********************************************************************
				/Input params
********************************************************************/
if (!$this->__component->__parent || empty($this->__component->__parent->__name)):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/photogallery/templates/.default/style.css');
endif;

IncludeAJAX();
$GLOBALS['APPLICATION']->AddHeadString('<script src="/bitrix/js/main/utils.js"></script>', true);
$GLOBALS['APPLICATION']->AddHeadString('<script src="/bitrix/components/bitrix/photogallery/templates/.default/script.js"></script>', true);
$arParams["THUMBS_SIZE"] = (intVal($temp["WIDTH"]) > 0 ? intVal($temp["WIDTH"]) : 300);
// EbK
$GLOBALS['APPLICATION']->IncludeComponent("bitrix:main.calendar", "", array("SILENT" => "Y"), $component, array("HIDE_ICONS" => "Y"));
if ($arParams["SHOW_TAGS"] == "Y" && IsModuleInstalled("search")):
	$GLOBALS['APPLICATION']->IncludeComponent("bitrix:search.tags.input", "", array("SILENT" => "Y"), $component, array("HIDE_ICONS" => "Y"));
endif;
$sTitle = htmlspecialcharsEx($arResult["ELEMENT"]["~NAME"]);
?>
<div class="photo-controls  photo-action">
	<noindex><a rel="nofollow" href="<?=$arResult["SECTION"]["BACK_LINK"]?>" title="<?=GetMessage("P_GO_TO_SECTION")?>" class="photo-action back-to-album" <?
	?>><?=GetMessage("P_UP")?></a></noindex>
<?
if (!empty($arResult["SECTION"]["UPLOAD_LINK"])):
	?><noindex><a rel="nofollow" href="<?=$arResult["SECTION"]["UPLOAD_LINK"]?>" title="<?=GetMessage("P_UPLOAD_TITLE")?>" class="photo-action photo-upload" <?
	?>><?=GetMessage("P_UPLOAD")?></a></noindex><?
endif;
?>
	<div class="empty-clear"></div>
</div>

<div class="photo-detail">

	<div class="photo-detail-inner-image">
		<div class="photo-photo">
			<div class="photo-img">
				<a rel="nofollow" href="<?=$arResult["SLIDE_SHOW"]?>">
					<?=CFile::ShowImage($arResult["ELEMENT"]["PICTURE"],
						$arParams["THUMBS_SIZE"],
						$arParams["THUMBS_SIZE"],
						"border=\"0\" vspace=\"0\" hspace=\"0\" ".
						"alt=\"".$sTitle."\" title=\"".$sTitle."\" ");?>
				</a>
			</div>
		</div>

<?
$ii = ($arResult["ELEMENT"]["CURRENT"]["NO"] - 1);
?>
		<div id="photo_navigation" class="photo-detail-inner-navigation">
			<table cellpadding="5" cellspacing="0" border="0"><tr><td>
<?
	if (!empty($arResult["ELEMENTS_LIST"][$ii-1]["DETAIL_PAGE_URL"])):
?>
				<a href="<?=$arResult["ELEMENTS_LIST"][$ii-1]["DETAIL_PAGE_URL"]?>">
					<div id="photo_go_to_prev" title="<?=GetMessage("P_GO_TO_PREV")?>"></div>
				</a>
<?
	else :
?>
				<div id="photo_go_to_prev_disabled" title="<?=GetMessage("P_GO_TO_PREV")?>"></div>
<?
	endif;
?>
			</td>
			<td nowrap="nowrap"><?=GetMessage("NO_OF_COUNT",array("#NO#"=>$arResult["ELEMENT"]["CURRENT"]["NO"],"#TOTAL#"=>$arResult["ELEMENT"]["CURRENT"]["COUNT"]))?></td>
			<td>
<?
	if (!empty($arResult["ELEMENTS_LIST"][$ii+1]["DETAIL_PAGE_URL"])):
?>
				<a href="<?=$arResult["ELEMENTS_LIST"][$ii+1]["DETAIL_PAGE_URL"]?>">
					<div id="photo_go_to_next" title="<?=GetMessage("P_GO_TO_NEXT")?>"></div>
				</a>
<?
	else:
?>
				<div id="photo_go_to_next_disabled" title="<?=GetMessage("P_GO_TO_NEXT")?>"></div>
<?
	endif;
?>
			</td></tr></table>
		</div>
	</div>
	<div id="photo_text_description" class="photo-detail-inner-description">
		<div class="photo-title" id="photo_title"><?=$arResult["ELEMENT"]["NAME"]?></div>
		<div class="photo-date" id="photo_date"><?=$arResult["ELEMENT"]["DATE_CREATE"]?></div>
<?

	if ($arParams["SHOW_TAGS"] == "Y" && !empty($arResult["ELEMENT"]["TAGS_LIST"])):
		?><div class="photo-tags" id="photo_tags"><?
		foreach ($arResult["ELEMENT"]["TAGS_LIST"] as $key => $val):
			?><noindex><a rel="nofollow" href="<?=$val["TAGS_URL"]?>"><?=$val["TAGS_NAME"]?></a></noindex><?
			if ($key != (count($arResult["ELEMENT"]["TAGS_LIST"]) - 1)):
			?>, <?
			endif;
		endforeach;
		?></div><?
	elseif ($arParams["SHOW_TAGS"] == "Y"):
		?><div class="photo-tags" id="photo_tags"><?=$arResult["ELEMENT"]["TAGS"]?></div><?
	endif;
?>
		<div class="photo-description" id="photo_description"><?=$arResult["ELEMENT"]["DETAIL_TEXT"]?></div>

		<div class="photo-controls photo-view">
<?
	if (!empty($arResult["SLIDE_SHOW"])):
			?><noindex><a rel="nofollow" href="<?=$arResult["SLIDE_SHOW"]?>" class="photo-view slide-show" title="<?=GetMessage("P_SLIDE_SHOW_TITLE")?>"><?
					?><?=GetMessage("P_SLIDE_SHOW")?></a></noindex><?
	endif;

	if (!empty($arResult["ELEMENT"]["REAL_PICTURE"]["SRC"])):
			?><noindex><a rel="nofollow" href="<?echo $arResult["ELEMENT"]["REAL_PICTURE"]["SRC"];?>" <?
					?>title="<?=GetMessage("P_ORIGINAL_TITLE")?>"
					onclick="ShowOriginal('<?echo CUtil::JSEscape($arResult["ELEMENT"]["REAL_PICTURE"]["SRC"])?>', '<?=CUtil::JSEscape($arResult["ELEMENT"]["NAME"])?>'); return false;" class="photo-view original"><?
					?><?=GetMessage("P_ORIGINAL")?></a></noindex><?
	endif;
?>
		</div>

		<div class="photo-controls">
<?
	if (!empty($arResult["ELEMENT"]["EDIT_URL"])):
		?><noindex><a rel="nofollow" href="<?=$arResult["ELEMENT"]["EDIT_URL"]?>" title="<?=GetMessage("P_EDIT_TITLE")?>" <?
			?>onclick="EditPhoto('<?=CUtil::JSEscape($arResult["ELEMENT"]["~EDIT_URL"])?>'); return false;" id="photo_edit" <?
			?>class="photo-action edit"><?=GetMessage("P_EDIT")?></a></noindex><?
	endif;
	if (!empty($arResult["ELEMENT"]["DROP_URL"])):
			?><noindex><a rel="nofollow" href="<?=$arResult["ELEMENT"]["DROP_URL"]?>" title="<?=GetMessage("P_DROP_TITLE")?>" <?
				?>onclick="return confirm('<?=GetMessage("P_DROP_CONFIM")?>');" id="photo_drop" class="photo-action delete"><?=GetMessage("P_DROP")?></a></noindex><br /><br /><?
	endif;
?>
		</div>
		<br />
		<div class="photo-controls"><div id="photo_vote"></div></div>
	</div>
	<div class="empty-clear"></div>
</div>