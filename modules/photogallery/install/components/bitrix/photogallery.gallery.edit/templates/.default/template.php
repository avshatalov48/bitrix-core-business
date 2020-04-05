<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!$this->__component->__parent || strpos($this->__component->__parent->__name, "photogallery") === false):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/photogallery/templates/.default/style.css');
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/photogallery/templates/.default/themes/gray/style.css');
?>
div.photo-gallery-avatar{
	width:<?=$arParams["GALLERY_AVATAR_SIZE"]?>px;
	height:<?=$arParams["GALLERY_AVATAR_SIZE"]?>px;}
</style>
<?
endif;

if (!empty($arResult["ERROR_MESSAGE"])):
?>
<div class="photo-note-box photo-note-error">
	<div class="photo-note-box-text"><?=ShowError($arResult["ERROR_MESSAGE"])?></div>
</div>
<?
endif;
if (!empty($arResult["OK_MESSAGE"])):
?>
<div class="photo-note-box photo-note-note">
	<div class="photo-note-box-text"><?=ShowNote($arResult["OK_MESSAGE"])?></div>
</div>
<?
endif;

?>
<form action="<?=POST_FORM_ACTION_URI?>" method="POST" class="photo-form" enctype='multipart/form-data'>
	<?=bitrix_sessid_post()?>
	<input type="hidden" name="back_url" value="<?= htmlspecialcharsex($_REQUEST["back_url"])?>" />
	<input type="hidden" name="ID" value="<?=$arResult["FORM"]["ID"]?>" />
	<input type="hidden" name="ACTION" value="<?=$arParams["ACTION"]?>" />
<table cellpadding="0" cellspacing="0" border="0" class="photo-popup">
	<thead>
		<tr>
			<td><?=($arParams["ACTION"] == "CREATE" ? GetMessage("P_GALLERY_CREATE") : GetMessage("P_GALLERY_EDIT"))?></td>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td class="table-body">
				<div class="photo-info-box photo-info-box-element-edit inner">
					<div class="photo-info-box-inner">

	<div class="photo-edit-fields photo-edit-fields-gallery">
		<div class="photo-edit-field photo-edit-field-code">
			<label for="CODE"><?=GetMessage("P_GALLERY_CODE")?><font class="starrequired">*</font></label>
			<input type="text" name="CODE" id="CODE" value="<?=$arResult["FORM"]["CODE"]?>" />
			<i><?=GetMessage("P_GALLERY_CODE_NOTIFY")?></i>
		</div>
		<div class="photo-edit-field photo-edit-field-title">
			<label for="NAME"><?=GetMessage("P_GALLERY_NAME")?><font class="starrequired">*</font></label>
			<input type="text" name="NAME" id="NAME" value="<?=$arResult["FORM"]["NAME"]?>" />
		</div>

<?if (count($arResult["GALLERIES"]) > 1 || (count($arResult["GALLERIES"]) > 0 && $arParams["ACTION"] == "CREATE")):?>
		<div class="photo-edit-field photo-edit-field-active">
			<input type="checkbox" name="ACTIVE" id="GALLERY_ACTIVE" value="Y" <?=
				($arResult["FORM"]["UF_DEFAULT"] == "Y" ? " checked='checked'" : "")?> <?=
				($arResult["GALLERY"]["UF_DEFAULT"] == "Y" ? " disabled='disabled'" : "")?> />
			<label for="GALLERY_ACTIVE"><?=GetMessage("P_GALLERY_ACTIVE")?></label>
			<i><?=GetMessage("P_GALLERY_ACTIVE_NOTIFY")?></i>
		</div>
<?endif;?>

		<div class="photo-edit-field photo-edit-field-avatar">
			<label for="AVATAR"><?=GetMessage("P_GALLERY_AVATAR")?></label>
		<?if (!empty($arResult["FORM"]["AVATAR"]["SRC"])):?>
			<div class="photo-gallery-avatar" style="background-image:url('<?=$arResult["FORM"]["AVATAR"]["SRC"]?>'); float: left; margin-right: 0.5em;"></div>
		<?endif;?>
			<input type="file" name="AVATAR" id="AVATAR" value="" />
			<i><?=str_replace(array("#WIDTH#", "#HEIGHT#"),
				$arParams["GALLERY_AVATAR_SIZE"], GetMessage("P_GALLERY_AVATAR_NOTIFY"))?></i>
			<div class="empty-clear"></div>
		</div>
		<div class="photo-edit-field photo-edit-field-description">
			<label for="DESCRIPTION"><?=GetMessage("P_GALLERY_DESCRIPTION")?></label>
			<textarea name="DESCRIPTION" id="DESCRIPTION"><?=$arResult["FORM"]["DESCRIPTION"]?></textarea>
		</div>
	</div>
				</div>
			</div>
		</td></tr>
	</tbody>
	<tfoot>
		<tr>
			<td class="table-controls">
				<input type="submit" name="save" value="<?=($arParams["ACTION"] == "CREATE" ? GetMessage("P_CREATE") : GetMessage("P_SAVE"))?>" />
				<input type="submit" name="cancel" value="<?=GetMessage("P_CANCEL");?>" />
			</td>
		</tr>
	</tfoot>
</table>
</form>