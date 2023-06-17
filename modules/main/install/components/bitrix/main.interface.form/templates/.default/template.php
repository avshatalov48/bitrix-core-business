<?
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2013 Bitrix
 */

/**
 * Bitrix vars
 *
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponentTemplate $this
 * @global CMain $APPLICATION
 * @global CUser $USER
 */

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

//color schemes
if($arParams["USE_THEMES"])
	$arThemes = CGridOptions::GetThemes($this->GetFolder());
else
	$arThemes = array();
?>

<div class="bx-interface-form">

<script type="text/javascript">
var bxForm_<?=$arParams["FORM_ID"]?> = null;
</script>

<?if($arParams["SHOW_FORM_TAG"]):?>
<form name="form_<?=$arParams["FORM_ID"]?>" id="form_<?=$arParams["FORM_ID"]?>" action="<?=POST_FORM_ACTION_URI?>" method="POST" enctype="multipart/form-data">

<?=bitrix_sessid_post();?>
<input type="hidden" id="<?=$arParams["FORM_ID"]?>_active_tab" name="<?=$arParams["FORM_ID"]?>_active_tab" value="<?=htmlspecialcharsbx($arResult["SELECTED_TAB"])?>">
<?endif?>
			<table cellspacing="0" class="bx-edit-tabs" width="100%">
				<tr>
					<td class="bx-tab-indent"><div class="empty"></div></td>
<?
$nTabs = count($arResult["TABS"]);
foreach($arResult["TABS"] as $tab):
	$bSelected = ($tab["id"] == $arResult["SELECTED_TAB"]);

	$callback = '';
	if(isset($tab['onselect_callback']) && $tab['onselect_callback'] <> '')
	{
		$callback = trim($tab['onselect_callback']);
		if(!preg_match('#^[a-z0-9-_\.]+$#i', $callback))
		{
			$callback = '';
		}
	}
?>
					<td title="<?=htmlspecialcharsbx($tab["title"] ?? '')?>" id="tab_cont_<?=$tab["id"]?>" class="bx-tab-container<?=($bSelected? "-selected":"")?>" onclick="<? if($callback <> ''):?><?= $callback ?>('<?=$tab["id"]?>');<?endif?>bxForm_<?=$arParams["FORM_ID"]?>.SelectTab('<?=$tab["id"]?>');" onmouseover="if(window.bxForm_<?=$arParams["FORM_ID"]?>){bxForm_<?=$arParams["FORM_ID"]?>.HoverTab('<?=$tab["id"]?>', true);}" onmouseout="if(window.bxForm_<?=$arParams["FORM_ID"]?>){bxForm_<?=$arParams["FORM_ID"]?>.HoverTab('<?=$tab["id"]?>', false);}">
						<table cellspacing="0">
							<tr>
								<td class="bx-tab-left<?=($bSelected? "-selected":"")?>" id="tab_left_<?=$tab["id"]?>"><div class="empty"></div></td>
								<td class="bx-tab<?=($bSelected? "-selected":"")?>" id="tab_<?=$tab["id"]?>"><?=htmlspecialcharsbx($tab["name"])?></td>
								<td class="bx-tab-right<?=($bSelected? "-selected":"")?>" id="tab_right_<?=$tab["id"]?>"><div class="empty"></div></td>
							</tr>
						</table>
					</td>
<?
endforeach;
?>
					<td width="100%"<?if($USER->IsAuthorized() && $arParams["SHOW_SETTINGS"] == true):?> ondblclick="bxForm_<?=$arParams["FORM_ID"]?>.ShowSettings()"<?endif?> style="white-space:nowrap; text-align:right">
<?if(count($arResult["TABS"]) > 1 && $arParams["CAN_EXPAND_TABS"] == true):?>
<a href="javascript:void(0)" onclick="bxForm_<?=$arParams["FORM_ID"]?>.ToggleTabs();" title="<?echo GetMessage("interface_form_show_all")?>" id="bxForm_<?=$arParams["FORM_ID"]?>_expand_link" class="bx-context-button bx-down"><span></span></a>
<?endif?>
<?if($arParams["SHOW_SETTINGS"] || !empty($arThemes)):?>
<a href="javascript:void(0)" onclick="bxForm_<?=$arParams["FORM_ID"]?>.menu.ShowMenu(this, bxForm_<?=$arParams["FORM_ID"]?>.settingsMenu);" title="<?echo GetMessage("interface_form_settings")?>" class="bx-context-button bx-form-menu"><span></span></a>
<?endif;?>
					</td>
				</tr>
			</table>
			<table cellspacing="0" class="bx-edit-tab">
				<tr>
					<td>
<?
$bWasRequired = false;
foreach($arResult["TABS"] as $tab):
?>
<div id="inner_tab_<?=$tab["id"]?>" class="bx-edit-tab-inner"<?if($tab["id"] <> $arResult["SELECTED_TAB"]) echo ' style="display:none;"'?>>
<div style="height: 100%;">
<?if(isset($tab["title"]) && $tab["title"] <> ''):?>
	<div class="bx-edit-tab-title">
	<table cellpadding="0" cellspacing="0" border="0" class="bx-edit-tab-title">
		<tr>
	<?
		if($tab["icon"] <> ""):
	?>
			<td class="bx-icon"><div class="<?=htmlspecialcharsbx($tab["icon"])?>"></div></td>
	<?
		endif
	?>
			<td class="bx-form-title"><?=htmlspecialcharsbx($tab["title"])?></td>
		</tr>
	</table>
	</div>
<?endif;?>

<div class="bx-edit-table">
<table cellpadding="0" cellspacing="0" border="0" class="bx-edit-table <?=(isset($tab["class"]) ? $tab['class'] : '')?>" id="<?=$tab["id"]?>_edit_table">
<?
$i = 0;
$cnt = count($tab["fields"]);
$prevType = '';
foreach($tab["fields"] as $field):
	$style = '';
	if(isset($field["show"]))
	{
		if($field["show"] == "N")
		{
			$style = "display:none;";
		}
	}

	$i++;
	if(!is_array($field))
		continue;

	$className = array();
	if($i == 1)
		$className[] = 'bx-top';
	if($i == $cnt)
		$className[] = 'bx-bottom';
	if($prevType == 'section')
		$className[] = 'bx-after-heading';

	if(isset($field['class']) && $field['class'] <> '')
	{
		$className[] = $field['class'];
	}
?>
	<tr<?if(!empty($className)):?> class="<?=implode(' ', $className)?>"<?endif?><?if(!empty($style)):?> style="<?= $style ?>"<?endif?>>
<?
if(isset($field["type"]) && $field["type"] == 'section'):
?>
		<td colspan="2" class="bx-heading"><?=htmlspecialcharsbx($field["name"])?></td>
<?
else:
	$val = $field["value"] ?? ($arParams["~DATA"][$field["id"]] ?? null);
	$valEncoded = '';
	if(!is_array($val))
		$valEncoded = htmlspecialcharsbx(htmlspecialcharsback($val));

	//default attributes
	if (!isset($field["params"]) || !is_array($field["params"]))
	{
		$field["params"] = [];
	}

	if (!isset($field["type"]) || $field["type"] == '' || $field["type"] == 'text')
	{
		if (!isset($field["params"]["size"]) || $field["params"]["size"] == '')
		{
			$field["params"]["size"] = "30";
		}
	}
	elseif($field["type"] == 'textarea')
	{
		if (!isset($field["params"]["cols"]) || $field["params"]["cols"] == '')
		{
			$field["params"]["cols"] = "40";
		}
		if (!isset($field["params"]["rows"]) || $field["params"]["rows"] == '')
		{
			$field["params"]["rows"] = "3";
		}
	}
	elseif($field["type"] == 'date')
	{
		if (!isset($field["params"]["size"]) || $field["params"]["size"] == '')
		{
			$field["params"]["size"] = "10";
		}
	}

	$params = '';
	if(isset($field["params"]) && is_array($field["params"]) && (!isset($field["type"]) || $field["type"] <> 'file'))
	{
		foreach($field["params"] as $p=>$v)
			$params .= ' '.$p.'="'.$v.'"';
	}

	if(!isset($field["colspan"]) || $field["colspan"] <> true):
		if(isset($field["required"]) && $field["required"])
			$bWasRequired = true;
?>
		<td class="bx-field-name<?if(!isset($field["type"]) || $field["type"] <> 'label') echo' bx-padding'?>"<?if(isset($field["title"]) && $field["title"] <> '') echo ' title="'.htmlspecialcharsEx($field["title"]).'"'?>><?=(isset($field["required"]) && $field["required"]? '<span class="required">*</span>':'')?><? if(isset($field["name"]) && $field["name"] <> ''):?><?= htmlspecialcharsEx($field["name"]) ?>:<?endif?></td>
<?
	endif
?>
		<td class="bx-field-value"<?=(isset($field["colspan"]) && $field["colspan"]? ' colspan="2"':'')?>>
<?
	$type = $field["type"] ?? null;
	switch($type):
		case 'label':
		case 'custom':
			echo $val;
			break;
		case 'checkbox':
?>
<input type="hidden" name="<?=$field["id"]?>" value="N">
<input type="checkbox" name="<?=$field["id"]?>" value="Y"<?=($val == "Y"? ' checked':'')?><?=$params?>>
<?
			break;
		case 'textarea':
?>
<textarea name="<?=$field["id"]?>"<?=$params?>><?=$valEncoded?></textarea>
<?
			break;
		case 'list':
		case 'select':
?>
<select name="<?=$field["id"]?>"<?=$params?>>
<?
			if(is_array($field["items"])):
				if(!is_array($val))
					$val = array($val);
				foreach($field["items"] as $k=>$v):
?>
	<option value="<?=htmlspecialcharsbx($k)?>"<?=(in_array($k, $val)? ' selected':'')?>><?=htmlspecialcharsbx($v)?></option>
<?
				endforeach;
?>
</select>
<?
			endif;
			break;
		case 'file':
			$arDefParams = array("iMaxW"=>150, "iMaxH"=>150, "sParams"=>"border=0", "strImageUrl"=>"", "bPopup"=>true, "sPopupTitle"=>false, "size"=>20);
			foreach($arDefParams as $k=>$v)
				if(!array_key_exists($k, $field["params"]))
					$field["params"][$k] = $v;

			echo CFile::InputFile($field["id"], $field["params"]["size"], $val);
			if($val <> '')
				echo '<br>'.CFile::ShowImage($val, $field["params"]["iMaxW"], $field["params"]["iMaxH"], $field["params"]["sParams"], $field["params"]["strImageUrl"], $field["params"]["bPopup"], $field["params"]["sPopupTitle"]);

			break;
		case 'date':
		case 'date_short':
?>
<?$APPLICATION->IncludeComponent(
	"bitrix:main.calendar",
	"",
	array(
		"SHOW_INPUT"=>"Y",
		"INPUT_NAME"=>$field["id"],
		"INPUT_VALUE"=>$val,
		"INPUT_ADDITIONAL_ATTR"=>$params,
		"SHOW_TIME" => $field["type"] === 'date'? 'Y' : 'N',
	),
	$component,
	array("HIDE_ICONS"=>true)
);?>
<?
			break;
		default:
?>
<input type="text" name="<?=$field["id"]?>" value="<?=$valEncoded?>"<?=$params?>>
<?
			break;
	endswitch;
?>
		</td>
<?endif?>
	</tr>
<?
	$prevType = $field["type"] ?? null;
endforeach;
?>
</table>
</div>
</div>
</div>
<?
endforeach;
?>
					</td>
				</tr>
			</table>
<?
if(isset($arParams["BUTTONS"])):
?>
			<div class="bx-buttons">
<?if(!isset($arParams["~BUTTONS"]["standard_buttons"]) || $arParams["~BUTTONS"]["standard_buttons"] !== false):?>
	<?if($arParams["BUTTONS"]["back_url"] <> ''):?>
	<input type="submit" name="save" value="<?echo GetMessage("interface_form_save")?>" title="<?echo GetMessage("interface_form_save_title")?>" />
	<?endif?>
	<input type="submit" name="apply" value="<?echo GetMessage("interface_form_apply")?>" title="<?echo GetMessage("interface_form_apply_title")?>" />
	<?if($arParams["BUTTONS"]["back_url"] <> ''):?>
	<input type="button" value="<?echo GetMessage("interface_form_cancel")?>" name="cancel" onclick="window.location='<?=htmlspecialcharsbx(CUtil::addslashes($arParams["~BUTTONS"]["back_url"]))?>'" title="<?echo GetMessage("interface_form_cancel_title")?>" />
	<?endif?>
<?endif?>
<?=$arParams["~BUTTONS"]["custom_html"]?>
			</div>
<?endif?>
<?if($arParams["SHOW_FORM_TAG"]):?>
</form>
<?endif?>

<?if($USER->IsAuthorized() && $arParams["SHOW_SETTINGS"] == true):?>
<div style="display:none">

<div id="form_settings_<?=$arParams["FORM_ID"]?>">
<table width="100%">
	<tr class="section">
		<td><?echo GetMessage("interface_form_tabs")?></td>
	</tr>
	<tr>
		<td align="center">
			<table>
				<tr>
					<td style="background-image:none" nowrap>
						<select style="min-width:150px;" name="tabs" size="10" ondblclick="this.form.tab_edit_btn.onclick()" onchange="bxForm_<?=$arParams["FORM_ID"]?>.OnSettingsChangeTab()">
						</select>
					</td>
					<td style="background-image:none">
						<div style="margin-bottom:5px"><input type="button" name="tab_up_btn" value="<?echo GetMessage("intarface_form_up")?>" title="<?echo GetMessage("intarface_form_up_title")?>" style="width:80px;" onclick="bxForm_<?=$arParams["FORM_ID"]?>.TabMoveUp()"></div>
						<div style="margin-bottom:5px"><input type="button" name="tab_down_btn" value="<?echo GetMessage("intarface_form_up_down")?>" title="<?echo GetMessage("intarface_form_down_title")?>" style="width:80px;" onclick="bxForm_<?=$arParams["FORM_ID"]?>.TabMoveDown()"></div>
						<div style="margin-bottom:5px"><input type="button" name="tab_add_btn" value="<?echo GetMessage("intarface_form_add")?>" title="<?echo GetMessage("intarface_form_add_title")?>" style="width:80px;" onclick="bxForm_<?=$arParams["FORM_ID"]?>.TabAdd()"></div>
						<div style="margin-bottom:5px"><input type="button" name="tab_edit_btn" value="<?echo GetMessage("intarface_form_edit")?>" title="<?echo GetMessage("intarface_form_edit_title")?>" style="width:80px;" onclick="bxForm_<?=$arParams["FORM_ID"]?>.TabEdit()"></div>
						<div style="margin-bottom:5px"><input type="button" name="tab_del_btn" value="<?echo GetMessage("intarface_form_del")?>" title="<?echo GetMessage("intarface_form_del_title")?>" style="width:80px;" onclick="bxForm_<?=$arParams["FORM_ID"]?>.TabDelete()"></div>
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr class="section">
		<td><?echo GetMessage("intarface_form_fields")?></td>
	</tr>
	<tr>
		<td align="center">
			<table>
				<tr>
					<td style="background-image:none" nowrap>
						<div style="margin-bottom:5px"><?echo GetMessage("intarface_form_fields_available")?></div>
						<select style="min-width:150px;" name="all_fields" multiple size="12" ondblclick="this.form.add_btn.onclick()" onchange="bxForm_<?=$arParams["FORM_ID"]?>.ProcessButtons()">
						</select>
					</td>
					<td style="background-image:none">
						<div style="margin-bottom:5px"><input type="button" name="add_btn" value="&gt;" title="<?echo GetMessage("intarface_form_add_field")?>" style="width:30px;" disabled onclick="bxForm_<?=$arParams["FORM_ID"]?>.FieldsAdd()"></div>
						<div style="margin-bottom:5px"><input type="button" name="del_btn" value="&lt;" title="<?echo GetMessage("intarface_form_del_field")?>" style="width:30px;" disabled onclick="bxForm_<?=$arParams["FORM_ID"]?>.FieldsDelete()"></div>
					</td>
					<td style="background-image:none" nowrap>
						<div style="margin-bottom:5px"><?echo GetMessage("intarface_form_fields_on_tab")?></div>
						<select style="min-width:150px;" name="fields" multiple size="12" ondblclick="this.form.del_btn.onclick()" onchange="bxForm_<?=$arParams["FORM_ID"]?>.ProcessButtons()">
						</select>
					</td>
					<td style="background-image:none">
						<div style="margin-bottom:5px"><input type="button" name="up_btn" value="<?echo GetMessage("intarface_form_up")?>" title="<?echo GetMessage("intarface_form_up_title")?>" style="width:80px;" disabled onclick="bxForm_<?=$arParams["FORM_ID"]?>.FieldsMoveUp()"></div>
						<div style="margin-bottom:5px"><input type="button" name="down_btn" value="<?echo GetMessage("intarface_form_up_down")?>" title="<?echo GetMessage("intarface_form_down_title")?>" style="width:80px;" disabled onclick="bxForm_<?=$arParams["FORM_ID"]?>.FieldsMoveDown()"></div>
						<div style="margin-bottom:5px"><input type="button" name="field_add_btn" value="<?echo GetMessage("intarface_form_add")?>" title="<?echo GetMessage("intarface_form_add_sect")?>" style="width:80px;" onclick="bxForm_<?=$arParams["FORM_ID"]?>.FieldAdd()"></div>
						<div style="margin-bottom:5px"><input type="button" name="field_edit_btn" value="<?echo GetMessage("intarface_form_edit")?>" title="<?echo GetMessage("intarface_form_edit_field")?>" style="width:80px;" onclick="bxForm_<?=$arParams["FORM_ID"]?>.FieldEdit()"></div>
					</td>
				</tr>
			</table>
		</td>
	</tr>
<?if($arResult["IS_ADMIN"]):?>
	<tr class="section">
		<td><?echo GetMessage("interface_form_common")?></td>
	</tr>
	<tr>
		<td><input type="checkbox" name="set_default_settings" id="set_default_settings_<?=$arParams["FORM_ID"]?>" onclick="BX('delete_users_settings_<?=$arParams["FORM_ID"]?>').disabled = !this.checked;"><label for="set_default_settings_<?=$arParams["FORM_ID"]?>"><?echo GetMessage("interface_form_common_set")?></label></td>
	</tr>
	<tr>
		<td><input type="checkbox" name="delete_users_settings" id="delete_users_settings_<?=$arParams["FORM_ID"]?>" disabled><label for="delete_users_settings_<?=$arParams["FORM_ID"]?>"><?echo GetMessage("interface_form_common_del")?></label></td>
	</tr>
<?endif;?>
</table>
</div>

</div>
<?
endif //$GLOBALS['USER']->IsAuthorized()
?>

<?
$variables = array(
	"mess"=>array(
		"collapseTabs"=>GetMessage("interface_form_close_all"),
		"expandTabs"=>GetMessage("interface_form_show_all"),
		"settingsTitle"=>GetMessage("intarface_form_settings"),
		"settingsSave"=>GetMessage("interface_form_save"),
		"tabSettingsTitle"=>GetMessage("intarface_form_tab"),
		"tabSettingsSave"=>"OK",
		"tabSettingsName"=>GetMessage("intarface_form_tab_name"),
		"tabSettingsCaption"=>GetMessage("intarface_form_tab_title"),
		"fieldSettingsTitle"=>GetMessage("intarface_form_field"),
		"fieldSettingsName"=>GetMessage("intarface_form_field_name"),
		"sectSettingsTitle"=>GetMessage("intarface_form_sect"),
		"sectSettingsName"=>GetMessage("intarface_form_sect_name"),
	),
	"ajax"=>array(
		"AJAX_ID"=> $arParams["AJAX_ID"] ?? null,
		"AJAX_OPTION_SHADOW"=> isset($arParams["AJAX_OPTION_SHADOW"]) && $arParams["AJAX_OPTION_SHADOW"] === "Y",
	),
	"settingWndSize"=>CUtil::GetPopupSize("InterfaceFormSettingWnd"),
	"tabSettingWndSize"=>CUtil::GetPopupSize("InterfaceFormTabSettingWnd", array('width'=>400, 'height'=>200)),
	"fieldSettingWndSize"=>CUtil::GetPopupSize("InterfaceFormFieldSettingWnd", array('width'=>400, 'height'=>150)),
	"component_path"=>$component->GetRelativePath(),
	"template_path"=>$this->GetFolder(),
	"sessid"=>bitrix_sessid(),
	"current_url"=>$APPLICATION->GetCurPageParam("", array("bxajaxid", "AJAX_CALL")),
	"GRID_ID"=>$arParams["THEME_GRID_ID"],
);
?>
<script type="text/javascript">
var formSettingsDialog<?=$arParams["FORM_ID"]?>;

bxForm_<?=$arParams["FORM_ID"]?> = new BxInterfaceForm('<?=$arParams["FORM_ID"]?>', <?=CUtil::PhpToJsObject(array_keys($arResult["TABS"]))?>);
bxForm_<?=$arParams["FORM_ID"]?>.vars = <?=CUtil::PhpToJsObject($variables)?>;
<?if($arParams["SHOW_SETTINGS"] == true):?>
bxForm_<?=$arParams["FORM_ID"]?>.oTabsMeta = <?=CUtil::PhpToJsObject($arResult["TABS_META"])?>;
bxForm_<?=$arParams["FORM_ID"]?>.oFields = <?=CUtil::PhpToJsObject($arResult["AVAILABLE_FIELDS"])?>;
<?endif?>
<?
$settingsMenu = array();
if($arParams["SHOW_SETTINGS"])
{
	$settingsMenu[] = array(
		'TEXT' => GetMessage("intarface_form_mnu_settings"),
		'TITLE' => GetMessage("intarface_form_mnu_settings_title"),
		'ONCLICK' => 'bxForm_'.$arParams["FORM_ID"].'.ShowSettings()',
		'DEFAULT' => true,
		'DISABLED' => ($USER->IsAuthorized()? false:true),
		'ICONCLASS' => 'form-settings'
	);
	if(!empty($arResult["OPTIONS"]["tabs"]))
	{
		if($arResult["OPTIONS"]["settings_disabled"] == "Y")
		{
			$settingsMenu[] = array(
				'TEXT' => GetMessage("intarface_form_mnu_on"),
				'TITLE' => GetMessage("intarface_form_mnu_on_title"),
				'ONCLICK' => 'bxForm_'.$arParams["FORM_ID"].'.EnableSettings(true)',
				'DISABLED' => ($USER->IsAuthorized()? false:true),
				'ICONCLASS' => 'form-settings-on'
			);
		}
		else
		{
			$settingsMenu[] = array(
				'TEXT' => GetMessage("intarface_form_mnu_off"),
				'TITLE' => GetMessage("intarface_form_mnu_off_title"),
				'ONCLICK' => 'bxForm_'.$arParams["FORM_ID"].'.EnableSettings(false)',
				'DISABLED' => ($USER->IsAuthorized()? false:true),
				'ICONCLASS' => 'form-settings-off'
			);
		}
	}
}
if(!empty($arThemes))
{
	$themeItems = array();
	foreach($arThemes as $theme)
	{
		$themeItems[] = array(
			'TEXT' => $theme["name"].($theme["theme"] == $arResult["GLOBAL_OPTIONS"]["theme"]? ' '.GetMessage("interface_form_default"):''),
			'ONCLICK' => 'bxForm_'.$arParams["FORM_ID"].'.SetTheme(this, \''.$theme["theme"].'\')',
			'ICONCLASS' => ($theme["theme"] == $arResult["OPTIONS"]["theme"] || $theme["theme"] == "grey" && $arResult["OPTIONS"]["theme"] == ''? 'checked' : '')
		);
	}

	$settingsMenu[] = array(
		'TEXT' => GetMessage("interface_form_colors"),
		'TITLE' => GetMessage("interface_form_colors_title"),
		'CLASS' => 'bx-grid-themes-menu-item',
		'MENU' => $themeItems,
		'DISABLED' => ($USER->IsAuthorized()? false:true),
		'ICONCLASS' => 'form-themes'
	);
}
?>
bxForm_<?=$arParams["FORM_ID"]?>.settingsMenu = <?=CUtil::PhpToJsObject($settingsMenu)?>;

<?if(isset($arResult["OPTIONS"]["expand_tabs"]) && $arResult["OPTIONS"]["expand_tabs"] == "Y"):?>
BX.ready(function(){bxForm_<?=$arParams["FORM_ID"]?>.ToggleTabs(true);});
<?endif?>
</script>

</div>

<?if($bWasRequired):?>
<div class="bx-form-notes"><span class="required">*</span><?echo GetMessage("interface_form_required")?></div>
<?endif?>
