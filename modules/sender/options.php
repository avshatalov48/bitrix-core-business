<?
$module_id = "sender";
$POST_RIGHT = $APPLICATION->GetGroupRight($module_id);
if($POST_RIGHT>="R") :

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/options.php");
IncludeModuleLangFile(__FILE__);

$arAllOptions = array(
	array("interval", GetMessage("opt_interval"), array("text", 10)),
	array("auto_method", GetMessage("opt_method"), array("selectbox", array("agent"=>GetMessage("opt_method_agent"), "cron"=>GetMessage("opt_method_cron")))),
	array("max_emails_per_hit", GetMessage("opt_max_per_hit"), array("text", 10)),
	array("auto_agent_interval", GetMessage("opt_auto_agent_interval"), array("text", 10)),
	array("max_emails_per_cron", GetMessage("opt_max_per_cron"), array("text", 10)),
	array("reiterate_method", GetMessage("opt_reiterate_method"), array("selectbox", array("agent"=>GetMessage("opt_method_agent"), "cron"=>GetMessage("opt_method_cron")))),
	array("thread_type", GetMessage("opt_threads_type"), array("selectbox", array
	("Single"=>GetMessage("opt_method_single_thread"),"Ten"=>GetMessage("opt_method_ten_threads")))),
	array("reiterate_interval", GetMessage("opt_reiterate_interval"), array("text", 10)),
	array("link_protocol", GetMessage("opt_link_protocol"), array("selectbox", array(""=>"http", "https"=>"https"))),
	array("track_mails", GetMessage("opt_track_mails"), array("checkbox", 35)),
	array("unsub_link", GetMessage("opt_unsub_link"), array("text", 35)),
	array("sub_link", GetMessage("opt_sub_link"), array("text", 35)),
	array("address_from", GetMessage("opt_address_from"), array("text-list", 3, 20)),
	array("address_send_to_me", GetMessage("opt_address_send_to_me"), array("text-list", 3, 20)),
	array("mail_headers", GetMessage("opt_mail_headers"), array("srlz-list", 3, 20))
);
$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("MAIN_TAB_SET"), "ICON" => "sender_settings", "TITLE" => GetMessage("MAIN_TAB_TITLE_SET")),
	array("DIV" => "edit2", "TAB" => GetMessage("MAIN_TAB_RIGHTS"), "ICON" => "sender_settings", "TITLE" => GetMessage("MAIN_TAB_TITLE_RIGHTS")),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);

if($REQUEST_METHOD=="POST" && $Update.$Apply.$RestoreDefaults <> '' && $POST_RIGHT=="W" && check_bitrix_sessid())
{
	if($RestoreDefaults <> '')
	{
		COption::RemoveOption("sender");
		$z = CGroup::GetList($v1="id",$v2="asc", array("ACTIVE" => "Y", "ADMIN" => "N"));
		while($zr = $z->Fetch())
			$APPLICATION->DelGroupRight($module_id, array($zr["ID"]));
	}
	else
	{
		foreach($arAllOptions as $arOption)
		{
			$name = $arOption[0];
			if($arOption[2][0]=="srlz-list")
			{
				$val = ${$name};
				TrimArr($val);
				sort($val);
				$val = serialize($val);
			}
			else if($arOption[2][0]=="text-list")
			{
				$val = "";
				$valCount = count(${$name});
				for($j=0; $j<$valCount; $j++)
				{
					if(trim(${$name}[$j]) <> '')
						$val .= ($val <> ""? ",":"").trim(${$name}[$j]);
				}
			}
			else
				$val=${$name};
			if($arOption[2][0] == "checkbox" && $val <> "Y")
				$val="N";

			COption::SetOptionString($module_id, $name, $val);
		}
	}

	CModule::IncludeModule('sender');
	\Bitrix\Sender\Runtime\Job::actualizeAll();

	$Update = $Update.$Apply;
	ob_start();
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin/group_rights.php");
	ob_end_clean();

	if($_REQUEST["back_url_settings"] <> '')
	{
		if(($Apply <> '') || ($RestoreDefaults <> ''))
			LocalRedirect($APPLICATION->GetCurPage()."?mid=".urlencode($module_id)."&lang=".urlencode(LANGUAGE_ID)."&back_url_settings=".urlencode($_REQUEST["back_url_settings"])."&".$tabControl->ActiveTabParam());
		else
			LocalRedirect($_REQUEST["back_url_settings"]);
	}
	else
	{
		LocalRedirect($APPLICATION->GetCurPage()."?mid=".urlencode($module_id)."&lang=".urlencode(LANGUAGE_ID)."&".$tabControl->ActiveTabParam());
	}
}

?>
<form method="post" action="<?echo $APPLICATION->GetCurPage()?>?mid=<?=urlencode($module_id)?>&amp;lang=<?=LANGUAGE_ID?>">
<?
$tabControl->Begin();
$tabControl->BeginNextTab();

	foreach($arAllOptions as $Option):
	$type = $Option[2];
	$val = COption::GetOptionString($module_id, $Option[0]);
	?>
	<tr>
		<td width="40%" <?if($type[0]=="textarea" || $type[0]=="text-list") echo 'class="adm-detail-valign-top"'?>>
			<label for="<?echo htmlspecialcharsbx($Option[0])?>"><?echo $Option[1]?></label>
		<td width="60%">
		<?
			if($type[0]=="checkbox"):
				?><input type="checkbox" name="<?echo htmlspecialcharsbx($Option[0])?>" id="<?echo htmlspecialcharsbx($Option[0])?>" value="Y"<?if($val=="Y")echo" checked";?>><?
			elseif($type[0]=="text"):
				?><input type="text" size="<?echo $type[1]?>" maxlength="255" value="<?echo htmlspecialcharsbx($val)?>" name="<?echo htmlspecialcharsbx($Option[0])?>"><?
			elseif($type[0]=="textarea"):
				?><textarea rows="<?echo $type[1]?>" cols="<?echo $type[2]?>" name="<?echo htmlspecialcharsbx($Option[0])?>"><?echo htmlspecialcharsbx($val)?></textarea><?
			elseif($type[0]=="text-list" || $type[0]=="srlz-list"):
				if ($type[0]=="srlz-list")
				{
					$aVal = !empty($val) ? unserialize($val) : '';
				}
				else
				{
					$aVal = explode(",", $val);
				}
				$aVal = is_array($aVal) ? $aVal : [];

				sort($aVal);
				$aValCount = count($aVal);
				for($j=0; $j<$aValCount; $j++):
					?><input type="text" size="<?echo $type[2]?>" value="<?echo htmlspecialcharsbx($aVal[$j])?>" name="<?echo htmlspecialcharsbx($Option[0])."[]"?>"><br><?
				endfor;
				for($j=0; $j<$type[1]; $j++):
					?><input type="text" size="<?echo $type[2]?>" value="" name="<?echo htmlspecialcharsbx($Option[0])."[]"?>"><br><?
				endfor;
			elseif($type[0]=="selectbox"):
				$arr = $type[1];
				$arr_keys = array_keys($arr);
				$alertWarning = '';
				if(in_array($Option[0], array('auto_method', 'reiterate_method')) && !CheckVersion(SM_VERSION, '15.0.9'))
					$alertWarning = 'onchange="if(this.value==\'cron\')alert(\''.GetMessage('opt_sender_cron_support').SM_VERSION.'.\');"';
				?><select name="<?echo htmlspecialcharsbx($Option[0])?>" <?=$alertWarning?>><?
					$arr_keys_count = count($arr_keys);
					for($j=0; $j<$arr_keys_count; $j++):
						?><option value="<?echo $arr_keys[$j]?>"<?if($val==$arr_keys[$j])echo" selected"?>><?echo htmlspecialcharsbx($arr[$arr_keys[$j]])?></option><?
					endfor;
					?></select><?
			endif;
		?></td>
	</tr>
	<?endforeach?>
<?$tabControl->BeginNextTab();?>
<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin/group_rights.php");?>
<?$tabControl->Buttons();?>
	<input <?if ($POST_RIGHT<"W") echo "disabled" ?> type="submit" name="Update" value="<?=GetMessage("MAIN_SAVE")?>" title="<?=GetMessage("MAIN_OPT_SAVE_TITLE")?>" class="adm-btn-save">
	<input <?if ($POST_RIGHT<"W") echo "disabled" ?> type="submit" name="Apply" value="<?=GetMessage("MAIN_OPT_APPLY")?>" title="<?=GetMessage("MAIN_OPT_APPLY_TITLE")?>">
	<?if($_REQUEST["back_url_settings"] <> ''):?>
		<input <?if ($POST_RIGHT<"W") echo "disabled" ?> type="button" name="Cancel" value="<?=GetMessage("MAIN_OPT_CANCEL")?>" title="<?=GetMessage("MAIN_OPT_CANCEL_TITLE")?>" onclick="window.location='<?echo htmlspecialcharsbx(CUtil::addslashes($_REQUEST["back_url_settings"]))?>'">
		<input type="hidden" name="back_url_settings" value="<?=htmlspecialcharsbx($_REQUEST["back_url_settings"])?>">
	<?endif?>
	<input <?if ($POST_RIGHT<"W") echo "disabled" ?> type="submit" name="RestoreDefaults" title="<?echo GetMessage("MAIN_HINT_RESTORE_DEFAULTS")?>" OnClick="return confirm('<?echo AddSlashes(GetMessage("MAIN_HINT_RESTORE_DEFAULTS_WARNING"))?>')" value="<?echo GetMessage("MAIN_RESTORE_DEFAULTS")?>">
	<?=bitrix_sessid_post();?>
<?$tabControl->End();?>
</form>
<?endif;?>
