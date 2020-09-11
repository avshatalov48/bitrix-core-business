<?
use Bitrix\Main\Localization\Loc;

if(!$USER->IsAdmin())
	return;

if (!\Bitrix\Main\Loader::includeModule('messageservice'))
{
	return;
}

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/options.php");
Loc::loadMessages(__FILE__);

include_once(__DIR__.'/default_option.php');
$arDefaultValues['default'] = $messageservice_default_option;

$arAllOptions = array(
	//array("smsru_partner", GetMessage("MESSAGESERVICE_SMSRU_PARTNER"), $messageservice_default_option['smsru_partner'], array("text", 50)),
	//array("smsru_secret_key", GetMessage("MESSAGESERVICE_SMSRU_SECRET_KEY"), $messageservice_default_option['smsru_secret_key'], array("text", 50)),
	array("clean_up_period", GetMessage("MESSAGESERVICE_CLEAN_UP_PERIOD"), "14", array("text", 3)),
	array("queue_limit", GetMessage("MESSAGESERVICE_QUEUE_LIMIT"), "5", array("text", 3)),
);

$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("MAIN_TAB_SET"), "ICON" => "ib_settings", "TITLE" => GetMessage("MAIN_TAB_TITLE_SET")),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);

if($_SERVER["REQUEST_METHOD"]=="POST" && ($_POST['Update'] || $_POST['Apply'] || $_POST['RestoreDefaults'])>0 && check_bitrix_sessid())
{
	if($_POST['RestoreDefaults'] <> '')
	{
		$arDefValues = $arDefaultValues['default'];
		foreach($arDefValues as $key=>$value)
		{
			COption::RemoveOption("messageservice", $key);
		}
	}
	else
	{
		foreach($arAllOptions as $arOption)
		{
			$name=$arOption[0];
			$val=$_REQUEST[$name];
			if($arOption[3][0]=="checkbox" && $val!="Y")
				$val="N";
			COption::SetOptionString("messageservice", $name, $val, $arOption[1]);
		}
	}
	if($_POST['Update'] <> '' && $_REQUEST["back_url_settings"] <> '')
		LocalRedirect($_REQUEST["back_url_settings"]);
	else
		LocalRedirect($APPLICATION->GetCurPage()."?mid=".urlencode($mid)."&lang=".urlencode(LANGUAGE_ID)."&back_url_settings=".urlencode($_REQUEST["back_url_settings"])."&".$tabControl->ActiveTabParam());
}


$tabControl->Begin();
?>
<form method="post" action="<?echo $APPLICATION->GetCurPage()?>?mid=<?=urlencode($mid)?>&amp;lang=<?echo LANGUAGE_ID?>">
	<?$tabControl->BeginNextTab();?>
	<?
	foreach($arAllOptions as $arOption):
		$val = COption::GetOptionString("messageservice", $arOption[0], $arOption[2]);
		$type = $arOption[3];
		?>
		<tr>
			<td width="40%" nowrap <?if($type[0]=="textarea") echo 'class="adm-detail-valign-top"'?>>
				<label for="<?echo htmlspecialcharsbx($arOption[0])?>"><?echo $arOption[1]?>:</label>
			<td width="60%">
				<?if($type[0]=="checkbox"):?>
					<input type="checkbox" id="<?echo htmlspecialcharsbx($arOption[0])?>" name="<?echo htmlspecialcharsbx($arOption[0])?>" value="Y"<?if($val=="Y")echo" checked";?>>
				<?elseif($type[0]=="text"):?>
					<input type="text" size="<?echo $type[1]?>" maxlength="255" value="<?echo htmlspecialcharsbx($val)?>" name="<?echo htmlspecialcharsbx($arOption[0])?>">
				<?elseif($type[0]=="textarea"):?>
					<textarea rows="<?echo $type[1]?>" cols="<?echo $type[2]?>" name="<?echo htmlspecialcharsbx($arOption[0])?>"><?echo htmlspecialcharsbx($val)?></textarea>
				<?elseif($type[0]=="selectbox"):?>
					<select name="<?echo htmlspecialcharsbx($arOption[0])?>">
						<?
						foreach ($type[1] as $key => $value)
						{
							?><option value="<?= $key ?>"<?= ($key == $val) ? " selected" : "" ?>><?= $value ?></option><?
						}
						?>
					</select>
				<?endif?>
			</td>
		</tr>
	<?endforeach?>

	<tr>
		<td width="40%" nowrap>
			<label><?=GetMessage("MESSAGESERVICE_SMS_SENDER_LINK")?>:</label>
		<td width="60%" valign="top">
			<ul>
			<?
			foreach (Bitrix\MessageService\Sender\SmsManager::getSenders() as $sender):
				if (!$sender->isConfigurable())
				{
					continue;
				}
				/** @var \Bitrix\MessageService\Sender\BaseConfigurable $sender */
			?>
			<li><a href="<?=htmlspecialcharsbx($sender->getManageUrl())?>"><?=htmlspecialcharsbx($sender->getName())?></a></li>
			<?endforeach;?>
			</ul>
		</td>
	</tr>
	<tr>
		<td width="40%" nowrap>
			<label><?=GetMessage("MESSAGESERVICE_SMS_SENDER_LIMITS")?>:</label>
		<td width="60%" valign="top">
			<a href="messageservice_sender_limits.php"><?=GetMessage("MESSAGESERVICE_TUNE_LINK")?></a></li>
		</td>
	</tr>

	<?$tabControl->Buttons();?>
	<input type="submit" name="Update" value="<?=GetMessage("MAIN_SAVE")?>" title="<?=GetMessage("MAIN_OPT_SAVE_TITLE")?>" class="adm-btn-save">
	<input type="submit" name="Apply" value="<?=GetMessage("MAIN_OPT_APPLY")?>" title="<?=GetMessage("MAIN_OPT_APPLY_TITLE")?>">
	<?if($_REQUEST["back_url_settings"] <> ''):?>
		<input type="button" name="Cancel" value="<?=GetMessage("MAIN_OPT_CANCEL")?>" title="<?=GetMessage("MAIN_OPT_CANCEL_TITLE")?>" onclick="window.location='<?echo htmlspecialcharsbx(CUtil::addslashes($_REQUEST["back_url_settings"]))?>'">
		<input type="hidden" name="back_url_settings" value="<?=htmlspecialcharsbx($_REQUEST["back_url_settings"])?>">
	<?endif?>
	<input type="submit" name="RestoreDefaults" title="<?echo GetMessage("MAIN_HINT_RESTORE_DEFAULTS")?>" OnClick="return confirm('<?echo AddSlashes(GetMessage("MAIN_HINT_RESTORE_DEFAULTS_WARNING"))?>')" value="<?echo GetMessage("MAIN_RESTORE_DEFAULTS")?>">
	<?=bitrix_sessid_post();?>
	<?$tabControl->End();?>
</form>