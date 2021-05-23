<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!$this->__component->__parent || empty($this->__component->__parent->__name)):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/photogallery/templates/.default/style.css');
endif;

?>
<div class="photo-controls photo-action">
	<noindex><a rel="nofollow" href="<?=$arResult["URL"]?>" title="<?=GetMessage("P_UP_TITLE")?>" <?
		?>class="photo-action back-to-album"><?=GetMessage("P_UP")?></a></noindex>
</div>
<?

if ($arParams["AJAX_CALL"] == "Y"):
	$APPLICATION->RestartBuffer();
endif;

?>
<div class="photo-window-edit" id="photo_section_edit_form">
<form method="post" action="<?=POST_FORM_ACTION_URI?>" name="form_photo" id="form_photo" onsubmit="return CheckForm(this);" class="photo-form">
	<input type="hidden" name="save_edit" value="Y" />
	<input type="hidden" name="edit" value="Y" />
	<input type="hidden" name="sessid" value="<?=bitrix_sessid()?>" />
	<input type="hidden" name="IBLOCK_SECTION_ID" value="<?=$arResult["FORM"]["IBLOCK_SECTION_ID"]?>" />
<table cellpadding="0" cellspacing="0" border="0" class="photo-popup">
	<thead>
		<tr>
			<td><?=GetMessage("P_EDIT_SECTION")?></td>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td class="table-body">
				<div class="photo-info-box photo-info-box-section-edit inner">
					<div class="photo-info-box-inner">
<?	
	ShowError($arResult["ERROR_MESSAGE"]);

if ($arParams["ACTION"] != "CHANGE_ICON")
{
?>
	<div class="photo-edit-fields photo-edit-fields-section">
		<div class="photo-edit-field photo-edit-field-title">
			<label for="NAME"><?=GetMessage("P_ALBUM_NAME")?><font class="starrequired">*</font></label>
			<input type="text" name="NAME" id="NAME" value="<?=$arResult["FORM"]["NAME"]?>" />
		</div>
		<div class="photo-edit-field photo-edit-field-date">
			<label for="DATE_CREATE"><?=GetMessage("P_ALBUM_DATE")?></label>
			<?$APPLICATION->IncludeComponent( "bitrix:system.field.edit", 
				$arResult["FORM"]["~DATE"]["USER_TYPE"]["USER_TYPE_ID"], 
				array(
					"bVarsFromForm" => $arResult["bVarsFromForm"], 
					"arUserField" => $arResult["FORM"]["~DATE"], 
					"form_name" => "form_photo"), 
				$component, 
			array("HIDE_ICONS"=>"Y"));?>
		</div>
		<div class="photo-edit-field photo-edit-field-description">
			<label for="DESCRIPTION"><?=GetMessage("P_ALBUM_DESCRIPTION")?></label>
			<textarea name="DESCRIPTION" id="DESCRIPTION"><?=$arResult["FORM"]["DESCRIPTION"]?></textarea>
		</div>
		
		<div class="photo-edit-field photo-edit-field-password" id="section_password">
<?
		if (!empty($arResult["FORM"]["~PASSWORD"]["VALUE"])):
?>
			<input type="hidden" id="DROP_PASSWORD" name="DROP_PASSWORD" value="N" />
			<input type="checkbox" id="USE_PASSWORD" name="USE_PASSWORD" value="Y" onclick="this.form.DROP_PASSWORD.value=this.checked?'N':'Y';" checked="checked" />
			<label for="USE_PASSWORD"><?=GetMessage("P_SET_PASSWORD")?></label>
<?
		else:
?>
			<input type="checkbox" id="USE_PASSWORD" name="USE_PASSWORD" value="Y" onclick="this.form.PHOTO_PASSWORD.disabled=!this.checked;" />		
			<label for="USE_PASSWORD"><?=GetMessage("P_SET_PASSWORD")?></label>
			<div class="photo-edit-field photo-edit-field-password-edit"  style="padding-left:1em;">
				<label for="PHOTO_PASSWORD"><?=GetMessage("P_PASSWORD")?></label>
				<input type="password" name="PASSWORD" id="PHOTO_PASSWORD" value="" disabled="disabled" />
			</div>
<?
		endif;
?>
		</div>
	</div>
<?
}
?>
					</div>
				</div>
			</td>
		</tr>
	</tbody>
	<tfoot>
		<tr><td class="table-controls">
			<input type="submit" name="name_submit" value="<?=GetMessage("P_SUBMIT");?>" />
			<input type="button" name="name_cancel" value="<?=GetMessage("P_CANCEL");?>" onclick="CancelSubmit(this)" />
		</td></tr>
	</tfoot>
</table>
</form>
</div>
<?
if ($arParams["AJAX_CALL"] == "Y"):
	$APPLICATION->ShowHeadStrings();
	$APPLICATION->ShowHeadScripts();
	die();
else:
?>
<script>
function CancelSubmit(pointer) {
	if (pointer.form) {
		pointer.form.edit.value = 'cancel'; 
		pointer.form.submit();}
	return false; }
function CheckForm() {
	return true; }
</script>
<?
endif;

?>