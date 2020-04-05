<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$arParams["GALLERY_AVATAR_SIZE"] = intVal(intVal($arParams["GALLERY_AVATAR_SIZE"]) > 0 ? $arParams["GALLERY_AVATAR_SIZE"] : 50);
if (!$this->__component->__parent || empty($this->__component->__parent->__name)):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/photogallery/templates/.default/style.css');
endif;

?><div class="photo-controls photo-view">
	<?=GetMessage("P_GALLEY_BY_USER")?> <span class="photo-author"><?=$arResult["USER"]["SHOW_NAME"]?></span>
	<div class="empty-clear"></div>
</div>
<?
if ($arResult["I"]["ACTIONS"]["CREATE_GALLERY"] =="Y" && $arParams["USER_ID"] == $GLOBALS["USER"]->GetId()):

?>
<div class="photo-controls photo-action">
	<noindex><a rel="nofollow" href="<?=$arResult["LINK"]["NEW"]?>" title="<?=GetMessage("P_GALLERY_CREATE_TITLE")?>" <?
		?> class="photo-action gallery-new"><?=GetMessage("P_GALLERY_CREATE")?></a></noindex>
	<div class="empty-clear"></div>
</div>
<?
endif;

if (!empty($arResult["NAV_STRING"])):
?>
<div class="photo-navigation photo-navigation-top"><?=$arResult["NAV_STRING"]?></div>
<?
endif;
?>
<div class="photo-items-list photo-galleries">
<?
foreach($arResult["GALLERIES"] as $res):
?>
	<table width="100%" cellpadding="0" cellspacing="0" border="0" <?
		?>class="photo-gallery <?=($res["UF_DEFAULT"] == "Y" ? " gallery-active" : "")?><?=(empty($res["CODE"]) ? " gallery-error-code" : "")?>" <?
		?>title="<?=($res["UF_DEFAULT"] == "Y" ? GetMessage("P_GALLERY_ACTIVE") : "").(empty($res["CODE"]) ? ($res["UF_DEFAULT"] == "Y" ? ".\n" : "").
			GetMessage("P_ERROR_CODE") : "")?>">
		<tr><td width="1%">
			<div class="photo-gallery-img">
				<table cellpadding="0" cellspacing="0" class="shadow">
					<tr class="t"><td colspan="2" rowspan="2">
						<div class="outer" style="width:<?=($arParams["GALLERY_AVATAR_SIZE"] + 32)?>px;">
							<div class="tool" style="height:<?=$arParams["GALLERY_AVATAR_SIZE"]?>px;"></div>
							<div class="inner">
								<a href="<?=$res["LINK"]["VIEW"]?>" title="<?=str_replace("#GALLERY#", $res["NAME"], GetMessage("P_GALLERY_VIEW_TITLE"))?>">
									<div class="photo-gallery-avatar" style="width:<?=$arParams["GALLERY_AVATAR_SIZE"]?>px; height:<?=$arParams["GALLERY_AVATAR_SIZE"]?>px;<?
										if (!empty($res["PICTURE"]["SRC"])):
												?>background-image:url('<?=$res["PICTURE"]["SRC"]?>');<?
										endif;
										if ($res["UF_DEFAULT"] == "Y"):
												?>position:relative;<?
										endif;
									?>"><?
									if ($res["UF_DEFAULT"] == "Y"):
										?><div class="gallery-active" title="<?=GetMessage("P_GALLERY_ACTIVE")?>"></div><?
									endif;
								?></div>					
								</a>
							</div>
						</div>
					</td>
					<td class="t-r"><div class="empty"></div></td></tr>
					<tr class="m"><td class="m-r"><div class="empty"></div></td></tr>
					<tr class="b">
						<td class="b-l"><div class="empty"></div></td>
						<td class="b-c"><div class="empty"></div></td>
						<td class="b-r"><div class="empty"></div></td></tr>
				</table>
			</div>
		</td>
		<td>
			<div class="gallery-info">
				<div class="photo-gallery-name">
					<a href="<?=$res["LINK"]["VIEW"]?>" title="<?=str_replace("#GALLERY#", $res["NAME"], GetMessage("P_GALLERY_VIEW_TITLE"))?>">
						<?=$res["NAME"]?></a><?
					if (empty($res["CODE"])):
						?><div class="gallery-error-code" title="<?=GetMessage("P_ERROR_CODE")?>"></div><?
					endif;
				?></div>
				<div class="photo-gallery-description"><?=$res["DESCRIPTION"]?></div>
				<div class="photo-controls gallery-controls">
				<?
				if ($arResult["I"]["PERMISSION"] >= "W"):
					?><a href="<?=$res["LINK"]["UPLOAD"]?>" class="photo-action photo-upload" title="<?=GetMessage("P_UPLOAD_TITLE")?>"><?=GetMessage("P_UPLOAD")?></a>
					<a href="<?=$res["LINK"]["EDIT"]?>" class="photo-action gallery-edit" title="<?=GetMessage("P_GALLERY_EDIT_TITLE")?>">
						<?=GetMessage("P_GALLERY_EDIT")?></a>
					<a href="<?=$res["LINK"]["DROP"]?>" class="photo-action gallery-drop" <?
						?>onclick="return confirm('<?=GetMessage('P_GALLERY_DELETE_ASK')?>');" ><?
						?><?=GetMessage("P_GALLERY_DELETE")?></a><?
				endif;
				?>
				</div>
			</div>
		</td></tr>
	</table><?
endforeach;
?>
<div class="empty-clear"></div>
</div>
<?
if (!empty($arResult["NAV_STRING"])):
?>
<div class="photo-navigation photo-navigation-bottom"><?=$arResult["NAV_STRING"]?></div>
<?
endif;
?>