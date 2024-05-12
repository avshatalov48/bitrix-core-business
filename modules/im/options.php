<?php

use \Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);
Loc::loadMessages($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/options.php');

$module_id = 'im';
CModule::IncludeModule($module_id);

$MOD_RIGHT = $APPLICATION->GetGroupRight($module_id);

if (CIMConvert::ConvertCount() > 0)
{
	$aMenu = array(
		array(
			"TEXT"=>Loc::getMessage("IM_OPTIONS_CONVERT"),
			"LINK"=>"im_convert.php?lang=".LANGUAGE_ID,
			"TITLE"=>Loc::getMessage("IM_OPTIONS_CONVERT_TITLE"),
		),
	);
	$context = new CAdminContextMenu($aMenu);
	$context->Show();
}


$arDefaultValues['default'] = array(
	'user_name_template' => CIMContactList::GetUserNameTemplate(false,false,true)
);
if (!IsModuleInstalled('intranet'))
{
	$arDefaultValues['default']['path_to_user_profile'] = '/club/user/#user_id#/';
}

$arDefaultValues['extranet'] = array(
	'user_name_template' => CIMContactList::GetUserNameTemplate(false,false,true)
);

$dbSites = CSite::GetList('', '', Array("ACTIVE" => "Y"));
$arSites = array();
$aSubTabs = array();
while ($site = $dbSites->Fetch())
{
	$site["ID"] = htmlspecialcharsbx($site["ID"]);
	$site["NAME"] = htmlspecialcharsbx($site["NAME"]);
	$arSites[] = $site;

	$aSubTabs[] = array("DIV" => "opt_site_".$site["ID"], "TAB" => "(".$site["ID"].") ".$site["NAME"], 'TITLE' => '');
}
$subTabControl = new CAdminViewTabControl("subTabControl", $aSubTabs);

$aTabs = array(
	array(
		"DIV" => "edit1", "TAB" => Loc::getMessage("IM_TAB_SETTINGS"), "ICON" => "im_path", "TITLE" => Loc::getMessage("IM_TAB_TITLE_SETTINGS"),
	),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);

if($_POST['Update'].$_GET['RestoreDefaults'] <> '' && check_bitrix_sessid() && $MOD_RIGHT >= 'W')
{
	if($_GET['RestoreDefaults'] <> '')
	{
		COption::RemoveOption("im", "turn_server_self");
		COption::RemoveOption("im", "turn_server");
		COption::RemoveOption("im", "turn_server_firefox");
		COption::RemoveOption("im", "turn_server_login");
		COption::RemoveOption("im", "turn_server_password");
		COption::RemoveOption("im", "call_server_enabled");

		COption::RemoveOption("im", "view_offline");
		COption::RemoveOption("im", "view_group");
		COption::RemoveOption("im", "send_by_enter");
		COption::RemoveOption("im", "correct_text");
		COption::RemoveOption("im", "panel_position_horizontal");
		COption::RemoveOption("im", "panel_position_vertical");
		COption::RemoveOption("im", "load_last_message");
		COption::RemoveOption("im", "load_last_notify");
		COption::RemoveOption("im", "privacy_message");
		COption::RemoveOption("im", "privacy_chat");
		COption::RemoveOption("im", "privacy_call");
		COption::RemoveOption("im", "privacy_search");
		COption::RemoveOption("im", "privacy_profile");

		COption::RemoveOption("im", "color_enable");
		COption::RemoveOption("im", "open_chat_enable");
		COption::RemoveOption("im", "general_chat_message_join");
		COption::RemoveOption("im", "general_chat_message_leave");
		COption::RemoveOption("im", "chat_message_start");
		COption::RemoveOption("im", "contact_list_birthday");

		foreach($arSites as $site)
		{
			$arDefValues = $site["LID"] == 'ex'? $arDefaultValues['extranet']: $arDefaultValues['default'];
			foreach($arDefValues as $key=>$value)
			{
				if (in_array($key, Array("user_name_template")))
					COption::RemoveOption("im", $key);
				else
					COption::SetOptionString("im", $key, $value, false, $site["LID"]);
			}
		}
	}
	elseif($_POST['Update'] <> '')
	{
		foreach($arSites as $site)
		{
			foreach($arDefaultValues['default'] as $key=>$value)
			{
				if (isset($_POST[$key."_".$site["LID"]]))
				{
					if (empty($_POST[$key."_".$site["LID"]]) && ($key == "user_name_template"))
						COption::RemoveOption("im", "user_name_template", $site["LID"]);
					else
						COption::SetOptionString("im", $key, $_POST[$key."_".$site["LID"]], false, $site["LID"]);
				}
			}
		}
		COption::SetOptionString("im", "turn_server_self", isset($_POST["TURN_SERVER_SELF"])? 'Y': 'N');
		if (isset($_POST["TURN_SERVER_SELF"]))
		{
			COption::SetOptionString("im", "turn_server", $_POST["TURN_SERVER"]);
			COption::SetOptionString("im", "turn_server_firefox", $_POST["TURN_SERVER_FIREFOX"]);
			COption::SetOptionString("im", "turn_server_login", $_POST["TURN_SERVER_LOGIN"]);
			COption::SetOptionString("im", "turn_server_password", $_POST["TURN_SERVER_PASSWORD"]);
		}
		else
		{
			COption::RemoveOption("im", "turn_server");
			COption::RemoveOption("im", "turn_server_firefox");
			COption::RemoveOption("im", "turn_server_login");
			COption::RemoveOption("im", "turn_server_password");
		}

		if ($_POST['PANEL_LOCATION'] == 'TL')
		{
			$_POST['PANEL_POSITION_VERTICAL'] = 'top';
			$_POST['PANEL_POSITION_HORIZONTAL'] = 'left';
		}
		elseif ($_POST['PANEL_LOCATION'] == 'TR')
		{
			$_POST['PANEL_POSITION_VERTICAL'] = 'top';
			$_POST['PANEL_POSITION_HORIZONTAL'] = 'right';
		}
		elseif ($_POST['PANEL_LOCATION'] == 'TC')
		{
			$_POST['PANEL_POSITION_VERTICAL'] = 'top';
			$_POST['PANEL_POSITION_HORIZONTAL'] = 'center';
		}
		elseif ($_POST['PANEL_LOCATION'] == 'BL')
		{
			$_POST['PANEL_POSITION_VERTICAL'] = 'bottom';
			$_POST['PANEL_POSITION_HORIZONTAL'] = 'left';
		}
		elseif ($_POST['PANEL_LOCATION'] == 'BR')
		{
			$_POST['PANEL_POSITION_VERTICAL'] = 'bottom';
			$_POST['PANEL_POSITION_HORIZONTAL'] = 'right';
		}
		elseif ($_POST['PANEL_LOCATION'] == 'BC')
		{
			$_POST['PANEL_POSITION_VERTICAL'] = 'bottom';
			$_POST['PANEL_POSITION_HORIZONTAL'] = 'center';
		}

		$_POST['START_CHAT_MESSAGE'] = $_POST['START_CHAT_MESSAGE'] == 'first'? 'first': 'last';
		$_POST['COLOR_ENABLE'] = isset($_POST['COLOR_ENABLE']);
		$_POST['OPEN_CHAT_ENABLE'] = isset($_POST['OPEN_CHAT_ENABLE']);
		$_POST['CALL_SERVER_ENABLED'] = isset($_POST['CALL_SERVER_ENABLED']);

		$arSettings = CIMSettings::checkValues(CIMSettings::SETTINGS, Array(
			'viewOffline' => !isset($_POST['VIEW_OFFLINE']),
			'viewGroup' => !isset($_POST['VIEW_GROUP']),
			'sendByEnter' => $_POST['SEND_BY_ENTER'] == 'Y',
			'correctText' => $_POST['CORRECT_TEXT'] == 'Y',
			'panelPositionHorizontal' => $_POST['PANEL_POSITION_HORIZONTAL'],
			'panelPositionVertical' => $_POST['PANEL_POSITION_VERTICAL'],
			'loadLastNotify' => isset($_POST['LOAD_LAST_NOTIFY']),
			'privacyMessage' => $_POST['PRIVACY_MESSAGE'],
			'privacyChat' => $_POST['PRIVACY_CHAT'],
			'privacyCall' => $_POST['PRIVACY_CALL'],
			'privacySearch' => $_POST['PRIVACY_SEARCH'],
			'privacyProfile' => $_POST['PRIVACY_PROFILE'],
		));

		if (in_array($_POST['CONTACT_LIST_BIRTHDAY'], ['all', 'department', 'none'], true))
		{
			COption::SetOptionString("im", "contact_list_birthday", $_POST['CONTACT_LIST_BIRTHDAY']);
		}

		COption::SetOptionString("im", "view_offline", $arSettings['viewOffline']);
		COption::SetOptionString("im", "view_group", $arSettings['viewGroup']);
		COption::SetOptionString("im", "send_by_enter", $arSettings['sendByEnter']);
		COption::SetOptionString("im", "correct_text", $arSettings['correctText']);
		COption::SetOptionString("im", "panel_position_horizontal", $arSettings['panelPositionHorizontal']);
		COption::SetOptionString("im", "panel_position_vertical", $arSettings['panelPositionVertical']);
		COption::SetOptionString("im", "load_last_notify", $arSettings['loadLastNotify']);
		COption::SetOptionString("im", "privacy_message", $arSettings['privacyMessage']);
		COption::SetOptionString("im", "privacy_chat", $arSettings['privacyChat']);
		COption::SetOptionString("im", "privacy_call", $arSettings['privacyCall']);
		COption::SetOptionString("im", "privacy_search", $arSettings['privacySearch']);
		COption::SetOptionString("im", "privacy_profile", $arSettings['privacyProfile']);
		COption::SetOptionString("im", "call_server_enabled", $_POST['CALL_SERVER_ENABLED']);
		COption::SetOptionString("im", "start_chat_message", $_POST['START_CHAT_MESSAGE']);
		COption::SetOptionString("im", "color_enable", $_POST['COLOR_ENABLE']);
		COption::SetOptionString("im", "open_chat_enable", $_POST['OPEN_CHAT_ENABLE']);

		if (IsModuleInstalled('intranet'))
		{
			COption::SetOptionString("im", "contact_list_load", isset($_POST['CONTACT_LIST_LOAD']));
			COption::SetOptionString("im", "general_chat_message_join", isset($_POST['GENERAL_CHAT_MESSAGE_JOIN']));
			COption::SetOptionString("im", "general_chat_message_leave", isset($_POST['GENERAL_CHAT_MESSAGE_LEAVE']));
		}

		if($Update <> '' && $_REQUEST["back_url_settings"] <> '')
		{
			LocalRedirect($_REQUEST["back_url_settings"]);
		}
		else
		{
			LocalRedirect($APPLICATION->GetCurPage()."?mid=".urlencode($mid)."&lang=".urlencode(LANGUAGE_ID)."&back_url_settings=".urlencode($_REQUEST["back_url_settings"])."&".$tabControl->ActiveTabParam());
		}
	}
}
?>
<form method="post" action="<?echo $APPLICATION->GetCurPage()?>?mid=<?=htmlspecialcharsbx($mid)?>&lang=<?echo LANG?>">
<?php echo bitrix_sessid_post()?>
<?php
$tabControl->Begin();
$tabControl->BeginNextTab();
$selfVideoServer = COption::GetOptionString("im", "turn_server_self") == 'Y'? true: false;

$arSettingsDefault = CIMSettings::GetDefaultSettings(CIMSettings::SETTINGS);
if ($arSettingsDefault['panelPositionVertical'] == 'top' && $arSettingsDefault['panelPositionHorizontal'] == 'left')
	$arSettingsDefault['location'] = 'TL';
else if ($arSettingsDefault['panelPositionVertical'] == 'top' && $arSettingsDefault['panelPositionHorizontal'] == 'right')
	$arSettingsDefault['location'] = 'TR';
else if ($arSettingsDefault['panelPositionVertical'] == 'top' && $arSettingsDefault['panelPositionHorizontal'] == 'center')
	$arSettingsDefault['location'] = 'TC';
else if ($arSettingsDefault['panelPositionVertical'] == 'bottom' && $arSettingsDefault['panelPositionHorizontal'] == 'left')
	$arSettingsDefault['location'] = 'BL';
else if ($arSettingsDefault['panelPositionVertical'] == 'bottom' && $arSettingsDefault['panelPositionHorizontal'] == 'right')
	$arSettingsDefault['location'] = 'BR';
else if ($arSettingsDefault['panelPositionVertical'] == 'bottom' && $arSettingsDefault['panelPositionHorizontal'] == 'center')
	$arSettingsDefault['location'] = 'BC';

$arReferenceId = Array(
	'select' => Array('all', 'contact'),
	'select3' => Array('all', 'contact', 'nobody'),
	'sendByEnter' => Array('Y', 'N'),
	'location' => Array('TL', 'TR', 'TC', 'BL', 'BR', 'BC'),
	'birthday' => Array('all', 'department', 'none'),
);
$arReference = Array(
	'select1' => Array(Loc::getMessage('IM_SELECT_1'), Loc::getMessage('IM_SELECT_2')),
	'select2' => Array(Loc::getMessage('IM_SELECT_1_2'), Loc::getMessage('IM_SELECT_2_2')),
	'select3' => Array(Loc::getMessage('IM_SELECT_1_2'), Loc::getMessage('IM_SELECT_2_2'), Loc::getMessage('IM_SELECT_2_3')),
	'sendByEnter' => Array("Enter", "Ctrl+Enter"),
	'location' => Array(Loc::getMessage('IM_PANEL_LOCATION_TL'), Loc::getMessage('IM_PANEL_LOCATION_TR'), Loc::getMessage('IM_PANEL_LOCATION_TC'), Loc::getMessage('IM_PANEL_LOCATION_BL'), Loc::getMessage('IM_PANEL_LOCATION_BR'), Loc::getMessage('IM_PANEL_LOCATION_BC')),
	'birthday' => Array(Loc::getMessage('IM_CONTACT_LIST_BIRTHDAY_ALL'), Loc::getMessage('IM_CONTACT_LIST_BIRTHDAY_DEPARTMENT'), Loc::getMessage('IM_CONTACT_LIST_BIRTHDAY_NONE')),
);
?>
	<tr>
		<td class="adm-detail-content-cell-l" width="40%"><?=Loc::getMessage("IM_OPTIONS_CALL_SERVER_ENABLED_MSGVER_1")?>:</td>
		<td class="adm-detail-content-cell-r" width="60%"><input type="checkbox" name="CALL_SERVER_ENABLED" <?=(COption::GetOptionString("im", 'call_server_enabled')?'checked="checked"' :'')?>></td>
	</tr>
	<tr>
		<td class="adm-detail-content-cell-l" width="40%"><?=Loc::getMessage("IM_OPTIONS_TURN_SERVER_SELF_2")?>:</td>
		<td class="adm-detail-content-cell-r" width="60%"><input type="checkbox" onclick="toogleVideoOptions(this)" name="TURN_SERVER_SELF" <?=($selfVideoServer?'checked="checked"' :'')?>></td>
	</tr>
	<tr id="video_group_2" <?if (!$selfVideoServer):?>style="display: none"<?endif;?>>
		<td class="adm-detail-content-cell-l"><?=Loc::getMessage("IM_OPTIONS_TURN_SERVER")?>:</td>
		<td class="adm-detail-content-cell-r"><input type="input" size="40" value="<?=htmlspecialcharsbx(COption::GetOptionString("im", "turn_server"))?>" name="TURN_SERVER"></td>
	</tr>
	<tr id="video_group_3" <?if (!$selfVideoServer):?>style="display: none"<?endif;?>>
		<td class="adm-detail-content-cell-l"><?=Loc::getMessage("IM_OPTIONS_TURN_SERVER_FIREFOX")?>:</td>
		<td class="adm-detail-content-cell-r"><input type="input" size="40" value="<?=htmlspecialcharsbx(COption::GetOptionString("im", "turn_server_firefox"))?>" name="TURN_SERVER_FIREFOX"></td>
	</tr>
	<tr id="video_group_4" <?if (!$selfVideoServer):?>style="display: none"<?endif;?>>
		<td class="adm-detail-content-cell-l"><?=Loc::getMessage("IM_OPTIONS_TURN_SERVER_LOGIN")?>:</td>
		<td class="adm-detail-content-cell-r"><input type="input" size="20" value="<?=htmlspecialcharsbx(COption::GetOptionString("im", "turn_server_login"))?>" name="TURN_SERVER_LOGIN"></td>
	</tr>
	<tr id="video_group_5" <?if (!$selfVideoServer):?>style="display: none"<?endif;?>	>
		<td class="adm-detail-content-cell-l"><?=Loc::getMessage("IM_OPTIONS_TURN_SERVER_PASSWORD")?>:<br><small>(<?=Loc::getMessage("IM_OPTIONS_TURN_SERVER_PASSWORD_HINT")?>)</small></td>
		<td class="adm-detail-content-cell-r"><input type="input" size="20" value="<?=htmlspecialcharsbx(COption::GetOptionString("im", "turn_server_password"))?>" name="TURN_SERVER_PASSWORD"></td>
	</tr>
	<tr>
		<td align="right"></td>
		<td></td>
	</tr>
	<tr>
		<td class="adm-detail-content-cell-l" width="40%"><?=Loc::getMessage("IM_VIEW_OFFLINE")?>:</td>
		<td class="adm-detail-content-cell-r" width="60%"><input type="checkbox" name="VIEW_OFFLINE" <?=(!$arSettingsDefault['viewOffline']?'checked="checked"' :'')?>></td>
	</tr>
	<tr>
		<td class="adm-detail-content-cell-l" width="40%"><?=Loc::getMessage("IM_VIEW_GROUP")?>:</td>
		<td class="adm-detail-content-cell-r" width="60%"><input type="checkbox" name="VIEW_GROUP" <?=(!$arSettingsDefault['viewGroup']?'checked="checked"' :'')?>></td>
	</tr>
	<tr>
		<td class="adm-detail-content-cell-l" width="40%"><?=Loc::getMessage("IM_SEND_BY_ENTER")?></td>
		<td class="adm-detail-content-cell-r" width="60%"><?=SelectBoxFromArray("SEND_BY_ENTER", array('reference_id' => $arReferenceId['sendByEnter'], 'reference' => $arReference['sendByEnter']), $arSettingsDefault['sendByEnter']? 'Y': 'N');?></td>
	</tr>
	<?/*<tr>
		<td class="adm-detail-content-cell-l" width="40%"><?=Loc::getMessage("IM_CORRECT_TEXT")?></td>
		<td class="adm-detail-content-cell-r" width="60%"><input type="checkbox" name="CORRECT_TEXT" value="Y" <?=($arSettingsDefault['correctText']?'checked="checked"' :'')?>></td>
	</tr>*/?>
	<tr>
		<td class="adm-detail-content-cell-l" width="40%"><?=Loc::getMessage("IM_PANEL_LOCATION")?></td>
		<td class="adm-detail-content-cell-r" width="60%"><?=SelectBoxFromArray("PANEL_LOCATION", array('reference_id' => $arReferenceId['location'], 'reference' => $arReference['location']), $arSettingsDefault['location']);?></td>
	</tr>
	<?if(!IsModuleInstalled('intranet')):?>
	<tr>
		<td class="adm-detail-content-cell-l" width="40%"><?=Loc::getMessage("IM_PRIVACY_MESS")?></td>
		<td class="adm-detail-content-cell-r" width="60%"><?=SelectBoxFromArray("PRIVACY_MESSAGE", array('reference_id' => $arReferenceId['select'], 'reference' => $arReference['select1']), $arSettingsDefault['privacyMessage']);?></td>
	</tr>
	<tr>
		<td class="adm-detail-content-cell-l" width="40%"><?=Loc::getMessage("IM_PRIVACY_CALL")?></td>
		<td class="adm-detail-content-cell-r" width="60%"><?=SelectBoxFromArray("PRIVACY_CALL", array('reference_id' => $arReferenceId['select'], 'reference' => $arReference['select1']), $arSettingsDefault['privacyCall']);?></td>
	</tr>
	<tr>
		<td class="adm-detail-content-cell-l" width="40%"><?=Loc::getMessage("IM_PRIVACY_CHAT")?></td>
		<td class="adm-detail-content-cell-r" width="60%"><?=SelectBoxFromArray("PRIVACY_CHAT", array('reference_id' => $arReferenceId['select'], 'reference' => $arReference['select2']), $arSettingsDefault['privacyChat']);?></td>
	</tr>
	<tr>
		<td class="adm-detail-content-cell-l" width="40%"><?=Loc::getMessage("IM_PRIVACY_SEARCH")?></td>
		<td class="adm-detail-content-cell-r" width="60%"><?=SelectBoxFromArray("PRIVACY_SEARCH", array('reference_id' => $arReferenceId['select'], 'reference' => $arReference['select1']), $arSettingsDefault['privacySearch']);?></td>
	</tr>
	<?if(IsModuleInstalled('b24network')):?>
	<tr>
		<td class="adm-detail-content-cell-l" width="40%"><?=Loc::getMessage("IM_PRIVACY_PROFILE")?></td>
		<td class="adm-detail-content-cell-r" width="60%"><?=SelectBoxFromArray("PRIVACY_PROFILE", array('reference_id' => $arReferenceId['select3'], 'reference' => $arReference['select3']), $arSettingsDefault['privacyProfile']);?></td>
	</tr>
	<?endif;?>
	<?endif;?>
	<tr>
		<td class="adm-detail-content-cell-l" width="40%"><?=Loc::getMessage("IM_START_CHAT_MESSAGE")?></td>
		<td class="adm-detail-content-cell-r" width="60%"><?=SelectBoxFromArray("START_CHAT_MESSAGE", array('reference_id' => Array('first', 'last'), 'reference' => Array(Loc::getMessage('IM_START_CHAT_MESSAGE_FIRST'), Loc::getMessage('IM_START_CHAT_MESSAGE_LAST'))), COption::GetOptionString("im", 'start_chat_message'));?></td>
	</tr>
	<?if(IsModuleInstalled('intranet')):?>
	<tr>
		<td class="adm-detail-content-cell-l" width="40%"><?=Loc::getMessage("IM_CONTACT_LIST_BIRTHDAY")?></td>
		<td class="adm-detail-content-cell-r" width="60%"><?=SelectBoxFromArray("CONTACT_LIST_BIRTHDAY", array('reference_id' => $arReferenceId['birthday'], 'reference' => $arReference['birthday']), COption::GetOptionString("im", 'contact_list_birthday'));?></td>
	</tr>
	<?endif;?>
	<tr>
		<td class="adm-detail-content-cell-l" width="40%"><?=Loc::getMessage("IM_COLOR_ENABLE")?>:</td>
		<td class="adm-detail-content-cell-r" width="60%"><input type="checkbox" name="COLOR_ENABLE" <?=(COption::GetOptionString("im", 'color_enable')?'checked="checked"' :'')?>></td>
	</tr>
	<tr>
		<td class="adm-detail-content-cell-l" width="40%"><?=Loc::getMessage("IM_OPEN_CHAT_ENABLE")?>:</td>
		<td class="adm-detail-content-cell-r" width="60%"><input type="checkbox" name="OPEN_CHAT_ENABLE" <?=(COption::GetOptionString("im", 'open_chat_enable')?'checked="checked"' :'')?>></td>
	</tr>
	<?if(IsModuleInstalled('intranet')):?>
	<tr>
		<td class="adm-detail-content-cell-l" width="40%"><?=Loc::getMessage("IM_CONTACT_LIST_LOAD")?>:</td>
		<td class="adm-detail-content-cell-r" width="60%"><input type="checkbox" name="CONTACT_LIST_LOAD" <?=(COption::GetOptionString("im", 'contact_list_load')?'checked="checked"' :'')?>></td>
	</tr>
	<tr>
		<td class="adm-detail-content-cell-l" width="40%"><?=Loc::getMessage("IM_GENERAL_CHAT_MESSAGE_JOIN")?>:</td>
		<td class="adm-detail-content-cell-r" width="60%"><input type="checkbox" name="GENERAL_CHAT_MESSAGE_JOIN" <?=(COption::GetOptionString("im", 'general_chat_message_join')?'checked="checked"' :'')?>></td>
	</tr>
	<tr>
		<td class="adm-detail-content-cell-l" width="40%"><?=Loc::getMessage("IM_GENERAL_CHAT_MESSAGE_LEAVE")?>:</td>
		<td class="adm-detail-content-cell-r" width="60%"><input type="checkbox" name="GENERAL_CHAT_MESSAGE_LEAVE" <?=(COption::GetOptionString("im", 'general_chat_message_leave')?'checked="checked"' :'')?>></td>
	</tr>
	<?endif;?>
	<tr>
		<td align="right"></td>
		<td></td>
	</tr>
	<tr>
		<td colspan="2">
<?php
$subTabControl->Begin();
foreach ($arSites as $site)
{
	$subTabControl->BeginNextTab();
?>
	<table width="75%" align="center">
<?php
	$arDefValues = $site["LID"] == 'ex'? $arDefaultValues['extranet']: $arDefaultValues['default'];
	foreach($arDefValues as $key=>$value)
	{
		if ($key == "user_name_template")
		{
	?>
		<tr>
			<td align ="right" valign="middle" width="50%"><?=Loc::getMessage("IM_OPTIONS_NAME_TEMPLATE");?>:</td>
			<td>
				<?$curVal = CIMContactList::GetUserNameTemplate($site["LID"]);?>
				<select name="<?=$key?>_<?=$site["LID"]?>">
					<?
					$arNameTemplates = CSite::GetNameTemplates();
					$arNameTemplates = array_reverse($arNameTemplates, true); //prepend array with default '' => Site Format value
					$arNameTemplates[""] = Loc::getMessage("IM_OPTIONS_NAME_IN_IM_FORMAT");
					$arNameTemplates = array_reverse($arNameTemplates, true);
					foreach ($arNameTemplates as $template => $phrase)
					{
						$template = str_replace(array("#NOBR#","#/NOBR#"), array("",""), $template);
						?><option value="<?= $template?>" <?=(($template == $curVal) ? " selected" : "")?> ><?= $phrase?></option><?
					}
					?>
				</select>
			</td>
		</tr>
	<?
		}
		else
		{
?>
		<tr>
			<td align="right"><?php echo Loc::getMessage("IM_OPTIONS_".mb_strtoupper($key))?>:</td>
			<td><input type="text" size="40" value="<?=htmlspecialcharsbx(COption::GetOptionString("im", $key, $value, $site["LID"]))?>" name="<?php echo $key?>_<?php echo $site["LID"]?>"></td>
		</tr>

<?php
		}
	}
?>
	</table>
<?php
}
$subTabControl->End();
?>
		</td>
	</tr>
<?$tabControl->Buttons();?>
<script>
function toogleVideoOptions(el)
{
	BX.style(BX('video_group_2'), 'display', el.checked? 'table-row': 'none');
	BX.style(BX('video_group_3'), 'display', el.checked? 'table-row': 'none');
	BX.style(BX('video_group_4'), 'display', el.checked? 'table-row': 'none');
	BX.style(BX('video_group_5'), 'display', el.checked? 'table-row': 'none');

}
function RestoreDefaults()
{
	if(confirm('<?echo AddSlashes(Loc::getMessage('MAIN_HINT_RESTORE_DEFAULTS_WARNING'))?>'))
		window.location = "<?echo $APPLICATION->GetCurPage()?>?RestoreDefaults=Y&lang=<?echo LANG?>&mid=<?echo urlencode($mid)."&".bitrix_sessid_get();?>";
}
</script>
<input type="submit" name="Update" <?if ($MOD_RIGHT<'W') echo "disabled" ?> value="<?echo Loc::getMessage('MAIN_SAVE')?>">
<input type="reset" name="reset" value="<?echo Loc::getMessage('MAIN_RESET')?>">
<?=bitrix_sessid_post();?>
<input type="button" <?if ($MOD_RIGHT<'W') echo "disabled" ?> title="<?echo Loc::getMessage('MAIN_HINT_RESTORE_DEFAULTS')?>" OnClick="RestoreDefaults();" value="<?echo Loc::getMessage('MAIN_RESTORE_DEFAULTS')?>">
<?$tabControl->End();?>
</form>