<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!$this->__component->__parent || empty($this->__component->__parent->__name)):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/photogallery/templates/.default/style.css');
endif;

if (!empty($arResult["ERROR_MESSAGE"]))
	ShowError($arResult["ERROR_MESSAGE"]);
elseif (!empty($arResult["OK_MESSAGE"]))
	ShowNote($arResult["OK_MESSAGE"]);
?>
<div class="photo-controls photo-view"><?
	?><?=GetMessage("P_GALLEY_BY_USER")?> <span class="photo-title"><?=$arResult["USER"]["SHOW_NAME"]?></span><?
?></div>
<form action="<?=POST_FORM_ACTION_URI?>" method="POST" class="photo-form" enctype='multipart/form-data'>
	<?=bitrix_sessid_post()?>
	<input type="hidden" name="ID" value="<?=$arResult["FORM"]["ID"]?>" />
	<input type="hidden" name="ACTION" value="<?=$arParams["ACTION"]?>" />
<table class="data-table photo-table" border="0" cellpadding="0" cellspacing="0">
	<thead>
		<tr><td colspan="3"><?=($arParams["ACTION"] == "CREATE" ? GetMessage("P_GALLERY_CREATE") : GetMessage("P_GALLERY_EDIT"))?></td></tr>
	</thead>
	<tbody>
		<tr><td><font class="starrequired">*</font><?=GetMessage("P_GALLERY_CODE")?>:</td>
			<td width="250"><input type="text" name="CODE" value="<?=$arResult["FORM"]["CODE"]?>" /></td>
			<td><div class="photo-data photo-explanation"><?=GetMessage("P_GALLERY_CODE_NOTIFY")?></div></td>
		</tr>
		<tr><td><font class="starrequired">*</font><?=GetMessage("P_GALLERY_NAME")?>:</td>
			<td><input type="text" name="NAME" value="<?=$arResult["FORM"]["NAME"]?>" /></td>
			<td></td></tr>
		<tr><td><?=GetMessage("P_GALLERY_DESCRIPTION")?>:</td>
			<td><textarea name="DESCRIPTION"><?=$arResult["FORM"]["DESCRIPTION"]?></textarea></td>
			<td></td></tr>
		<?
if (count($arResult["GALLERIES"]) > 1 || (count($arResult["GALLERIES"]) > 0 && $arParams["ACTION"] == "CREATE")):
		?><tr><td><label for="GALLERY_ACTIVE"><?=GetMessage("P_GALLERY_ACTIVE")?>:</label></td>
			<td><input type="checkbox" name="ACTIVE" id="GALLERY_ACTIVE" value="Y" <?=
				($arResult["FORM"]["UF_DEFAULT"] == "Y" ? " checked='checked'" : "")?> <?=
				($arResult["GALLERY"]["UF_DEFAULT"] == "Y" ? " disabled='disabled'" : "")?> /></td>
			<td><?=GetMessage("P_GALLERY_ACTIVE_NOTIFY")?></td></tr><?
endif;
			?>
		<tr><td><?=GetMessage("P_GALLERY_AVATAR")?>:</td>
			<td><?
			if (!empty($arResult["FORM"]["AVATAR"]["SRC"])):
				?><div class="photo-gallery-avatar" <?
		?>style="width:<?=$arParams["GALLERY_AVATAR_SIZE"]?>px; height:<?=$arParams["GALLERY_AVATAR_SIZE"]?>px;<?
	if (!empty($arResult["FORM"]["AVATAR"]["SRC"])):
			?>background-image:url('<?=$arResult["FORM"]["AVATAR"]["SRC"]?>');<?
	endif;
		?>"></div><?
			endif;
			?><input type="file" name="AVATAR" value="" /></td>
			<td><div class="photo-data photo-explanation"><?=str_replace(array("#WIDTH#", "#HEIGHT#"), $arParams["GALLERY_AVATAR_SIZE"], GetMessage("P_GALLERY_AVATAR_NOTIFY"))?></div>
			</td></tr>
	</tbody>
	<tfoot>
		<tr><td colspan="3" align="center"><input type="submit" name="save" <?
				?>value="<?=($arParams["ACTION"] == "CREATE" ? GetMessage("P_CREATE") : GetMessage("P_SAVE"))?>" /> <?
			?><input type="submit" name="cancel" <?
				?>value="<?=GetMessage("P_CANCEL")?>" /></td></tr>
	</tfoot>
	</table>
</form>