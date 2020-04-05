<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();


if (!is_array($arResult["SECTIONS"]) || empty($arResult["SECTIONS"])):
	return false;
endif;

if (!$this->__component->__parent || empty($this->__component->__parent->__name)):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/photogallery/templates/.default/style.css');
endif;

$arParams["WORD_LENGTH"] = (intVal($arParams["WORD_LENGTH"]) > 0 ? intVal($arParams["WORD_LENGTH"]) : 17);

?>
<div class="photo-albums-list photo-albums">
<?
$ELEMENTS_CNT = false; $SECTIONS_CNT = false;
$SECTIONS_ELEMENTS_CNT = false;
foreach ($arResult["SECTIONS"] as $res):
	if ($SECTIONS_ELEMENTS_CNT):
		break;
	endif;
	$ELEMENTS_CNT = ($ELEMENTS_CNT ? $ELEMENTS_CNT : (intVal($res["ELEMENTS_CNT"]) > 0));
	$SECTIONS_CNT = ($SECTIONS_CNT ? $SECTIONS_CNT : (intVal($res["SECTIONS_CNT"]) > 0));
	$SECTIONS_ELEMENTS_CNT = ($SECTIONS_ELEMENTS_CNT ? $SECTIONS_ELEMENTS_CNT : (intVal($res["ELEMENTS_CNT"]) > 0 && intVal($res["SECTIONS_CNT"]) > 0));
endforeach;

foreach ($arResult["SECTIONS"] as $res):

?>
<div class="photo-album">
	<div class="photo-album-img">
		<table cellpadding="0" cellspacing="0" class="shadow">
			<tr class="t"><td colspan="2" rowspan="2">
				<div class="outer" style="width:<?=($arParams["ALBUM_PHOTO_THUMBS_SIZE"] + 38)?>px;">
					<div class="tool" style="height:<?=$arParams["ALBUM_PHOTO_THUMBS_SIZE"]?>px;"></div>
					<div class="inner">
						<a href="<?=$res["LINK"]?>" <?
							?>title="<?=htmlspecialcharsbx($res["~NAME"])?><?=
							htmlspecialcharsbx(!empty($res["DESCRIPTION"]) ? ", ".$res["DESCRIPTION"] : "")?>">
							<div class="photo-album-cover" id="photo_album_cover_<?=$res["ID"]?>" <?
								?>style="width:<?=$arParams["ALBUM_PHOTO_THUMBS_SIZE"]?>px; height:<?=$arParams["ALBUM_PHOTO_THUMBS_SIZE"]?>px;<?
							if (!empty($res["PICTURE"]["SRC"])):
								?>background-image:url('<?=$res["PICTURE"]["SRC"]?>');<?
							endif;
								?>" title="<?=htmlspecialcharsbx($res["~NAME"])?>"></div>
						</a>
					</div>
				</div>
			</td><td class="t-r"><div class="empty"></div></td></tr>
			<tr class="m"><td class="m-r"><div class="empty"></div></td></tr>
			<tr class="b"><td class="b-l"><div class="empty"></div></td><td class="b-c"><div class="empty"></div></td><td class="b-r"><div class="empty"></div></td></tr>
		</table>
	</div>
	<div class="photo-album-info">
		<div class="photo-album-info-name name" id="photo_album_name_<?=$res["ID"]?>" style="width:<?=($arParams["ALBUM_PHOTO_THUMBS_SIZE"] + 38)?>px;">
			<a href="<?=$res["LINK"]?>" title="<?=htmlspecialcharsbx(empty($res["~DESCRIPTION"]) ? $res["~NAME"] : $res["~DESCRIPTION"])?>" <?
				?>class="photo-album-info-name" style="width:<?=($arParams["ALBUM_PHOTO_THUMBS_SIZE"])?>px;">
				<?=(strLen($res["NAME"]) > $arParams["WORD_LENGTH"] ? htmlspecialcharsbx(subStr($res["~NAME"], 0, ($arParams["WORD_LENGTH"]-3)))."..." : $res["NAME"])?>
			</a>
		</div>
		<?
	if ($arParams["PERMISSION"] <= "U"):
?>
		<div class="photo-album-info-cnt-values">
			<?=GetMessage("P_PHOTOS_CNT")?>: <a href="<?=$res["LINK"]?>"><?=$res["ELEMENTS_CNT"]?></a>
		</div>
<?

	elseif ($ELEMENTS_CNT || $SECTIONS_CNT):

?>
		<div class="photo-album-info-cnt-values">
<?

		if ($SECTIONS_ELEMENTS_CNT):
		?>
			<div class="photo-album-info-cnt-value photo-album-info-cnt-photo"><?=GetMessage("P_PHOTOS_CNT")?>: <a href="<?=$res["LINK"]?>"><?=$res["ELEMENTS_CNT"]?></a></div>
			<div class="photo-album-info-cnt-value photo-album-info-cnt-album">
		<?
			if (intVal($res["SECTIONS_CNT"]) > 0):
				?><?=GetMessage("P_ALBUMS_CNT")?>: <a href="<?=$res["LINK"]?>"><?=$res["SECTIONS_CNT"]?></a><?
			else:
				?><?=GetMessage("P_ALBUMS_CNT_NO");?><?
			endif;
		?>
			</div>
		<?
		elseif (intVal($res["SECTIONS_CNT"]) > 0):
		?>
			<div class="photo-album-info-cnt-value photo-album-info-cnt-album">
				<?=GetMessage("P_ALBUMS_CNT")?>: <a href="<?=$res["LINK"]?>"><?=$res["SECTIONS_CNT"]?></a>
			</div>
		<?
		elseif (intVal($res["ELEMENTS_CNT"]) > 0):
		?>
			<div class="photo-album-info-cnt-value photo-album-info-cnt-photo">
				<?=GetMessage("P_PHOTOS_CNT")?>: <a href="<?=$res["LINK"]?>"><?=$res["ELEMENTS_CNT"]?></a>
			</div>
		<?
		else:
		?><div class="photo-album-info-cnt-value photo-album-info-cnt-photo"><br /></div><?
		endif;
?>
		</div>
<?
	endif;
?>
	</div>
</div>
<?
endforeach;
?>
	<div class="empty-clear"></div>
</div>
<div class="photo-navigation photo-navigation-bottom"><?=$arResult["NAV_STRING"]?></div>