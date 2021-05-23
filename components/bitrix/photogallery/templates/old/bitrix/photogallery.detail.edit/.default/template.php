<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/*************************************************************************
	Processing of received parameters
*************************************************************************/
$arParams["SHOW_TAGS"] = ($arParams["SHOW_TAGS"] == "Y" ? "Y" : "N");
$arResult["SHOW_TAGS"] = (($arResult["SHOW_TAGS"] == "Y" && $arParams["SHOW_TAGS"] == "Y") ? "Y" : "N");
$arParams["SHOW_PUBLIC"] = ($arParams["SHOW_PUBLIC"] == "N" ? "N" : "Y");
$arParams["SHOW_APPROVE"] = ($arParams["SHOW_APPROVE"] == "N" ? "N" : "Y");
/*************************************************************************
	/Processing of received parameters
*************************************************************************/
if ($arParams["BEHAVIOUR"] == "USER"):
?>
	<div class="photo-user<?=($arResult["GALLERY"]["CREATED_BY"] == $GLOBALS["USER"]->GetId() ? " photo-user-my" : "")?>">
<?

if (!empty($arResult["DETAIL_LINK"])):
?>
	<div class="photo-controls photo-action">
		<noindex><a rel="nofollow" href="<?=$arResult["DETAIL_LINK"]?>" title="<?=GetMessage("P_GO_TO_SECTION")?>" class="photo-action back-to-album"><?=GetMessage("P_UP")?></a></noindex>
	</div>
<?
endif;
endif;

if ($arParams["AJAX_CALL"] == "Y"):
	$APPLICATION->RestartBuffer();
endif;

?>
<div class="photo-window-edit" id="photo_photo_edit">
<script>
window.cancelblur = function(e)
{
	if (!e)
		e=window.event;
	e.preventDefault();
	e.stopPropagation();
}
</script>
<form method="post" action="<?=POST_FORM_ACTION_URI?>" name="form_photo" id="form_photo" onsubmit="return CheckForm(this);" class="photo-form">
	<input type="hidden" name="edit" value="Y" />
	<input type="hidden" name="sessid" value="<?=bitrix_sessid()?>" />
	<input type="hidden" name="SECTION_ID" value="<?=$arResult["ELEMENT"]["~IBLOCK_SECTION_ID"]?>" />
	<input type="hidden" name="ELEMENT_ID" value="<?=$arResult["ELEMENT"]["~ID"]?>" />
<table cellpadding="0" cellspacing="0" border="0" class="photo-popup">
	<thead>
		<tr>
			<td><?=GetMessage("P_EDIT_ELEMENT")?></td>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td class="table-body">
				<div class="photo-info-box photo-info-box-element-edit inner">
					<div class="photo-info-box-inner">
<?
	ShowError($arResult["ERROR_MESSAGE"]);
?>

	<div class="photo-edit-fields photo-edit-fields-element">
		<div class="photo-edit-field photo-edit-field-title">
			<label for="TITLE"><?=GetMessage("P_TITLE")?><font class="starrequired">*</font></label>
			<input type="text" name="TITLE" name="TITLE" value="<?=$arResult["ELEMENT"]["NAME"]?>" />
		</div>
		<div class="photo-edit-field photo-edit-field-date">
			<label for="DATE_CREATE"><?=GetMessage("P_DATE")?></label>
			<?
				$APPLICATION->IncludeComponent(
					"bitrix:main.calendar",
					"",
					array(
						"SHOW_INPUT" => "Y",
						"FORM_NAME" => "form_photo",
						"INPUT_NAME" => "DATE_CREATE",
						"INPUT_VALUE" => $arResult["ELEMENT"]["DATE_CREATE"]),
					null,
					array("HIDE_ICONS" => "Y"));
				?>
		</div>
		<div class="photo-edit-field photo-edit-field-albums">
			<label for="TO_SECTION_ID"><?=GetMessage("P_ALBUMS")?></label>
				<?
if (is_array($arResult["SECTION_LIST"]))
{
		?><select id="TO_SECTION_ID" name="TO_SECTION_ID"><?
		foreach ($arResult["SECTION_LIST"] as $key => $val):
			?><option value="<?=$key?>" <?
				?> <?=($arResult["ELEMENT"]["IBLOCK_SECTION_ID"] == $key ? "selected" : "")?>><?=$val?></option><?
		endforeach;
		?></select><?
}
			?>
		</div>
<?

if ($arParams["BEHAVIOUR"] == "USER"):
	if ($arParams["SHOW_PUBLIC"] != "N"):
?>
		<div class="photo-edit-field photo-edit-field-public">
			<input type="checkbox" name="PUBLIC_ELEMENT" id="PHOTO_PUBLIC_ELEMENT" value="Y" <?
			?><?=($arResult["ELEMENT"]["PROPERTIES"]["PUBLIC_ELEMENT"]["VALUE"] == "Y" ? " checked='checked'" : "")?> />
			<label for="PHOTO_PUBLIC_ELEMENT"><?=GetMessage("P_PUBLIC_ELEMENT")?></label>
		</div>
<?
	endif;
	if ($arResult["I"]["ABS_PERMISSION"] >= "W"):
		if ($arParams["SHOW_APPROVE"] != "N"):
?>
		<div class="photo-edit-field photo-edit-field-approve">
			<input type="checkbox" name="APPROVE_ELEMENT" id="PHOTO_APPROVE_ELEMENT" value="Y" <?
			?><?=($arResult["ELEMENT"]["PROPERTIES"]["APPROVE_ELEMENT"]["VALUE"] == "Y" ? " checked='checked'" : "")?> />
			<label for="PHOTO_APPROVE_ELEMENT"><?=GetMessage("P_APPROVE_ELEMENT")?></label>
		</div>
<?
		endif;
?>
		<div class="photo-edit-field photo-edit-field-active">
			<input type="checkbox" name="ACTIVE" id="PHOTO_ACTIVE" value="Y" <?
			?><?=($arResult["ELEMENT"]["ACTIVE"] == "Y" ? " checked='checked'" : "")?> />
			<label for="PHOTO_ACTIVE"><?=GetMessage("P_ACTIVE_ELEMENT")?></label>
		</div>
<?
	endif;
endif;

if ($arParams["SHOW_TAGS"] == "Y"):
?>
		<div class="photo-edit-field photo-edit-field-tags">
			<label for="TAGS"><?=GetMessage("P_TAGS")?></label>
<?
	if (IsModuleInstalled("search")):
			?><?$APPLICATION->IncludeComponent(
				"bitrix:search.tags.input",
				"",
				array(
					"VALUE" => $arResult["ELEMENT"]["TAGS"],
					"NAME" => "TAGS"),
				null,
				array(
					"HIDE_ICONS" => "Y"));?><?
	else:
			?><input type="text" name="TAGS" id="TAGS" value="<?=$arResult["ELEMENT"]["TAGS"]?>" /><?
	endif;
?>
		</div>
<?
endif;

?>
		<div class="photo-edit-field photo-edit-field-description">
			<label for="DESCRIPTION"><?=GetMessage("P_DESCRIPTION")?></label>
			<textarea name="DESCRIPTION" id="DESCRIPTION"><?=$arResult["ELEMENT"]["DETAIL_TEXT"]?></textarea>
		</div>
	</div>
				</div>
			</div>
		</td></tr>
	</tbody>
	<tfoot>
		<tr>
			<td class="table-controls">
				<input type="submit" name="name_submit" value="<?=GetMessage("P_SUBMIT");?>" />
				<input type="button" name="cancel" value="<?=GetMessage("P_CANCEL");?>" onclick="CancelSubmit(this)" />
			</td>
		</tr>
	</tfoot>
</table>
</form>
</div>
<?

if ($arParams["AJAX_CALL"] == "Y"):
	die();
else:
?>
<script>
function CancelSubmit(pointer) {
	if (pointer.form) {
		pointer.form.edit.value = 'cancel';
		pointer.form.submit();}
	return false; }
function CheckForm(){return true;}

document.getElementById('TO_SECTION_ID').onclick = function(e){
	if (!jsUtils.IsIE) {
		e.preventDefault();
		e.stopPropagation();}
	return false; }
</script>
<?
endif;

if ($arParams["BEHAVIOUR"] == "USER"):
	?></div><?
endif;

?>