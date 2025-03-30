<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2013 Bitrix
 */

/**
 * Bitrix vars
 * @global CUser $USER
 * @global CMain $APPLICATION
 * @global CDatabase $DB
 */
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
define("HELP_FILE", "utilities/dump_auto.php");

if(!$USER->CanDoOperation('edit_php'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

if(!defined("START_EXEC_TIME"))
	define("START_EXEC_TIME", microtime(true));

IncludeModuleLangFile(__DIR__.'/dump.php');

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/backup.php");
$strBXError = '';
$bGzip = function_exists('gzcompress');
$encrypt = function_exists('openssl_encrypt');
$bBitrixCloud = $encrypt;
if (!CModule::IncludeModule('bitrixcloud'))
{
	$bBitrixCloud = false;
	$strBXError = GetMessage('ERR_NO_BX_CLOUD');
}
elseif (!CModule::IncludeModule('clouds'))
{
	$bBitrixCloud = false;
	$strBXError = GetMessage('ERR_NO_CLOUDS');
}

$bCron = COption::GetOptionString("main", "agents_use_crontab", "N") == 'Y' || defined('BX_CRONTAB_SUPPORT') && BX_CRONTAB_SUPPORT === true || COption::GetOptionString("main", "check_agents", "Y") != 'Y';

$APPLICATION->SetTitle(GetMessage("MAIN_DUMP_AUTO_PAGE_TITLE"));
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");

$arAllBucket = CBackup::GetBucketList();
$strError = '';

if ($DB->type != 'MYSQL')
	echo BeginNote().GetMessage('MAIN_DUMP_MYSQL_ONLY').EndNote();

$server_name = COption::GetOptionString("main", "server_name", "");
if (!$server_name)
	$server_name = $_SERVER['HTTP_HOST'];
$server_name = rtrim($server_name, '/');
if (!preg_match('/^[a-z0-9.\-]+$/i', $server_name)) // cyrillic domain hack
{
	$converter = new CBXPunycode();
	$server_name = $converter->Encode($server_name);
}
$url = (CMain::IsHTTPS() ? "https://" : "http://").$server_name;
define('LOCK_FILE', $_SERVER['DOCUMENT_ROOT'].'/bitrix/backup/auto_lock');

$dump_auto_enable = IntOption('dump_auto_enable');
$isBitrixCloudBackupInProgress = false;
if ($bBitrixCloud && $dump_auto_enable == 2)
{
	$arJobs = CBitrixCloudBackup::getInstance()->getBackupJob();
	if (is_array($arJobs))
	{
		foreach ($arJobs as $jobInfo)
		{
			if ($jobInfo['STATUS'] === 'PROCESS')
			{
				$isBitrixCloudBackupInProgress = true;
			}
		}
	}
}

if(!empty($_REQUEST['save']))
{
	if (!check_bitrix_sessid())
	{
		CAdminMessage::ShowMessage(array(
			"MESSAGE" => GetMessage("MAIN_DUMP_ERROR"),
			"DETAILS" => GetMessage("DUMP_MAIN_SESISON_ERROR"),
			"TYPE" => "ERROR",
			"HTML" => true));
	}
	elseif (
		intval($_REQUEST['dump_auto_enable'] ?? 0) === 2
		&& $isBitrixCloudBackupInProgress
	)
	{
		CAdminMessage::ShowMessage([
			'MESSAGE' => GetMessage('MAIN_DUMP_ERROR'),
			'DETAILS' => GetMessage('DUMP_BITRIXCLOUD_IS_IN_PROGRESS_CHANGE_ERROR'),
			'TYPE' => 'ERROR',
			'HTML' => true,
		]);
	}
	else
	{
		$BUCKET_ID = $_REQUEST['dump_bucket_id'] ?? 0;
		if (!$encrypt)
		{
			$_REQUEST['dump_encrypt_key'] = '';
			if ($BUCKET_ID == -1)
				$BUCKET_ID = 0;
		}
		CPasswordStorage::Set('dump_temporary_cache', $_REQUEST['dump_encrypt_key'] ?? '');

		IntOptionSet("dump_bucket_id", $BUCKET_ID);

		$dump_auto_set = intval($_REQUEST['dump_auto_enable'] ?? 0);
		if ($bBitrixCloud)
			CBitrixCloudBackup::getInstance()->deleteBackupJob();
		$dump_site_id = $_REQUEST['dump_site_id'] ?? '';
		if ($dump_auto_set)
		{
			$t = preg_match('#^([0-9]{2}):([0-9]{2})$#', $_REQUEST['dump_auto_time'] ?? '', $regs) ? $regs[1] * 60 + $regs[2] : 0;
			IntOptionSet("dump_auto_time", $t);
			$sec = $t * 60;

			$i = intval($_REQUEST['dump_auto_interval'] ?? 0);
			if (!$i)
				$i = 1;
			IntOptionSet("dump_auto_interval", $i);
			COption::SetOptionInt('main', 'last_backup_start_time', 0);
			COption::SetOptionInt('main', 'last_backup_end_time', 0);

			$start_time = time();
			$min_left = $t - date('H') * 60 - date('i');
			if ($min_left < -60)
			{
				$start_time += 86400;
				$day = 'TOMORROW';
				$w = date('w', time() + 86400);
			}
			else
			{
				$day = 'TODAY';
				$w = date('w');
			}

			// converting time to UTC
			$diff = (CTimeZone::Enabled() ? CTimeZone::GetOffset() : 0) + date('Z');
			$sec -= $diff;
			if ($sec < 0)
			{
				$sec += 86400;
				$w = ($w-1)%7;
			}
			elseif ($sec >= 86400)
			{
				$sec -= 86400;
				$w = ($w+1)%7;
			}

			switch ($dump_auto_interval)
			{
				case 1:
					$arWeekDays = array(0,1,2,3,4,5,6);
					break;
				case 2:
					if ($w%2)
						$arWeekDays = array(1,3,5,0);
					else
						$arWeekDays = array(0,2,4,6);
					break;
				case 3:
					$arWeekDays = array($w,($w+3)%7);
					sort($arWeekDays);
					break;
				default: // 7
					$arWeekDays = array($w);
			}

			$strMessage = GetMessage("MAIN_DUMP_SHED_CLOSEST_TIME_".$day).FormatDate('FULL', strtotime(date('Y-m-d '.sprintf('%02d:%02d',floor($t/60), $t%60),$start_time))).' '.GetMessage('DUMP_LOCAL_TIME');

			if ($dump_auto_set == 2)
			{
				$backup_secret_key = randString(16);
				CPasswordStorage::Set('backup_secret_key', $backup_secret_key);
				$strError = CBitrixCloudBackup::getInstance()->addBackupJob(
					$backup_secret_key,
					$url,
					$sec,
					$arWeekDays
				);
				$strMessage .= '<br>'.GetMessage('DUMP_CHECK_BITRIXCLOUD', array('#LINK#' => '/bitrix/admin/bitrixcloud_backup_job.php?lang='.LANGUAGE_ID));
			}
		}
		elseif (!empty($_REQUEST['dump_auto_green_button']))
			$strError = GetMessage('DUMP_WARN_NO_BITRIXCLOUD');
		else
			$strMessage = GetMessage('DUMP_SAVED_DISABLED');

		if (!$strError)
		{
			IntOptionSet("dump_auto_enable", $dump_auto_set);
			$dump_auto_enable = $dump_auto_set;
		}

		COption::SetOptionString("main", "dump_site_id"."_auto", serialize($dump_site_id));
		IntOptionSet("dump_delete_old", $_REQUEST['dump_delete_old'] ?? 0);
		IntOptionSet("dump_old_time", $_REQUEST['dump_old_time'] ?? 0);
		IntOptionSet("dump_old_cnt", $_REQUEST['dump_old_cnt'] ?? 0);
		IntOptionSet("dump_old_size", $_REQUEST['dump_old_size'] ?? 0);

		IntOptionSet("dump_integrity_check", isset($_REQUEST['dump_integrity_check']) && $_REQUEST['dump_integrity_check'] == 'Y');
		IntOptionSet("dump_use_compression", $bGzip && (!isset($_REQUEST['dump_disable_gzip']) || $_REQUEST['dump_disable_gzip'] != 'Y'));

		IntOptionSet("dump_max_exec_time", $_REQUEST['dump_max_exec_time'] ?? 0);
		IntOptionSet("dump_max_exec_time_sleep", $_REQUEST['dump_max_exec_time_sleep'] ?? 0);

		$dump_archive_size_limit = isset($_REQUEST['dump_archive_size_limit']) ? intval($_REQUEST['dump_archive_size_limit'] * 1024 * 1024) : 0;
		if ($dump_archive_size_limit <= 10240 * 1024)
			$dump_archive_size_limit = 100 * 1024 * 1024;
		IntOptionSet("dump_archive_size_limit", $dump_archive_size_limit);

/*		$bDumpCloud = false;
		foreach($arAllBucket as $arBucket)
		{
			if ($res = $_REQUEST['dump_cloud'][$arBucket['ID']] == 'Y')
				$bDumpCloud = true;
			IntOptionSet('dump_cloud_'.$arBucket['ID'], $res);
		}
		IntOptionSet('dump_do_clouds', $bDumpCloud);
		*/
		IntOptionSet('dump_do_clouds', isset($_REQUEST['dump_do_clouds']) && $_REQUEST['dump_do_clouds'] == 'Y');

		IntOptionSet('dump_base', isset($_REQUEST['dump_base']) && $_REQUEST['dump_base'] == 'Y');
		IntOptionSet('dump_base_skip_stat', isset($_REQUEST['dump_base_skip_stat']) && $_REQUEST['dump_base_skip_stat'] == 'Y');
		IntOptionSet('dump_base_skip_search', isset($_REQUEST['dump_base_skip_search']) && $_REQUEST['dump_base_skip_search'] == 'Y');
		IntOptionSet('dump_base_skip_log', isset($_REQUEST['dump_base_skip_log']) && $_REQUEST['dump_base_skip_log'] == 'Y');

		IntOptionSet('dump_file_kernel', isset($_REQUEST['dump_file_kernel']) && $_REQUEST['dump_file_kernel'] == 'Y');
		IntOptionSet('dump_file_public', isset($_REQUEST['dump_file_public']) && $_REQUEST['dump_file_public'] == 'Y');

		IntOptionSet('skip_mask', isset($_REQUEST['skip_mask']) && $_REQUEST['skip_mask'] == 'Y');
		$arMask = array_unique($_REQUEST['arMask'] ?? []);
		foreach($arMask as $mask)
		{
			if (trim($mask))
			{
				$mask = rtrim(str_replace('\\','/',trim($mask)),'/');
				$skip_mask_array[] = $mask;
			}
		}
		COption::SetOptionString("main", "skip_mask_array_auto", serialize($skip_mask_array));

		IntOptionSet('dump_max_file_size', intval($_REQUEST['max_file_size'] ?? 0));

		if ($strError)
			CAdminMessage::ShowMessage(array(
				"MESSAGE" => GetMessage("MAIN_DUMP_ERROR"),
				"DETAILS" => $strError,
				"TYPE" => "ERROR",
				"HTML" => true));
		else
			CAdminMessage::ShowMessage(array(
				"MESSAGE" => GetMessage("MAIN_DUMP_SUCCESS_SAVED"),
				"DETAILS" => $strMessage,
				"TYPE" => "OK",
				"HTML" => true));
	}
}
elseif (file_exists(LOCK_FILE))
{
	if ($t = intval(file_get_contents(LOCK_FILE)))
		CAdminMessage::ShowMessage(array(
			"MESSAGE" => GetMessage("MAIN_DUMP_AUTO_LOCK"),
			"DETAILS" => GetMessage("MAIN_DUMP_AUTO_LOCK_TIME", array('#TIME#' => HumanTime(time() - $t))),
			"TYPE" => "OK",
			"HTML" => true));
	else
		CAdminMessage::ShowMessage(array(
			"MESSAGE" => GetMessage("MAIN_DUMP_ERROR"),
			"DETAILS" => GetMessage("MAIN_DUMP_ERR_OPEN_FILE").' '.LOCK_FILE,
			"TYPE" => "ERROR",
			"HTML" => true));

}
else
{
	if ($dump_auto_enable)
		CAdminMessage::ShowMessage(array(
			"MESSAGE" => GetMessage('DUMP_AUTO_INFO_ON'),
			"TYPE" => "OK",
			"HTML" => true));
	else
		CAdminMessage::ShowMessage(array(
			"MESSAGE" => GetMessage('DUMP_AUTO_INFO_OFF'),
			"TYPE" => "ERROR",
			"HTML" => true));
}

if (!$encrypt)
{
	CAdminMessage::ShowMessage(array(
		"MESSAGE" => GetMessage("MAIN_DUMP_NOT_INSTALLED1"),
		"DETAILS" => GetMessage("MAIN_DUMP_NO_ENC_FUNCTIONS"),
		"TYPE" => "ERROR",
		"HTML" => true));
}

if ($isBitrixCloudBackupInProgress && empty($_REQUEST['save']))
{
	CAdminMessage::ShowMessage([
		'DETAILS' => GetMessage('DUMP_BITRIXCLOUD_IS_IN_PROGRESS_CHANGE_WARNING'),
		'TYPE' => 'OK',
		'HTML' => true,
	]);
}

$aMenu = array(
	array(
		"TEXT"	=> GetMessage("MAIN_DUMP_LIST_PAGE_TITLE"),
		"LINK"	=> "/bitrix/admin/dump_list.php?lang=".LANGUAGE_ID,
		"TITLE"	=> GetMessage("MAIN_DUMP_LIST_PAGE_TITLE"),
		"ICON"	=> "btn_list"
	)
);
$context = new CAdminContextMenu($aMenu);
$context->Show();
CAdminFileDialog::ShowScript(
	Array
	(
		"event" => "__bx_select_dir",
		"arResultDest" => Array("FUNCTION_NAME" => "mnu_SelectValue"),
		"arPath" => Array('PATH'=>"/"),
		"select" => 'D',
		"operation" => 'O',
		"showUploadTab" => false,
		"showAddToMenuTab" => false,
		"allowAllFiles" => true,
		"SaveConfig" => true
	)
);
?>
<script>
var numRows=0;
function AddTableRow()
{
	var oTable = BX('skip_mask_table');
	numRows = oTable.rows.length;
	var oRow = oTable.insertRow(-1);
	var oCell = oRow.insertCell(0);
	oCell.innerHTML = '<input type="text" name="arMask[]" id="mnu_FILES_' + numRows  +'" size=30><input type="button" id="mnu_FILES_btn_' + numRows  + '" value="..." onclick="showMenu(this, '+ numRows  +')">';
}

var currentID;

function showMenu(div, id)
{
	currentID = id;
	__bx_select_dir();
}

function mnu_SelectValue(filename, path, site, title, menu)
{
	BX('mnu_FILES_' + currentID).value = path + (path == '/' ? '' : '/') + filename;
}

function BigGreyButton()
{
	// Location
	BX("localfolder").checked = true;

	// Starter
	BX("dump_auto_disable").checked = true;

	if (ob = document.fd1.dump_encrypt)
		ob.checked = false;

	CheckEncrypt();
	CheckEnabled();
	SaveSettings();
}

function BigGreenButton()
{
	// Location
	BX("<?=$bBitrixCloud ? 'bitrixcloud' : 'localfolder'?>").checked = true;

	// Starter
	BX("<?=$bBitrixCloud ? 'dump_auto_bitrix' : ($bCron ? 'dump_auto_cron' : 'dump_auto_disable')?>").checked = true;

	if (ob = document.fd1.dump_encrypt)
		ob.checked = false;

	BX("<?=$bBitrixCloud ? 'dump_delete_old_1' : 'dump_delete_old_4' ?>").checked = true;
	document.fd1.dump_old_cnt.value = 3;
	document.fd1.dump_integrity_check.checked = true;
	document.fd1.dump_disable_gzip.checked = false;
	document.fd1.dump_archive_size_limit.value = 100;

	CheckEncrypt();
	if (CheckEnabled())
	{
		document.fd1.dump_auto_time.value = '<?=sprintf('%02d:%d0', rand(0,5), rand(0,3))?>';
		document.fd1.dump_auto_interval.value = 7;
	}

	var i = 0;
	while(ob = BX('dump_site_id' + i))
	{
		ob.checked = true;
		i++;
	}
	document.fd1.dump_auto_green_button.value = 1;
	SaveSettings();
}

function CheckEncrypt()
{
	var enc;
	if(enc = document.fd1.dump_encrypt)
	{
		if (ob = BX('bitrixcloud'))
		enc.disabled = ob.checked;
	}
}

function CheckEnabled()
{
	var on = !BX('dump_auto_disable').checked;

	document.fd1.dump_auto_time.disabled = !on;
	document.fd1.dump_auto_interval.disabled = !on;
	<?php if ($isBitrixCloudBackupInProgress): ?>
		BX('save_button').disabled = BX('dump_auto_bitrix').checked;
	<?php endif; ?>
	return on;
}
BX.ready(CheckEnabled);

function SavePassword()
{
	var key = BX('dump_encrypt_key').value;
	var l = key.length;

	var strError = '';
	if (!l)
		strError = '<?=GetMessageJS("MAIN_DUMP_EMPTY_PASS")?>';
	else if (!/^[\040-\176]*$/.test(key))
		strError = '<?=GetMessageJS('DUMP_ERR_NON_ASCII')?>';
	else if (l < 6)
		strError = '<?=GetMessageJS("MAIN_DUMP_ENC_PASS_DESC")?>';
	else if (key != BX('dump_encrypt_key_confirm').value)
		strError = '<?=GetMessageJS("DUMP_MAIN_ERR_PASS_CONFIRM")?>';

	if (strError)
	{
		BX('password_error').innerHTML = strError;
		BX('dump_encrypt_key').focus();
	}
	else
	{
		BX('password_error').innerHTML = '';
		BX.WindowManager.Get().Close();
		document.fd1.dump_encrypt_key.value = BX('dump_encrypt_key').value;
		document.fd1.submit();
	}
}

var PasswordDialog;
function SaveSettings()
{
	var ob;
	if (BX('bitrixcloud').checked || (ob = document.fd1.dump_encrypt) && ob.checked)
	{
		if (!PasswordDialog)
		{
			PasswordDialog = new BX.CDialog({
				title: '<?=GetMessage("DUMP_MAIN_ENC_ARC")?>',
				content: '<?
					echo '<div style="color:red" id=password_error></div>';
					echo CUtil::JSEscape(BeginNote().GetMessage('MAIN_DUMP_SAVE_PASS_AUTO').EndNote());
					echo '<table>';
					echo '<tr><td>'.GetMessage('MAIN_DUMP_ENC_PASS').'</td><td><input type="password" value="" id="dump_encrypt_key" onkeyup="if(event.keyCode==13) {BX(&quot;dump_encrypt_key_confirm&quot;).focus()}"/></td></tr>';
					echo '<tr><td>'.GetMessage('DUMP_MAIN_PASSWORD_CONFIRM').'</td><td><input type="password" value="" id="dump_encrypt_key_confirm"  onkeyup="if(event.keyCode==13) {SavePassword()}"/></td></tr>';
					echo '</table>';
				?>',
				height: 300,
				width: 600,
				resizable: false,
				buttons: [ {
					title: '<?=GetMessage("DUMP_MAIN_SAVE")?>',
	//				id: 'my_save',
	//				name: 'my_save',
					className: 'adm-btn-save',
					action: SavePassword

				}, BX.CAdminDialog.btnCancel ]
			})
		}
		PasswordDialog.Show();
		BX('dump_encrypt_key').focus();
	}
	else
	{
		document.fd1.dump_encrypt_key.value = "";
		document.fd1.submit();
	}
}
</script>

<form name="fd1" action="<?echo $APPLICATION->GetCurPage()?>?lang=<?=LANGUAGE_ID?>" method="POST">
<?=bitrix_sessid_post()?>
<input type=hidden name=save value=Y>
<input type=hidden name=dump_encrypt_key>
<input type=hidden name=dump_auto_green_button>
<?
$aTabs = array();
$aTabs[] = array("DIV"=>"main", "TAB"=>GetMessage('DUMP_AUTO_TAB'), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("MAIN_DUMP_AUTO_PAGE_TITLE"));
$aTabs[] = array("DIV"=>"expert", "TAB"=>GetMessage("DUMP_MAIN_PARAMETERS"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("DUMP_MAIN_AUTO_PARAMETERS"));

$editTab = new CAdminTabControl("editTab", $aTabs, true, true);
$editTab->Begin();
$editTab->BeginNextTab();
?>
<tr>
	<td colspan=2>
	<?
		if ($dump_auto_enable)
			echo '<input type="button" value="'.GetMessage('DUMP_BTN_AUTO_DISABLE').'" onclick="BigGreyButton()">';
		else
			echo '<input type="button" value="'.GetMessage('DUMP_BTN_AUTO_ENABLE').'" onclick="BigGreenButton()" class="adm-btn-save">';
	?>
	</td>
</tr>
<tr>
	<td colspan=2>
<? echo BeginNote();
	echo nl2br(GetMessage('DUMP_AUTO_INFO_TEXT'));
echo EndNote();

?>
	</td>
</tr>
<?
$editTab->BeginNextTab();

$BUCKET_ID = IntOption('dump_bucket_id', -1);
if ($BUCKET_ID == -1 && !$bBitrixCloud)
	$BUCKET_ID = 0;
?>
<tr>
	<td class="adm-detail-valign-top" width=40%><?=GetMessage('MAIN_DUMP_ARC_LOCATION')?></td>
	<td>
		<div><label><input type=radio name=dump_bucket_id value="-1" <?=$BUCKET_ID == -1 ? "checked" : ""?> id="bitrixcloud" <?=!$bBitrixCloud ? 'disabled' : ''?> onclick="CheckEncrypt()"> <?=GetMessage('DUMP_MAIN_IN_THE_BXCLOUD')?></label><?=$strBXError ? ' <span style="color:red">('.$strBXError.')</span>' : ''?></div>
		<div><label><input type=radio name=dump_bucket_id value="0"  <?=$BUCKET_ID == 0  ? "checked" : ""?> id="localfolder" onclick="CheckEncrypt()"> <?=GetMessage('MAIN_DUMP_LOCAL_DISK')?></label></div>
		<?
		$arWriteBucket = CBackup::GetBucketList($arFilter = array('READ_ONLY' => 'N'));
		if ($arWriteBucket)
		{
			foreach($arWriteBucket as $f)
				echo '<div><label><input type=radio name=dump_bucket_id value="'.$f['ID'].'" '.($BUCKET_ID == $f['ID'] ? "checked" : "").' onclick="CheckEncrypt()"> '.GetMessage('DUMP_MAIN_IN_THE_CLOUD').' '.htmlspecialcharsbx($f['BUCKET'].' ('.$f['SERVICE_ID'].')').'</label></div>';
		}
		?>
	</td>
</tr>
<tr class="heading">
	<td colspan="2"><?=GetMessage("MAIN_DUMP_SHED")?></td>
</tr>
<tr>
	<td class="adm-detail-valign-top" width=40%><?=GetMessage('AUTO_EXEC_METHOD')?></td>
	<td>
		<?
		?>
		<div><label><input type="radio" name="dump_auto_enable" id="dump_auto_bitrix" value="2" <?= $dump_auto_enable == 2 ? 'checked' : '' ?> <?=$bBitrixCloud ? '' : 'disabled'?> onclick="CheckEnabled()"> <?=GetMessage('AUTO_EXEC_FROM_BITRIX')?> (<a href="/bitrix/admin/settings.php?lang=<?=LANGUAGE_ID?>&mid=main" target=_blank><?=GetMessage('AUTO_URL')?></a>: <?=htmlspecialcharsbx($url)?>)</div>
		<div><label><input type="radio" name="dump_auto_enable" id="dump_auto_cron" value="1" <?= $dump_auto_enable == 1 ? 'checked' : '' ?> <?=$bCron ? '' : 'disabled'?> onclick="CheckEnabled()"> <?=GetMessage('AUTO_EXEC_FROM_CRON')?><span class="required"><sup>1</sup></span></label></div>
		<div><label><input type="radio" name="dump_auto_enable" id="dump_auto_disable" value="0" <?= $dump_auto_enable == 0 ? 'checked' : '' ?> onclick="CheckEnabled()"> <?=GetMessage('AUTO_EXEC_FROM_MAN', array('#SCRIPT#' => '<b>/bitrix/modules/main/tools/backup.php</b>'))?></div>
	</td>
</tr>
<tr>
	<td><?=GetMessage("TIME_SPENT")?></td>
	<td><?
		require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/tools/clock.php");
		$min = IntOption('dump_auto_time');
		CClock::Show(array(
			'view' => 'select',
			'inputName' => 'dump_auto_time',
			'initTime' => sprintf('%02d:%02d',floor($min / 60),($min % 60))
			)
		);
		?>
	</td>
</tr>
<tr>
	<td><?=GetMessage("MAIN_DUMP_PERIODITY")?></td>
	<td>
		<select name=dump_auto_interval>
		<?
			foreach(array(
				1 => GetMessage("MAIN_DUMP_PER_1"),
				2 => GetMessage("MAIN_DUMP_PER_2"),
				3 => GetMessage("MAIN_DUMP_PER_3"),
//				5 => GetMessage("MAIN_DUMP_PER_5"),
				7 => GetMessage("MAIN_DUMP_PER_7"),
//				14 => GetMessage("MAIN_DUMP_PER_14"),
//				21 => GetMessage("MAIN_DUMP_PER_21"),
//				30 => GetMessage("MAIN_DUMP_PER_30"),
				) as $k => $v)
					echo '<option value="'.$k.'" '.(IntOption('dump_auto_interval') == $k ? 'selected' : '').'>'.$v.'</option>';
		?>
		</select>
	</td>
</tr>
<tr class="heading">
	<td colspan="2"><?=GetMessage("MAIN_DUMP_DELETE_OLD")?></td>
</tr>
<tr>
	<td class="adm-detail-valign-top" width=40%><?=GetMessage('DUMP_DELETE')?>:</td>
	<td>
		<?
			$dump_delete_old = IntOption('dump_delete_old', 1);
		?>
		<div><label><input type=radio name=dump_delete_old value=0 <?=$dump_delete_old == 0 ? "checked" : ""?>> <?=GetMessage('DUMP_NOT_DELETE')?></label></div>
		<div><label><input type=radio name=dump_delete_old value=1 <?=$dump_delete_old == 1 ? "checked" : ""?> id="dump_delete_old_1"> <?=GetMessage('DUMP_CLOUD_DELETE')?></label></div>
		<div><label><input type=radio name=dump_delete_old value=2 <?=$dump_delete_old == 2 ? "checked" : ""?>> <?=GetMessage('DUMP_RM_BY_TIME', array('#TIME#' => '<input type=text name=dump_old_time size=2 value="'.IntOption('dump_old_time').'">'))?></label></div>
		<div><label><input type=radio name=dump_delete_old value=4 <?=$dump_delete_old == 4 ? "checked" : ""?> id="dump_delete_old_4"> <?=GetMessage('DUMP_RM_BY_CNT',  array('#CNT#'  => '<input type=text name=dump_old_cnt size=2 value="'.IntOption('dump_old_cnt').'">'))?></label></div>
		<div><label><input type=radio name=dump_delete_old value=8 <?=$dump_delete_old == 8 ? "checked" : ""?>> <?=GetMessage('DUMP_RM_BY_SIZE', array('#SIZE#' => '<input type=text name=dump_old_size size=1 value="'.IntOption('dump_old_size').'">'))?></label></div>
	</td>
</tr>



<tr class="heading">
	<td colspan="2"><?=GetMessage("DUMP_MAIN_ARC_CONTENTS")?></td>
</tr>
<?
	$arSitePath = array();
	$res = CSite::GetList('sort', 'asc', array('ACTIVE'=>'Y'));
	while($f = $res->Fetch())
	{
		$root = rtrim($f['ABS_DOC_ROOT'],'/');
		if (is_dir($root))
			$arSitePath[$root] = array($f['ID'] => '['.$f['ID'].'] '.$f['NAME']);
	}

	if (count($arSitePath) > 1)
	{
	?>
	<tr>
		<td class="adm-detail-valign-top"><?=GetMessage("DUMP_MAIN_SITE")?></td>
		<td>
			<?
				$dump_site_id = array();
				if ($s = COption::GetOptionString("main", "dump_site_id"."_auto", ($NS['dump_site_id'])))
				{
					$sites = unserialize($s, ['allowed_classes' => false]);
					if (is_array($sites))
					{
						$dump_site_id = $sites;
					}
				}
				$i = 0;
				foreach($arSitePath as $path => $val)
				{
					$path = rtrim(str_replace('\\','/',$path),'/');
					$k = key($val);
					$v = current($val);
					echo '<div><input type=checkbox id="dump_site_id'.$i.'" name="dump_site_id[]" value="'.htmlspecialcharsbx($k).'" '.(in_array($k, $dump_site_id) ? ' checked' : '').'> <label for="dump_site_id'.$i.'">'.htmlspecialcharsbx($v).'</label></div>';
					$i++;
				}
			?>
		</td>
	</tr>
	<?
	}
	?>
<?
if ($arAllBucket)
{
?>
<tr>
	<td class="adm-detail-valign-top"><?=GetMessage("DUMP_MAIN_DOWNLOAD_CLOUDS")?></td>
	<td>
		<input type="checkbox" name="dump_do_clouds" value=Y <?=(IntOption("dump_do_clouds", 1) ? "checked" : "")?>>
		<?
//		foreach($arAllBucket as $arBucket)
//			echo '<div><input type="checkbox" id="dump_cloud_'.$arBucket['ID'].'" name="dump_cloud['.$arBucket['ID'].']" value=Y '.(IntOption("dump_cloud_".$arBucket['ID'], 1) ? "checked" : "").'> <label for="dump_cloud_'.$arBucket['ID'].'">'.htmlspecialcharsbx($arBucket['BUCKET'].' ('.$arBucket['SERVICE_ID'].')').'</label></div>';
		?>
	</td>
</tr>
<?
}
?>
<?
if ($DB->type == 'MYSQL')
{
?>
<tr>
	<td><?=GetMessage("DUMP_MAIN_ARC_DATABASE")?>:</td>
	<td><input type="checkbox" name="dump_base" value=Y <?=IntOption("dump_base", 1) ? "checked" : "" ?>></td>
</tr>
<tr>
	<td class="adm-detail-valign-top"><?=GetMessage("DUMP_MAIN_DB_EXCLUDE")?></td>
	<td>
		<div><input type="checkbox" name="dump_base_skip_stat" value=Y <?=IntOption("dump_base_skip_stat", 0) ? "checked" : "" ?> id="dump_base_skip_stat"> <label for="dump_base_skip_stat"><? echo GetMessage("MAIN_DUMP_BASE_STAT") ?></label></div>
		<div><input type="checkbox" name="dump_base_skip_search" value="Y" <?=IntOption("dump_base_skip_search", 0) ? "checked" : "" ?> id="dump_base_skip_search"> <label for="dump_base_skip_search"><? echo GetMessage("MAIN_DUMP_BASE_SINDEX") ?></label></div>
		<div><input type="checkbox" name="dump_base_skip_log" value="Y"<?=IntOption("dump_base_skip_log", 0) ? "checked" : "" ?> id="dump_base_skip_log"> <label for="dump_base_skip_log"><? echo GetMessage("MAIN_DUMP_EVENT_LOG") ?></label></div>
	</td>
</tr>
<?
}
?>
<tr>
	<td><?echo GetMessage("MAIN_DUMP_FILE_KERNEL")?></td>
	<td><input type="checkbox" name="dump_file_kernel" value="Y" <?=IntOption("dump_file_kernel", 1) ? "checked" : ''?>></td>
</tr>
<tr>
	<td><?echo GetMessage("MAIN_DUMP_FILE_PUBLIC")?></td>
	<td><input type="checkbox" name="dump_file_public" value="Y" <?=IntOption("dump_file_public", 1) ? "checked" : ''?>></td>
</tr>
<tr>
	<td class="adm-detail-valign-top"><?echo GetMessage("MAIN_DUMP_MASK")?></span></td>
	<td>
		<input type="checkbox" name="skip_mask" value="Y" <?=IntOption('skip_mask', 0)?" checked":'';?>>
		<table id="skip_mask_table" cellspacing=0 cellpadding=0>
		<?
		$i=-1;

		$res = unserialize(COption::GetOptionString("main","skip_mask_array_auto"), ['allowed_classes' => false]);
		$skip_mask_array = is_array($res)?$res:array();

		foreach($skip_mask_array as $mask)
		{
			$i++;
			echo
			'<tr><td>
				<input type="text" name="arMask[]" id="mnu_FILES_'.$i.'" value="'.htmlspecialcharsbx($mask).'" size=30>'.
				'<input type="button" id="mnu_FILES_btn_'.$i.'" value="..." onclick="showMenu(this, \''.$i.'\')">'.
			'</tr>';
		}
		$i++;
		?>
			<tr><td><input type="text" name="arMask[]" id="mnu_FILES_<?=$i?>" size=30><input type="button" id="mnu_FILES_btn_<?=$i?>" value="..." onclick="showMenu(this, '<?=$i?>')"></tr>
		</table>
		<input type=button id="more_button" value="<?=GetMessage('MAIN_DUMP_MORE')?>" onclick="AddTableRow()">
	</td>
</tr>
<tr>
	<td><?echo GetMessage("MAIN_DUMP_FILE_MAX_SIZE")?></td>
	<td><input type="text" name="max_file_size" size="10" value="<?=IntOption("dump_max_file_size", 0)?>">
	<?echo GetMessage("MAIN_DUMP_FILE_MAX_SIZE_kb")?></td>
</tr>

<tr class="heading">
	<td colspan="2"><?=GetMessage("DUMP_MAIN_ARC_MODE")?></td>
</tr>

<tr>
	<td><?=GetMessage("MAIN_DUMP_ENABLE_ENC")?><span class="required"><sup>2</sup></td>
	<td><input type="checkbox" name="dump_encrypt" value="Y" <?=($BUCKET_ID == -1 || CPasswordStorage::Get('dump_temporary_cache') ? "checked" : "")?> <?=!$encrypt || $BUCKET_ID == -1  ? 'disabled' : ''?>></td>
</tr>
<tr>
	<td width=40%><?=GetMessage('INTEGRITY_CHECK_OPTION')?></td>
	<td><input type="checkbox" name="dump_integrity_check" value="Y" <?=IntOption('dump_integrity_check') ? 'checked' : '' ?>>
</tr>
<tr>
	<td><?=GetMessage('DISABLE_GZIP')?></td>
	<td><input type="checkbox" name="dump_disable_gzip" value="Y" <?=IntOption('dump_use_compression') && $bGzip ? '' : 'checked' ?> <?=!$bGzip ? 'disabled' : ''?>>
</tr>
<tr>
	<td width=40%><?=GetMessage('STEP_LIMIT')?></td>
	<td>
		<input type="text" name="dump_max_exec_time" value="<?=IntOption("dump_max_exec_time", 20)?>" size=2>
		<?echo GetMessage("MAIN_DUMP_FILE_STEP_sec");?>,
		<?echo GetMessage("MAIN_DUMP_FILE_STEP_SLEEP")?>
		<input type="text" name="dump_max_exec_time_sleep" value="<?=IntOption("dump_max_exec_time_sleep", 3)?>" size=2>
		<?echo GetMessage("MAIN_DUMP_FILE_STEP_sec");?>
	</td>
</tr>
<tr>
	<td><?=GetMessage("MAIN_DUMP_MAX_ARCHIVE_SIZE")?></td>
	<td><input type="text" name="dump_archive_size_limit" value="<?=IntOption('dump_archive_size_limit', 100 * 1024 * 1024) / 1024 / 1024?>" size=4> <?=GetMessage("MAIN_DUMP_MAX_ARCHIVE_SIZE_VALUES")?><span class="required"><sup>3</sup></span></td>
</tr>
<?
$editTab->Buttons();
?>
<input type="button" class="adm-btn-save" value="<?=GetMessage("DUMP_MAIN_SAVE")?>" id="save_button" onclick="SaveSettings()">
<?
$editTab->End();
?>
</form>

<?
echo BeginNote();
echo '<div><span class=required><sup>1</sup></span> '.GetMessage("MAIN_DUMP_SHED_TIME_SET").'.</div>';
echo '<div><span class=required><sup>2</sup></span> '.GetMessage("MAIN_DUMP_BXCLOUD_ENC").'</div>';
echo '<div><span class=required><sup>3</sup></span> '.GetMessage("MAIN_DUMP_MAX_ARCHIVE_SIZE_INFO").'</div>';
echo EndNote();

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");

#################################################
################## FUNCTIONS
function IntOption($name, $def = 0)
{
	static $CACHE;
	$name .= '_auto';

	if (!isset($CACHE[$name]))
		$CACHE[$name] = COption::GetOptionInt("main", $name, $def);
	return $CACHE[$name];
}

function IntOptionSet($name, $val)
{
	$val = intval($val);
	COption::SetOptionInt('main', $name.'_auto', $val);
}
