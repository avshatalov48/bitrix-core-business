<?
##############################################
# Bitrix: SiteManager                        #
# Copyright (c) 2002-2004 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################
global $MESS;
IncludeModuleLangFile(__FILE__);
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/options.php");

$module_id = "mail";
CModule::IncludeModule($module_id);

$MOD_RIGHT = $APPLICATION->GetGroupRight($module_id);
if($MOD_RIGHT>="R"):

$arAllOptions =
	Array(
		Array("save_src", GetMessage("MAIL_OPTIONS_SAVE_SRC"), B_MAIL_SAVE_SRC, Array("checkbox", "Y")),
		Array("save_attachments", GetMessage("MAIL_OPTIONS_SAVE_ATTACHMENTS"), B_MAIL_SAVE_ATTACHMENTS, Array("checkbox", "Y")),
		Array("connect_timeout", GetMessage("MAIL_OPTIONS_TIMEOUT"), B_MAIL_TIMEOUT, Array("text", 2)),
		Array("spam_check", GetMessage("MAIL_OPTIONS_CHECKSPAM"), B_MAIL_CHECK_SPAM, Array("checkbox", "Y")),
		Array("time_keep_log", GetMessage("MAIL_OPTIONS_LOG_SAVE"), B_MAIL_KEEP_LOG, Array("text", 2))
		);

if($MOD_RIGHT>="W" && check_bitrix_sessid())
{
	if ($REQUEST_METHOD=="GET" && strlen($RestoreDefaults)>0)
	{
		COption::RemoveOption($module_id);
		$z = CGroup::GetList($v1="id",$v2="asc", array("ACTIVE" => "Y", "ADMIN" => "N"));
		while($zr = $z->Fetch())
			$APPLICATION->DelGroupRight($module_id, array($zr["ID"]));
	}

	if($REQUEST_METHOD=="POST" && strlen($Update)>0)
	{
		for($i=0; $i<count($arAllOptions); $i++)
		{
			$name=$arAllOptions[$i][0];
			$val=$$name;
			if($arAllOptions[$i][3][0]=="checkbox" && $val!="Y")
				$val="N";
			COption::SetOptionString("mail", $name, $val, $arAllOptions[$i][1]);
		}
		COption::SetOptionString("mail", "php_path", $php_path);
	}
}

$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("MAIN_TAB_SET"), "ICON" => "support_settings", "TITLE" => GetMessage("MAIN_TAB_TITLE_SET")),
	array("DIV" => "edit2", "TAB" => GetMessage("MAIN_TAB_SMTP"), "ICON" => "support_settings", "TITLE" => GetMessage("MAIN_TAB_SMTP_TITLE")),
	array("DIV" => "edit3", "TAB" => GetMessage("MAIN_TAB_RIGHTS"), "ICON" => "support_settings", "TITLE" => GetMessage("MAIN_TAB_TITLE_RIGHTS")),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);
?>
<?
$tabControl->Begin();
?><form method="POST" action="<?echo $APPLICATION->GetCurPage()?>?mid=<?=htmlspecialcharsbx($mid)?>&lang=<?=LANGUAGE_ID?>"><?
$tabControl->BeginNextTab();
	for($i=0; $i<count($arAllOptions); $i++):
		$Option = $arAllOptions[$i];
		$val = COption::GetOptionString("mail", $Option[0], $Option[2]);
		$type = $Option[3];
	?>
		<tr>
			<td valign="top" width="50%"><?if($type[0]=="checkbox")
							echo "<label for=\"".htmlspecialcharsbx($Option[0])."\">".$Option[1]."</label>";
						else
							echo $Option[1];?></td>
			<td valign="top" width="50%">
					<?if($type[0]=="checkbox"):?>
						<input type="checkbox" name="<?echo htmlspecialcharsbx($Option[0])?>" id="<?echo htmlspecialcharsbx($Option[0])?>" value="Y"<?if($val=="Y")echo" checked";?>>
					<?elseif($type[0]=="text"):?>
						<input type="text" size="<?echo $type[1]?>" maxlength="255" value="<?echo htmlspecialcharsbx($val)?>" name="<?echo htmlspecialcharsbx($Option[0])?>">
					<?elseif($type[0]=="textarea"):?>
						<textarea rows="<?echo $type[1]?>" cols="<?echo $type[2]?>" name="<?echo htmlspecialcharsbx($Option[0])?>"><?echo htmlspecialcharsbx($val)?></textarea>
					<?endif?>
			</td>
		</tr>
	<?
	endfor;
	?>
<?$tabControl->BeginNextTab();?>

<?$val = COption::GetOptionString("mail", "php_path", (StrToUpper(SubStr(PHP_OS, 0, 3)) === "WIN") ? "../apache/php.exe -c ../apache/php.ini" : "authbind php -c /etc/php.ini");?>
<script>
var ss = false;
function StartSMTPD()
{
	if(BX('php_path').value!='<?=AddSlashes($val)?>' && !confirm("<?echo GetMessage("MAIL_OPT_CONFIRM")?>"))
		return false;
	BX.showWait();

	BX('iStartSMTPD').disabled = true;
	ss = 'start';

	BX.ajax({
		'url':'/bitrix/admin/mail_smtpd_manager.php',
		'method':'POST',
		'data' : 'action=start&sessid=' + BX.bitrix_sessid(),
		'dataType': 'json',
		'timeout': 5,
		'async': false,
		'start': true,
		'onsuccess': StartSMTPDY,
		'onfailure': StartSMTPDN
	});
}

function StartSMTPDY(o)
{
	BX.closeWait();
	if(o == "success")
		Stats(true);
	else
		alert("<?echo GetMessage("MAIL_OPT_ERR")?>"+o);
}

function StartSMTPDN()
{
	BX.closeWait();
	alert('<?=GetMessage("MAIL_OPT_ERR_CON")?>');
}

var v = false, t;
function Stats(norefresh)
{
	v = true;
	BX.ajax({
		'url':'/bitrix/admin/mail_smtpd_manager.php',
		'method':'POST',
		'data' : 'action=stats&sessid=' + BX.bitrix_sessid(),
		'dataType': 'json',
		'timeout': 10,
		'async': false,
		'start': true,
		'onsuccess': (norefresh ? OnStats : OnStatsRefresh)
	});
}

function OnStatsRefresh(o)
{
	if(!v)
		return;
	v = false;

	OnStats(o);
	setTimeout("Stats()", 5000);
}

function __TimePeriodToString(t)
{
	var m = 0, h = 0, d = 0, s = t;
	if(t/60 > 1)
	{
		m = Math.floor(t/60);
		s = t%60;
		if(m/60>1)
		{
			h = Math.floor(m/60);
			m = m%60;
			if(h/24>0)
			{
				d = Math.floor(h/24);
				h = h%24;
			}
		}
	}

	return (d>0?d+"<?echo GetMessage("EMAIL_OPT_DAYS")?> ":'')+(h>0?h+"<?echo GetMessage("EMAIL_OPT_HR")?> ":'')+(m>0?m+"<?echo GetMessage("EMAIL_OPT_MIN")?> ":'')+s+"<?echo GetMessage("EMAL_OPT_SEC")?>";
}

function OnStats(o)
{
	if(o == false)
	{
		BX('status').innerHTML = "<?echo GetMessage("EMAL_OPT_SMTP_STOPPED")?>";
		BX('iStopSMTPD').style.display = 'none';
		BX('iStartSMTPD').style.display = '';
		if(ss != 'start')
			BX('iStartSMTPD').disabled = false;
	}
	else
	{
		var d = new Date(o.started * 1000);
		BX('status').innerHTML = "<?echo GetMessage("EMAL_OPT_SMTP_RUN")?>"+"<br>"+
			"<?echo GetMessage("EMAL_OPT_SMTP_STAT_START")?>"+' '+ d.toString() +" ("+"<?echo GetMessage("EMAL_OPT_SMTP_STAT_UPTIME")?>"+" "+ __TimePeriodToString(o.uptime) + ")<br>"+
			"<?echo GetMessage("EMAL_OPT_SMTP_STAT_CNT")?>"+' '+ o.messages +" "+"<?echo GetMessage("EMAL_OPT_SMTP_STAT_CNT_MAIL")?>"+"<br>"+
			"<?echo GetMessage("EMAL_OPT_SMTP_STAT_CONS")?>"+" "+ o.connections +" ("+"<?echo GetMessage("EMAL_OPT_SMTP_STAT_CONS_NOW")?>"+" " + o.connections_now+")";

		BX('iStopSMTPD').style.display = '';
		if(ss != 'stop')
			BX('iStopSMTPD').disabled = false;
		BX('iStartSMTPD').style.display = 'none';
	}
}

function StopSMTPD()
{
	ss = 'stop';
	BX('iStopSMTPD').disabled = true;
	BX.ajax({
		'url':'/bitrix/admin/mail_smtpd_manager.php',
		'method':'POST',
		'data' : 'action=stop&sessid=' + BX.bitrix_sessid(),
		'dataType': 'json',
		'timeout': 10,
		'async': true,
		'start': true
	});
}

setTimeout("Stats()", 0);
</script>
	<tr>
		<td valign="top"  width="50%"><?echo GetMessage("EMAL_OPT_PHP_LINE")?></td>
		<td valign="middle" width="50%">
			<input type="text" id="php_path" size="35" maxlength="255" value="<?=htmlspecialcharsbx($val)?>" name="php_path"></td>
	</tr>

	<tr>
		<td valign="top" width="50%"><?echo GetMessage("EMAL_OPT_STATUS")?></td>
		<td valign="middle" width="50%" id="status"><?echo GetMessage("EMAL_OPT_STATUS_UNK")?></td>
	</tr>

	<tr>
		<td valign="top"  width="50%"></td>
		<td valign="middle" width="50%">
			<input type="button" onclick="StartSMTPD()" id="iStartSMTPD" value="<?echo GetMessage("EMAL_OPT_START_SMTP")?>"> <input type="button" style="display:none" onclick="StopSMTPD()" id="iStopSMTPD" value="<?echo GetMessage("EMAL_OPT_STOP_SMTP")?>">
		</td>
	</tr>
<?$tabControl->BeginNextTab();?>

<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin/group_rights.php");?>
<?$tabControl->Buttons();?>
<script type="text/javascript">
function RestoreDefaults()
{
	if(confirm('<?echo AddSlashes(GetMessage("MAIN_HINT_RESTORE_DEFAULTS_WARNING"))?>'))
		window.location = "<?echo $APPLICATION->GetCurPage()?>?RestoreDefaults=Y&lang=<?echo LANG?>&mid=<?echo urlencode($mid)?>&<?echo bitrix_sessid_get()?>";
}
</script>
<input type="submit" name="Update" <?if ($MOD_RIGHT<"W") echo "disabled" ?> value="<?echo GetMessage("MAIL_OPTIONS_SAVE")?>">
<input type="reset" name="reset" value="<?echo GetMessage("MAIL_OPTIONS_RESET")?>">
<input type="hidden" name="Update" value="Y">
<input type="button" <?if ($MOD_RIGHT<"W") echo "disabled" ?> title="<?echo GetMessage("MAIN_HINT_RESTORE_DEFAULTS")?>" OnClick="RestoreDefaults();" value="<?echo GetMessage("MAIN_RESTORE_DEFAULTS")?>">
<?$tabControl->End();?>
<?echo bitrix_sessid_post()?>
</form>
<?endif; //if($MOD_RIGHT>="R"):?>