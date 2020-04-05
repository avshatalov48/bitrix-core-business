<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
IncludeModuleLangFile(__FILE__);

if (!isset($adminFormParams) || !is_array($adminFormParams))
{
	$adminFormParams = array(
		'tabPrefix' => 'cedit'
	);
}
$jsAdminFormParams = CUtil::PhpToJSObject($adminFormParams);

$arSystemTabsFields = array();

foreach($this->arSystemTabs as $arTab)
{
	if(!array_key_exists("CUSTOM", $arTab) || $arTab["CUSTOM"] !== "Y")
	{
		$arSystemTabsFields[$arTab["DIV"]] = array();
		if(is_array($arTab["FIELDS"]))
		{
			foreach($arTab["FIELDS"] as $i => $arField)
			{
				$arSystemTabsFields[$arTab["DIV"]][$arField["id"]] = $arField["id"];
			}
		}
	}
}

$arSystemTabs = array();
foreach($this->arSystemTabs as $arTab)
{
	if(!array_key_exists("CUSTOM", $arTab) || $arTab["CUSTOM"] !== "Y")
	{
		$arSystemTabs[$arTab["DIV"]] = $arTab["TAB"];
	}
}

$arSystemFields = array();
foreach($this->arSystemTabs as $arTab)
{
	if(!array_key_exists("CUSTOM", $arTab) || $arTab["CUSTOM"] !== "Y")
	{
		if(is_array($arTab["FIELDS"]))
		{
			foreach($arTab["FIELDS"] as $arField)
			{
				$id = htmlspecialcharsbx($arField["id"]);
				$label = htmlspecialcharsbx(rtrim(trim($arField["content"]), " :"));
				if($arField["delimiter"])
					$arSystemFields[$id] = "--".$label;
				else
					$arSystemFields[$id] = ($arField["required"]? "*": "&nbsp;&nbsp;").$label;
			}
		}
	}
}

$arAvailableTabs = $arSystemTabs;
$arAvailableFields = $arSystemFields;

$arCustomFields = array();
foreach($this->tabs as $arTab)
{
	if(!array_key_exists("CUSTOM", $arTab) || $arTab["CUSTOM"] !== "Y")
	{
		$ar = array(
			"TAB" => $arTab["TAB"],
			"FIELDS" => array(),
		);
		if(is_array($arTab["FIELDS"]))
		{
			foreach($arTab["FIELDS"] as $arField)
			{
				$id = htmlspecialcharsbx($arField["id"]);
				$label = htmlspecialcharsbx(rtrim(trim($arField["content"]), " :"));
				if($arField["delimiter"])
					$ar["FIELDS"][$id] = "--".$label;
				else
					$ar["FIELDS"][$id] = ($arField["required"]? "*": "&nbsp;&nbsp;").$label;
				unset($arAvailableFields[$id]);
			}
		}
		$arCustomFields[$arTab["DIV"]] = $ar;
		unset($arAvailableTabs[$arTab["DIV"]]);
	}
}

$arFormEditMess = array(
	"admin_lib_sett_tab_prompt" => GetMessage("admin_lib_sett_tab_prompt"),
	"admin_lib_sett_tab_default_name" => GetMessage("admin_lib_sett_tab_default_name"),
	"admin_lib_sett_sec_prompt" => GetMessage("admin_lib_sett_sec_prompt"),
	"admin_lib_sett_sec_default_name" => GetMessage("admin_lib_sett_sec_default_name"),
	"admin_lib_sett_sec_rename" => GetMessage("admin_lib_sett_sec_rename"),
	"admin_lib_sett_tab_rename" => GetMessage("admin_lib_sett_tab_rename"),
);

$obJSPopup = new CJSPopup(GetMessage("admin_lib_sett_tab_title"));
$obJSPopup->ShowTitlebar(GetMessage("admin_lib_sett_tab_title"));
$obJSPopup->StartContent();
?>
<script type="text/javascript">
var arSystemTabsFields = <?echo CUtil::PhpToJSObject($arSystemTabsFields)?>;
var arSystemTabs = <?echo CUtil::PhpToJSObject($arSystemTabs)?>;
var arSystemFields = <?echo CUtil::PhpToJSObject($arSystemFields)?>;
var arFormEditMess = <?echo CUtil::PhpToJSObject($arFormEditMess)?>;
(BX.defer(Sync))();
</script>
</form>
<form enctype="multipart/form-data" name="form_settings" action="<?echo $APPLICATION->GetCurPageParam()?>" method="POST">
<div class="settings-form">
<h2 ondblclick="exportSettingsToPhp(event, '<?echo $this->name;?>')"><?echo GetMessage("admin_lib_sett_tab_fields")?></h2>
<table width="100%" cellspacing="0">
	<tr valign="center">
		<td colspan="2"><?echo GetMessage("admin_lib_sett_tab_available_tabs")?>:</td>
		<td colspan="2"><?echo GetMessage("admin_lib_sett_tab_selected_tabs")?>:</td>
	</tr>
	<tr valign="center">
		<td width="0">
			<select class="select" name="available_tabs" id="available_tabs" onchange="Sync();" size="8" style="height: 190px;">
<?
foreach($arSystemTabs as $id => $label)
{
	echo '<option value="'.htmlspecialcharsbx($id).'">'.htmlspecialcharsbx($label).'</option>';
}
?>
			</select>
		</td>
		<td width="50%" align="center">
			<input type="button" name="tabs_copy" id="tabs_copy" value="&nbsp; &gt; &nbsp;" title="<?echo GetMessage("admin_lib_sett_tab_copy")?>" disabled onclick="OnAdd(this.id, <? echo $jsAdminFormParams; ?>);">
		</td>
		<td width="0">
			<select class="select" name="selected_tabs" id="selected_tabs" size="8" onchange="Sync();" style="height: 190px;">
<?
foreach($arCustomFields as $tab_id => $arTab)
{
	echo '<option value="'.htmlspecialcharsbx($tab_id).'">'.htmlspecialcharsbx($arTab["TAB"]).'</option>';
}
?>
			</select>
		</td>
		<td width="50%" align="center">
			<input type="button" name="tabs_up" id="tabs_up" class="button" value="<?echo GetMessage("admin_lib_sett_up")?>" title="<?echo GetMessage("admin_lib_sett_up_title")?>" disabled onclick="BX.selectUtils.moveOptionsUp(document.form_settings.selected_tabs);"><br>
			<input type="button" name="tabs_down" id="tabs_down" class="button" value="<?echo GetMessage("admin_lib_sett_down")?>" title="<?echo GetMessage("admin_lib_sett_down_title")?>" disabled onclick="BX.selectUtils.moveOptionsDown(document.form_settings.selected_tabs);"><br>
			<input type="button" name="tabs_rename" id="tabs_rename" class="button" value="<?echo GetMessage("admin_lib_sett_tab_rename")?>" title="<?echo GetMessage("admin_lib_sett_tab_rename_title")?>" disabled onclick="OnRename(this.id);"><br>
			<input type="button" name="tabs_add" id="tabs_add" class="button" value="<?echo GetMessage("admin_lib_sett_tab_add")?>" title="<?echo GetMessage("admin_lib_sett_tab_add_title")?>" onclick="OnAdd(this.id, <? echo $jsAdminFormParams; ?>);"><br>
			<input type="button" name="tabs_delete" id="tabs_delete" class="button" value="<?echo GetMessage("admin_lib_sett_del")?>" title="<?echo GetMessage("admin_lib_sett_del_title")?>" disabled onclick="OnDelete(this.id);"><br>
		</td>
	</tr>
	<tr valign="center">
		<td colspan="2"><?echo GetMessage("admin_lib_sett_tab_available_fields")?>:</td>
		<td colspan="2"><?echo GetMessage("admin_lib_sett_tab_selected_fields")?>:</td>
	</tr>
	<tr valign="center">
		<td>
			<select class="select" name="available_fields" id="available_fields" size="12" multiple onchange="Sync();" style="height: 255px;">
<?
foreach($arAvailableFields as $id => $label)
{
	echo '<option value="'.$id.'">'.$label.'</option>';
}
?>
			</select>
		</td>
		<td align="center">
			<input type="button" name="fields_copy" id="fields_copy" value="&nbsp; &gt; &nbsp;" title="<?echo GetMessage("admin_lib_sett_fields_copy")?>" disabled onclick="OnAdd(this.id, <? echo $jsAdminFormParams; ?>);"><br><br>
		</td>
		<td id="selected_fields">
			<select style="display:block; height: 255px;" disabled class="select" name="selected_fields[undef]" id="selected_fields[undef]" size="12" multiple></select>
<?
foreach($arCustomFields as $tab_id => $arTab)
{
	if(is_array($arTab["FIELDS"]))
	{
		echo '<select style="display:none; height:255px;" class="select" name="selected_fields['.$tab_id.']" id="selected_fields['.$tab_id.']" size="12" multiple onchange="Sync();">';
		foreach($arTab["FIELDS"] as $field_id => $label)
		{
			echo '<option value="'.$field_id.'">'.$label.'</option>';
		}
		echo '</select>';
	}
}
?>
		</td>
		<td align="center">
			<input type="button" name="fields_up" id="fields_up" class="button" value="<?echo GetMessage("admin_lib_sett_up")?>" title="<?echo GetMessage("admin_lib_sett_up_title")?>" disabled onclick="FieldsUpAndDown('up');"><br>
			<input type="button" name="fields_down" id="fields_down" class="button" value="<?echo GetMessage("admin_lib_sett_down")?>" title="<?echo GetMessage("admin_lib_sett_down_title")?>" disabled onclick="FieldsUpAndDown('down');"><br>
			<input type="button" name="fields_rename" id="fields_rename" class="button" value="<?echo GetMessage("admin_lib_sett_field_rename")?>" title="<?echo GetMessage("admin_lib_sett_field_rename_title")?>" disabled onclick="OnRename(this.id);"><br>
			<input type="button" name="fields_add" id="fields_add" class="button" value="<?echo GetMessage("admin_lib_sett_field_add")?>" title="<?echo GetMessage("admin_lib_sett_field_add_title")?>" onclick="OnAdd(this.id, <? echo $jsAdminFormParams; ?>);"><br>
			<input type="button" name="fields_delete" id="fields_delete" class="button" value="<?echo GetMessage("admin_lib_sett_del")?>" title="<?echo GetMessage("admin_lib_sett_fields_delete")?>" disabled onclick="OnDelete(this.id);">
		</td>
	</tr>
</table>
<?
if($GLOBALS["USER"]->CanDoOperation('edit_other_settings')):
?>
<h2><?echo GetMessage("admin_lib_sett_common")?></h2>
<table cellspacing="0" width="100%">
	<tr>
		<td><input type="checkbox" name="set_default" id="set_default" value="Y"></td>
		<td><label for="set_default"><?echo GetMessage("admin_lib_sett_common_set")?></label></td>
		<td><a class="delete-icon" title="<?echo GetMessage("admin_lib_sett_common_del")?>" href="javascript:if(confirm('<?echo GetMessage("admin_lib_sett_common_del_conf")?>'))<?echo $this->name?>.DeleteSettings(true)"></a></td>
	</tr>
</table>
<?
endif
?>
	<div id="save_settings_error" class="settings-error">
		<p class="settings-error-header"><?=GetMessage('SAVE_SETTINGS_ERROR_TITLE'); ?></p>
		<p class="settings-error-message"><?=GetMessage('SAVE_SETTINGS_ERROR'); ?></p>
		<div id="absent_required_fields" class="absent-fields"></div>
	</div>
</div>
</form>
<?
$obJSPopup->StartButtons();
?>
<input type="button" id="save_settings" value="<?echo GetMessage("admin_lib_sett_save")?>" onclick="<?echo $this->name?>.SaveSettings(this);" title="<?echo GetMessage("admin_lib_sett_save_title")?>" class="adm-btn-save">
<input type="button" value="<?echo GetMessage("admin_lib_sett_cancel")?>" onclick="<?echo $this->name?>.CloseSettings()" title="<?echo GetMessage("admin_lib_sett_cancel_title")?>">
<input type="button" value="<?echo GetMessage("admin_lib_sett_reset")?>" onclick="if(confirm('<?echo GetMessage("admin_lib_sett_reset_ask")?>'))<?echo $this->name?>.DeleteSettings()" title="<?echo GetMessage("admin_lib_sett_reset_title")?>">
<?
$obJSPopup->EndButtons();
?>