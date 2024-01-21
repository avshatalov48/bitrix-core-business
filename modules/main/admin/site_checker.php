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
 * @global CMain $APPLICATION
 * @global CUser $USER
 */

@ini_set("track_errors", "1");
@ini_set('display_errors', 1);
error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
$message = null;

define('DEBUG_FLAG', str_replace('\\','/',$_SERVER['DOCUMENT_ROOT'] . '/bitrix/site_checker_debug'));
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/classes/general/site_checker.php');

// NO AUTH TESTS
if (isset($_REQUEST['unique_id']) && $_REQUEST['unique_id'])
{
	if (!file_exists(DEBUG_FLAG) && $_REQUEST['unique_id'] != checker_get_unique_id())
		die('Permission denied: UNIQUE ID ERROR');

	$testType = $_GET['test_type'] ?? '';
	switch ($testType)
	{
		case 'socket_test':
			echo "SUCCESS";
		break;
		case 'webdav_test':
			if ($_SERVER['REQUEST_METHOD'] == $_GET['method'])
				echo "SUCCESS";
			else
				echo 'Incorrect $_SERVER[REQUEST_METHOD]: '.$_SERVER['REQUEST_METHOD'].', expected: '.preg_replace('#[^A-Z]#', '', $_GET['method']);
		break;
		case 'compression':
			echo str_repeat('SUCCESS', 8*1024);
		break;
		case 'perf':
			define("NOT_CHECK_PERMISSIONS", true);
			define("LDAP_NO_PORT_REDIRECTION", true);
			require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

			foreach(GetModuleEvents("main", "OnEpilog", true) as $arEvent)
				ExecuteModuleEventEx($arEvent);

			$APPLICATION->EndBufferContentMan();

			echo round(microtime(true) - START_EXEC_TIME, 4);
		break;
		case 'fast_download':
			header('X-Accel-Redirect: /bitrix/tmp/success.txt');
		break;
		case 'dbconn_test':
			ob_start();
			define('NOT_CHECK_PERMISSIONS', true);
			require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');
			$buff = '';
			while(ob_get_level())
			{
				$buff .= ob_get_contents();
				ob_end_clean();
			}
			ob_end_clean();
			if (function_exists('mb_internal_encoding'))
				mb_internal_encoding('ISO-8859-1');
			echo $buff === '' ? 'SUCCESS' : 'Length: '.mb_strlen($buff).' ('.$buff . ')';
		break;
		case 'pcre_recursion_test':
			$a = str_repeat('a',4096);
			if (preg_match('/(a)+/',$a)) // Segmentation fault (core dumped)
				echo 'SUCCESS';
			else
				echo 'CLEAN';
		break;
		case 'method_exists':
			$arRes= Array
			(
				"CLASS" => "",
				"CALC_METHOD" => ""
			);
			method_exists($arRes['CLASS'], $arRes['CALC_METHOD']);
			echo 'SUCCESS';
		break;
		case 'upload_test':
			if (function_exists('mb_internal_encoding'))
				mb_internal_encoding('ISO-8859-1');

			$dir = $_SERVER['DOCUMENT_ROOT'].'/bitrix/tmp';
			if (!file_exists($dir))
				mkdir($dir);

			$binaryData = '';
			for($i=40;$i<240;$i++)
				$binaryData .= chr($i);
			if (isset($_REQUEST['big']) && $_REQUEST['big'])
				$binaryData = str_repeat($binaryData, 21000);

			if (isset($_REQUEST['raw']) && $_REQUEST['raw'])
				$binaryData_received = file_get_contents('php://input');
			elseif (move_uploaded_file($tmp_name = $_FILES['test_file']['tmp_name'], $image = $dir.'/site_checker.bin'))
			{
				$binaryData_received = file_get_contents($image);
				unlink($image);
			}
			else
			{
				echo 'move_uploaded_file('.$tmp_name.','.$image.')=false'."\n";
				echo '$_FILES='."\n";
				print_r($_FILES);
				die();
			}

			if ($binaryData === $binaryData_received)
				echo "SUCCESS";
			else
				echo 'strlen($binaryData)='.mb_strlen($binaryData).', strlen($binaryData_received)='.mb_strlen($binaryData_received);
		break;
		case 'post_test':
			$ok = true;
			for ($i=0;$i<201;$i++)
				$ok = $ok && ($_POST['i'.$i] == md5($i));

			echo $ok ? 'SUCCESS' : 'FAIL';
			break;
		case 'memory_test':
			@ini_set("memory_limit", "512M");
			$max = intval($_GET['max']);
			if ($max)
			{
				for($i=1;$i<=$max;$i++)
					$a[] = str_repeat(chr($i),1024*1024); // 1 Mb

				echo "SUCCESS";
			}
		break;
		case 'auth_test':
			$remote_user = ($_SERVER["REMOTE_USER"] ?? '') ?: ($_SERVER["REDIRECT_REMOTE_USER"] ?? '');
			$strTmp = base64_decode(mb_substr($remote_user, 6));
			if ($strTmp)
				list($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) = explode(':', $strTmp);
			if ($_SERVER['PHP_AUTH_USER']=='test_user' && $_SERVER['PHP_AUTH_PW']=='test_password')
				echo('SUCCESS');
		break;
		case 'session_test':
			session_start();
			echo $_SESSION['CHECKER_CHECK_SESSION'] ?? '';
			$_SESSION['CHECKER_CHECK_SESSION'] = 'SUCCESS';
		break;
		case 'redirect_test':
			foreach(array('SERVER_PORT','HTTPS','FCGI_ROLE','SERVER_PROTOCOL','SERVER_PORT','HTTP_HOST') as $key)
				$GLOBALS['_SERVER'][$key] = $GLOBALS['_REQUEST'][$key];
			function IsHTTPS()
			{
				return ($_SERVER["SERVER_PORT"]==443 || mb_strtolower($_SERVER["HTTPS"]) == "on");
			}

			function SetStatus($status)
			{
				$bCgi = (mb_stristr(php_sapi_name(), "cgi") !== false);
				$bFastCgi = ($bCgi && (array_key_exists('FCGI_ROLE', $_SERVER) || array_key_exists('FCGI_ROLE', $_ENV)));
				if($bCgi && !$bFastCgi)
					header("Status: ".$status);
				else
					header($_SERVER["SERVER_PROTOCOL"]." ".$status);
			}

			if (isset($_REQUEST['done']))
				echo 'SUCCESS';
			else
			{
				SetStatus("302 Found");
				$protocol = (IsHTTPS() ? "https" : "http");
				$host = $_SERVER['HTTP_HOST'];
				if($_SERVER['SERVER_PORT'] <> 80 && $_SERVER['SERVER_PORT'] <> 443 && $_SERVER['SERVER_PORT'] > 0 && strpos($_SERVER['HTTP_HOST'], ":") === false)
					$host .= ":".$_SERVER['SERVER_PORT'];
				$url = "?redirect_test=Y&done=Y&unique_id=".checker_get_unique_id();
				header("Request-URI: ".$protocol."://".$host.$url);
				header("Content-Location: ".$protocol."://".$host.$url);
				header("Location: ".$protocol."://".$host.$url);
				exit;
			}
		break;
		default:
		break;
	}

	if (isset($_GET['fix_mode']) && ($fix_mode = intval($_GET['fix_mode'])))
	{
		if (isset($_REQUEST['charset']) && $_REQUEST['charset'])
		{
			define('LANG_CHARSET', preg_replace('#[^a-z0-9\-]#i', '', $_REQUEST['charset']));
			header('Content-type: text/plain; charset='.LANG_CHARSET);
		}
		define('LANGUAGE_ID', preg_match('#[a-z]{2}#',$_REQUEST['lang'] ?? '',$regs) ? $regs[0] : 'en');
		if (file_exists($file = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/lang/'.LANGUAGE_ID.'/admin/site_checker.php'))
			include_once($file);
		else
			include_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/lang/en/admin/site_checker.php');

		InitPureDB();

		$oTest = new CSiteCheckerTest($_REQUEST['step'] ?? 0, 0, $fix_mode);
		if (file_exists(DEBUG_FLAG))
			$oTest->timeout = 30;

		if (!empty($_REQUEST['global_test_vars']) && ($d = base64_decode($_REQUEST['global_test_vars'])))
			$oTest->arTestVars = unserialize($d, ['allowed_classes' => false]);
		else
			$oTest->arTestVars = array();

		$oTest->Start();
		if ($oTest->percent < 100)
		{
			$strNextRequest = '&step='.$oTest->step.'&global_test_vars='.base64_encode(serialize($oTest->arTestVars));
			$strFinalStatus = '';
		}
		else
		{
			$strNextRequest = '';
			$strFinalStatus = '100%';
		}
		// fix mode
		echo '
			iPercent = '.$oTest->percent.';
			test_percent = '.$oTest->test_percent.';
			strCurrentTestFunc = "'.$oTest->last_function.'";
			strCurrentTestName = "'.CUtil::JSEscape($oTest->strCurrentTestName).'";
			strNextTestName = "'.CUtil::JSEscape($oTest->strNextTestName).'";
			strNextRequest = "'.CUtil::JSEscape($strNextRequest).'";
			strResult = "'.CUtil::JSEscape(str_replace(array("\r","\n"),"",$oTest->strResult)).'";
			strFinalStatus = "'.CUtil::JSEscape($strFinalStatus).'";
			test_result = '.($oTest->result === true ? 1 : ($oTest->result === false ? -1 : 0)).'; // 0 = note
		';
	}
	die();
}
// END NO AUTH TESTS

if (file_exists(DEBUG_FLAG))
{
	define('NOT_CHECK_PERMISSIONS', true);
}

if(isset($_REQUEST['test_start']) && $_REQUEST['test_start'])
{
	define("NO_KEEP_STATISTIC", true);
	define("NO_AGENT_CHECK", true);
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
IncludeModuleLangFile(__FILE__);

define("HELP_FILE", "utilities/site_checker.php");
//error_reporting(E_ALL &~E_NOTICE);

define("SUPPORT_PAGE", (LANGUAGE_ID == 'ru' ? 'https://www.1c-bitrix.ru/support/' : 'https://www.bitrixsoft.com/support/'));

if ($USER->CanDoOperation('view_other_settings'))
{
	if (file_exists(DEBUG_FLAG))
		if (!unlink(DEBUG_FLAG))
			CAdminMessage::ShowMessage(Array("TYPE"=>"ERROR", "MESSAGE"=>'Can\'t delete ' . DEBUG_FLAG));
}
elseif(!defined('NOT_CHECK_PERMISSIONS') || NOT_CHECK_PERMISSIONS !== true)
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

if (isset($_POST['access_check']) && $_POST['access_check'])
{
	if (defined('NOT_CHECK_PERMISSIONS') && NOT_CHECK_PERMISSIONS ===true || check_bitrix_sessid())
	{
		$ob = new CSearchFiles;
		$ob->TimeLimit = 10;

		if (isset($_REQUEST['break_point']) && $_REQUEST['break_point'])
		{
			$ob->SkipPath = $_REQUEST['break_point'];
		}

		$check_type = $_REQUEST['check_type'] ?? '';

		$sNextPath = '';
		if ($check_type == 'upload')
		{
			if (!file_exists($tmp = $_SERVER['DOCUMENT_ROOT'].BX_PERSONAL_ROOT.'/tmp'))
				mkdir($tmp);
			$upload = $_SERVER['DOCUMENT_ROOT'].'/'.COption::GetOptionString('main', 'upload_dir', 'upload');

			if (0 === mb_strpos($_REQUEST['break_point'] ?? '', $upload))
				$path = $upload;
			else
			{
				$path = $tmp;
				$sNextPath = $upload;
			}
		}
		elseif($check_type == 'kernel')
			$path = $_SERVER['DOCUMENT_ROOT'].'/bitrix';
		elseif($check_type == 'personal')
			$path = $_SERVER['DOCUMENT_ROOT'].BX_PERSONAL_ROOT;
		else
		{
			$path = $_SERVER['DOCUMENT_ROOT'];
			$check_type = 'full';
		}

		if ($ob->Search($path))
		{
			if ($ob->BreakPoint || $sNextPath)
			{
				if ($ob->BreakPoint)
					$sNextPath = $ob->BreakPoint;
				$cnt_total = intval($_REQUEST['cnt_total'] ?? 0) + $ob->FilesCount;
				?><form method=post id=postform>
					<input type=hidden name=access_check value="Y">
					<input type=hidden name=lang value="<?=LANGUAGE_ID?>">
					<?=bitrix_sessid_post();?>
					<input type=hidden name=cnt_total value="<?=$cnt_total?>">
					<input type=hidden name=check_type value="<?=$check_type?>">
					<input type=hidden name=break_point value="<?=htmlspecialcharsbx($sNextPath)?>">
				</form>
				<?
				CAdminMessage::ShowMessage(array(
					'TYPE' => 'OK',
					'HTML' => true,
					'MESSAGE' => GetMessage('SC_TESTING'),
					'DETAILS' => str_replace(array('#NUM#','#PATH#'),array($cnt_total,$sNextPath),GetMessage('SC_FILES_CHECKED')),
					)
				);
				?>
				<script>
				if (parent.document.getElementById('access_submit').disabled)
					window.setTimeout("parent.ShowWaitWindow();document.getElementById('postform').submit()",500);
				</script><?
			}
			else
			{
				if ($check_type == 'full')
					COption::SetOptionString('main', 'site_checker_access', 'Y');
				CAdminMessage::ShowMessage(Array("TYPE"=>"OK", "MESSAGE"=>GetMessage("SC_FILES_OK")));
				?><script>parent.access_check_start(0);</script><?
			}
		}
		else
		{
			COption::SetOptionString('main', 'site_checker_access', 'N');
			CAdminMessage::ShowMessage(array(
				'TYPE' => 'ERROR',
				'MESSAGE' => GetMessage("SC_FILES_FAIL"),
				'DETAILS' => implode("<br>",$ob->arFail),
				'HTML' => true
				)
			);
			?><script>parent.access_check_start(0);</script><?
		}
	}
	else
		echo '<h1>Permission denied: BITRIX SESSID ERROR</h1>';
	exit;
}
elseif(isset($_REQUEST['test_start']) && $_REQUEST['test_start'])
{
	if (defined('NOT_CHECK_PERMISSIONS') && NOT_CHECK_PERMISSIONS ===true || check_bitrix_sessid())
	{
		$fast = isset($_REQUEST['fast']) ? (int)$_REQUEST['fast'] : 0;
		$oTest = new CSiteCheckerTest($_REQUEST['step'] ?? 0, $fast);
		if (isset($_REQUEST['global_test_vars']) && ($d = base64_decode($_REQUEST['global_test_vars'])))
		{
			$oTest->arTestVars = unserialize($d, ['allowed_classes' => false]);
		}

		$oTest->Start();
		if ($oTest->percent < 100)
		{
			$strNextRequest = '&step='.$oTest->step.'&global_test_vars='.base64_encode(serialize($oTest->arTestVars));
			$strFinalStatus = '';
		}
		else
		{
			$strNextRequest = '';
			$strFinalStatus = '100%';
		}
		// test mode
		echo '
			iPercent = '.$oTest->percent.';
			test_percent = '.$oTest->test_percent.';
			strCurrentTestFunc = "'.$oTest->last_function.'";
			strCurrentTestName = "'.CUtil::JSEscape($oTest->strCurrentTestName).'";
			strNextTestName = "'.CUtil::JSEscape($oTest->strNextTestName).'";
			strNextRequest = "'.CUtil::JSEscape($strNextRequest).'";
			strResult = "'.CUtil::JSEscape(str_replace(array("\r","\n"),"",$oTest->strResult)).'";
			strFinalStatus = "'.CUtil::JSEscape($strFinalStatus).'";
			strGroupName = "'.CUtil::JSEscape($oTest->group_name).'";
			strGroupDesc = "'.CUtil::JSEscape($oTest->group_desc).'";
			test_result = '.($oTest->result === true ? 1 : ($oTest->result === false ? -1 : 0)).'; // 0 = note
		';
	}
	else
		echo '<h1>Permission denied: BITRIX SESSID ERROR</h1>';
	exit;
}
elseif (isset($_REQUEST['read_log']) && $_REQUEST['read_log']) // after prolog to send correct charset
{
	$oTest = new CSiteCheckerTest();
	$str = htmlspecialcharsEx(file_get_contents($_SERVER['DOCUMENT_ROOT'].$oTest->LogFile));

	if (($s = strlen($str)) > ini_get('pcre.backtrack_limit'))
		@ini_set('pcre.backtrack_limit', $s);

	?><!DOCTYPE HTML><html><body style="color:#666"><h1 style="color:#000"><?=GetMessage("MAIN_SC_SYSTEST_LOG")?></h1><?
	$str = preg_replace('#^[0-9]{4}-...-[0-9]{2} .*\):#m','<span style="color:#000">$0</span>', $str);

	$a = $_REQUEST['anchor'] ?? '';
	if (preg_match('#[a-z_0-9]+#', $a))
	{
		$str = preg_replace('#^.+\(' . $a . '\)#m', '<a name="' . $a . '" style="background-color:#EE3">$0</a>', $str);
	}

	$str = preg_replace('#Ok$#m', '<span style="color:#408218">$0</span>', $str);
	$str = preg_replace('#Warning$#m', '<span style="color:#663300">$0</span>', $str);
	$str = preg_replace('#Fail$#m', '<span style="color:#DD0000">$0</span>', $str);
	echo '<pre>'.$str.'</pre>';
	exit;
}
elseif (isset($_REQUEST['fix_mode']) && ($fix_mode = intval($_REQUEST['fix_mode'])))
{
	?>
	<table id="fix_table" width="100%" class="internal" style="padding:20px;padding-bottom:0;">
		<tr class="heading">
			<td class="align-left" colspan="2"><?=GetMessage('SC_GR_FIX')?></td>
		</tr>
	</table>
	<script>
		var fix_mode = <?=$fix_mode?>;
		BX.ajax.get('site_checker.php?fix_mode=' + fix_mode + '&test_start=Y&lang=<?=LANGUAGE_ID?>&charset=<?=LANG_CHARSET?>&<?=bitrix_sessid_get()?>&unique_id=<?=checker_get_unique_id()?>', fix_onload);
	</script>
	<?
	exit;
}

$bIntranet = CModule::IncludeModule('intranet');
$aTabs = array();
if ($bIntranet)
	$aTabs[] = array("DIV" => "edit0", "TAB" => GetMessage("SC_PORTAL_WORK"), "ICON" => "site_check", "TITLE" => GetMessage("SC_PORTAL_WORK_DESC"));
$aTabs[] = array("DIV" => "edit1", "TAB" => GetMessage("SC_TEST_CONFIG"), "ICON" => "site_check", "TITLE" => GetMessage("SC_FULL_CP_TEST"));
$aTabs[] = array("DIV" => "edit2", "TAB" => GetMessage("SC_TAB_2"), "ICON" => "site_check", "TITLE" => GetMessage("SC_SUBTITLE_DISK"));

$tabControl = new CAdminTabControl("tabControl", $aTabs, true, true);

$APPLICATION->SetTitle(GetMessage("SC_SYSTEM_TEST"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

?>

	<style>
		.sc_help_link {
			background: url("/bitrix/themes/.default/icons/status_icons.png") no-repeat scroll -12px -235px transparent;
			cursor:pointer;
			float:right;
			width:25px;
			height:25px;
			margin-left:10px;
		}

		.sc_icon {
			display: inline-block;
			height:25px;
			margin-right:10px;
			vertical-align: middle;
			width:25px;
		}

		.sc_icon_success {
			background: url("/bitrix/themes/.default/icons/status_icons.png") no-repeat scroll -14px -19px transparent;
		}

		.sc_icon_warning{
			background: url("/bitrix/themes/.default/icons/status_icons.png") no-repeat scroll -12px -212px transparent;
		}

		.sc_icon_error{
			background: url("/bitrix/themes/.default/icons/status_icons.png") no-repeat scroll -12px -73px transparent;
		}

		.sc_success {
			color:#408218 !important;
			vertical-align: middle;
		}

		.sc_warning {
			color:#000000;
			vertical-align: middle;
		}

		.sc_error {
			color:#DD0000 !important;
			vertical-align: middle;
		}

		.sc_code {
			border:1px solid #CCC;
			margin:10px;
			padding:10px;
			font-family:monospace;
			background-color:#FEFEFA;
		}

		.sc_progress {
			text-align:center !important;
			font-weight:bold !important;
			background-color:#b9cbdf;
			padding:2px;
			margin:10px;
		}
	</style>
	<script>
		var bTestFinished = false;

		function show_popup(title, link, confirm_text)
		{
			if (confirm_text && !confirm(confirm_text))
				return;

			var d = new BX.CAdminDialog({
				'title': title,
				'content_url': '/bitrix/admin/site_checker.php' + link,
				'draggable': true,
				'resizable': true,
				'buttons': [BX.CAdminDialog.btnClose]
			});

			d.Show();
		}

		function help_me(title, id)
		{
			var html;
			html = '<div style="font-size:1.2em;padding:20px">';
			html += (r = obTestResult[id]) ? '<div style="border:1px solid #ccc;padding:10px;"><b><?=GetMessageJS("MAIN_SC_TEST_RESULT")?></b> ' + r + '</div><br>' : '';
			html += (h = obHelp[id]) ? h : obHelp['notopic'];
			html += '<br><br><?=GetMessageJS('SC_READ_MORE_ANC')?>'.replace('#LINK#', '/bitrix/admin/site_checker.php?lang=<?=LANGUAGE_ID?>&read_log=Y&anchor=' + id + '#' + id);
			html += '</div>';

			var d = new BX.CAdminDialog({
				'title': title,
				'content': html,
				'draggable': true,
				'resizable': true,
				'buttons': [BX.CAdminDialog.btnClose]
			});

			d.Show();

		}

		function fix_onload(result)
		{
			var oRow;
			var oCell;

			try
			{
				eval(result);

				var oTable = BX('fix_table');
				if (oRow = BX('in_progress'))
				{
					oCell = oRow.cells[1];
				}
				else
				{
					oRow = oTable.insertRow(-1);
					oCell = oRow.insertCell(-1);
					oCell.style.width = '40%';
					oCell.innerHTML = strCurrentTestName;
					oCell = oRow.insertCell(-1);
				}

				if (strResult == '')
				{
					oRow.setAttribute('id', 'in_progress');
					oCell.innerHTML = '<div class="sc_progress" style="width:' + test_percent + '%">' + test_percent +  '%</div>';
				}
				else
				{
					oRow.setAttribute('id', '');
					oCell.innerHTML = SetResultColor(test_result, strResult);
				}

				if (strNextRequest)
					BX.ajax.get('site_checker.php?fix_mode=' + fix_mode + '&test_start=Y&lang=<?=LANGUAGE_ID?>&charset=<?=LANG_CHARSET?>&<?=bitrix_sessid_get()?>&unique_id=<?=checker_get_unique_id()?>' + strNextRequest, fix_onload);
			}
			catch(e)
			{
				console.log(e);
				alert('<?=GetMessageJS("SC_TEST_FAIL")?>');
			}
		}

		function set_start(val)
		{
			document.getElementById('test_start').disabled = val ? 'disabled' : '';
			document.getElementById('test_stop').disabled = val ? '' : 'disabled';
			document.getElementById('progress').style.visibility = val ? 'visible' : 'hidden';

			if (val)
			{
				ShowWaitWindow();

				obTestResult = new Object;
				if (ob = BX('express_result'))
					ob.innerHTML = '';
				if (ob = BX('express_status'))
					ob.innerHTML = '';
				document.getElementById('result').innerHTML = '<table id="result_table" width="100%" class="internal"></table>';
				document.getElementById('status').innerHTML = '<?
					$oTest = new CSiteCheckerTest();
					echo $oTest->strCurrentTestName;
				?>';

				document.getElementById('percent').innerHTML = '0%';
				document.getElementById('indicator').style.width = '0%';

				BX.ajax.get('site_checker.php?test_start=Y&lang=<?=LANGUAGE_ID?>&<?=bitrix_sessid_get()?>', test_onload);
			}
			else
				CloseWaitWindow();
		}

		var strGroupName_last = '';
		function test_onload(result)
		{
			var oRow;
			var oCell;

			try
			{
				if (result)
					eval(result);
				else
					throw 'Empty result';
			}
			catch(e)
			{
				console.log(e);
				strNextRequest = '';
				strResult = '<span class="sc_error"><?=GetMessageJS("SC_TEST_FAIL")?></span>';
			}

			if (document.getElementById('test_start').disabled) // Stop was not pressed
			{
				document.getElementById('percent').innerHTML = iPercent + '%';
				document.getElementById('indicator').style.width = iPercent + '%';
				document.getElementById('status').innerHTML = strNextTestName;

				if (!(oRow = BX('in_progress')))
				{
					var oTable = BX('result_table');
					if (strGroupName != strGroupName_last)
					{
						strGroupName_last = strGroupName;
						oRow = oTable.insertRow(-1);
						oRow.className = 'heading';
						oCell = oRow.insertCell(-1);
						oCell.className = 'align-left';
						oCell.setAttribute("colSpan", "2");
						oCell.innerHTML = strGroupName;
					}

					oRow = oTable.insertRow(-1);
					oCell = oRow.insertCell(-1);
					oCell.style.width = '40%';
					oCell.innerHTML = strCurrentTestName;
					oCell = oRow.insertCell(-1);
				}

				if (strResult != '') // test finished
				{
					oRow.setAttribute('id', '');

					oCell = oRow.cells[1];
					oCell.innerHTML = '<div class="sc_help_link"></div>' + GetIconForResult(test_result) + SetResultColor(test_result, strResult);

					var oDiv = oCell.firstChild;
					oDiv.id = strCurrentTestFunc;
					oDiv.title = strCurrentTestName;
					oDiv.onclick = function(){help_me(this.title, this.id)};
				}
				else
				{
					oRow.setAttribute('id', 'in_progress');

					oCell = oRow.cells[1];
					oCell.innerHTML = '<div class="sc_progress" style="width:' + test_percent + '%">' + test_percent +  '%</div>';
				}

				if (strNextRequest)
				{
					<? if (isset($_GET['HTTP_HOST']))
					{
						?>
						BX.ajax.get('site_checker.php?HTTP_HOST=<?=urlencode($_GET['HTTP_HOST'])?>&SERVER_PORT=<?=urlencode($_GET['SERVER_PORT'])?>&HTTPS=<?=urlencode($_GET['HTTPS'])?>&test_start=Y&lang=<?=LANGUAGE_ID?>&<?=bitrix_sessid_get()?>' + strNextRequest, test_onload);
						<?
					}
					else
					{
						?>
						BX.ajax.get('site_checker.php?HTTP_HOST=' + window.location.hostname + '&SERVER_PORT=' + window.location.port + '&HTTPS=' + (window.location.protocol == 'https:' ? 'on' : '') + '&test_start=Y&lang=<?=LANGUAGE_ID?>&<?=bitrix_sessid_get()?>' + strNextRequest, test_onload);
						<?
					}?>
				}
				else // Finish
				{
					set_start(0);
					bTestFinished = true;
				}
			}
		}

		var oExpressTable;
		var strGroupName_last_e;
		var group_num;
		var group_test_result = 1;
		var global_test_result = 1;

		var obTestResult;
		function ExpressTest(result, begin)
		{
			var oRow;
			var oCell;

			if (begin)
			{
				obTestResult = new Object;
				group_num = 1;
				global_test_result = 1;
				set_start(0);
				BX('express_start').disabled = true;
				strNextRequest = '';
				ob = BX('express_result');
				ob.innerHTML = '<table width="100%" class="internal" style="margin-top:10px"></table>';
				oExpressTable = ob.firstChild;
				ShowWaitWindow();
			}
			else
			{
				try
				{
					if (result)
						eval(result);
					else
						throw 'Empty result';

					if (strResult)
					{
						if (test_result < global_test_result)
							global_test_result = test_result;

						if (strNextRequest)
							BX('express_status').innerHTML = '<div class="sc_progress" style="width:' + iPercent + '%">' + iPercent + '%</div>';
						else
						{
							strGroupName = '';
							if (test_result < group_test_result)
								group_test_result = test_result;
							BX('express_status').innerHTML = global_test_result == 1 ? '<h3>' + SetResultColor(1, '<?=GetMessageJS("MAIN_SC_ALL_FUNCS_TESTED")?>') + '</h3>' : '';
						}

						if (strGroupName != strGroupName_last_e)
						{
							if (oRow = BX('express_group' + group_num))
							{
								html = GetIconForResult(group_test_result);
								if (group_test_result == 1)
									html += '<span onclick="ShowTestResult(' + group_num + ')" class="sc_success" style="cursor:pointer;border-bottom:1px dashed"><?=GetMessageJS("SC_ERRORS_NOT_FOUND")?></span>';
								else if (group_test_result == -1)
									html += '<span onclick="ShowTestResult(' + group_num + ')" class="sc_error" style="cursor:pointer;border-bottom:1px dashed"><?=GetMessageJS("SC_ERRORS_FOUND")?></span>'
								else
									html += '<span onclick="ShowTestResult(' + group_num + ')" class="sc_warning" style="cursor:pointer;border-bottom:1px dashed"><?=GetMessageJS("SC_WARNINGS_FOUND")?></span>'
								oCell = oRow.cells[1];
								oCell.innerHTML = html;

								if (group_test_result == -1)
									window.setTimeout('ShowTestResult(' + group_num + ', 1)', 100);

								group_num++;
							}

							group_test_result = test_result;

							if (strNextRequest)
							{
								strGroupName_last_e = strGroupName;
								oRow = oExpressTable.insertRow(-1);
								oRow.id = 'express_group' + group_num;
								oRow.className = 'heading';

								oCell = oRow.insertCell(-1);
								oCell.className = 'align-left';
								oCell.style.width = '40%';
								oCell.innerHTML = strGroupName;

								oCell = oRow.insertCell(-1);
								oCell.className = "align-left";

								oCell.innerHTML = '<span style="color:black"><?=GetMessageJS("SC_TESTING1")?></span>';
							}
						}
						else if (test_result < group_test_result)
							group_test_result = test_result;

						oRow = oExpressTable.insertRow(-1);
						oRow.style.display = 'none';
						oCell = oRow.insertCell(-1);
						oCell.style.width = '40%';
						oCell.innerHTML = strCurrentTestName;
						oCell = oRow.insertCell(-1);
						oCell.innerHTML = '<div class="sc_help_link"></div>' + GetIconForResult(test_result, 1);
						obTestResult[strCurrentTestFunc] = SetResultColor(test_result, strResult);

						var oDiv = oCell.firstChild;
						oDiv.id = strCurrentTestFunc;
						oDiv.title = '<?=GetMessageJS("SC_HELP")?> ' + strCurrentTestName;
						oDiv.onclick = function(){help_me(this.title, this.id)};
					}
				}
				catch(e)
				{
					console.log(e);
					strNextRequest = '';
					BX('express_status').innerHTML = result;
				}
			}

			HTTP_HOST = 	(tmp = "<?=urlencode($_GET['HTTP_HOST'] ?? '')?>") ? tmp : window.location.hostname;
			SERVER_PORT = 	(tmp = "<?=urlencode($_GET['SERVER_PORT'] ?? '')?>") ? tmp : window.location.port;
			HTTPS = 	(tmp = "<?=urlencode($_GET['HTTPS'] ?? '')?>") ? tmp : (window.location.protocol == 'https:' ? 'on' : '');

			if (strNextRequest || begin)
				BX.ajax.get('site_checker.php?test_start=Y&fast=1&lang=<?=LANGUAGE_ID?>&<?=bitrix_sessid_get()?>&HTTP_HOST=' + HTTP_HOST + '&SERVER_PORT=' + SERVER_PORT + '&HTTPS=' + HTTPS + strNextRequest, ExpressTest);
			else
			{
				BX('express_start').disabled = false;
				CloseWaitWindow();
			}
		}

		function ShowTestResult(num, open)
		{
			var start = 0;
			l = oExpressTable.rows.length;
			for(i = 0; i < l; i++)
			{
				var oRow = oExpressTable.rows[i];
				if (oRow.id == 'express_group' + num)
				{
					start = 1;
				}
				else if (start)
				{
					if (oRow.className != '')
						break;

					oRow.style.display = oRow.style.display == 'none' || open ? '' : 'none';
				}
			}
		}

		function GetIconForResult(test_result, bText)
		{
			if (test_result == 1)
				return '<div class="sc_icon sc_icon_success"></div>' + (bText ? '<span class="sc_success"><?=GetMessageJS("MAIN_SC_FUNC_WORKS_FINE")?></span>' : '');
			else if (test_result == 0)
				return '<div class="sc_icon sc_icon_warning"></div>' + (bText ? '<span class="sc_warning"><?=GetMessageJS("MAIN_SC_FUNC_WORKS_PARTIAL")?></span>' : '');
			else if (test_result == -1)
				return '<div class="sc_icon sc_icon_error"></div>' + (bText ? '<span class="sc_error"><?=GetMessageJS("MAIN_SC_FUNC_WORKS_WRONG")?></span>' : '');
		}

		function SetResultColor(test_result, text)
		{
			return (test_result == 1 ? '<span class="sc_success">' : test_result == 0 ? '<span class="sc_warning">' : '<span class="sc_error">') + text + '</span>';
		}

		<?=(isset($_REQUEST['express_test']) && $_REQUEST['express_test'] ? 'window.setTimeout(\'ExpressTest("", true)\', 500);' : '')?>
		<?=(isset($_REQUEST['start_test']) && $_REQUEST['start_test'] ? 'window.setTimeout(\'set_start(1)\', 500);' : '')?>
	</script>

<?
$tabControl->Begin();

if ($bIntranet)
{
	// portal checker
$tabControl->BeginNextTab();
?>
	<tr>
	<td colspan="2">
		<input type=button id="express_start" value="<?=GetMessage("SC_TEST_START")?>" onclick="ExpressTest('', true)" class="adm-btn-green">
		<div id="express_status"></div>
	</td>
	</tr>
	<tr><td colspan="2" id="express_result"></td></tr>
<?
}

// site_checker
$tabControl->BeginNextTab();
?>
	<tr>
		<td colspan="2"><?=GetMessage("MAIN_SC_FULL_TEST_DESC")?></td>
	</tr>
	<tr>
	<td colspan="2">
		<br>
		<input type=button value="<?=GetMessage("SC_START_TEST_B")?>" id="test_start" onclick="set_start(1)" class="adm-btn-green">
		<input type=button value="<?=GetMessage("SC_STOP_TEST_B")?>" disabled id="test_stop" onclick="set_start(0)">
		<div id="progress" style="visibility:hidden;padding-top:4px;" width="100%">
			<div id="status" style="font-weight:bold;font-size:1.2em"></div>
			<table border="0" cellspacing="0" cellpadding="2" width="100%">
				<tr>
					<td height="20">
						<div style="border:1px solid #B9CBDF">
							<div id="indicator" style="height:20px; width:0; background-color:#B9CBDF;transition: width 0.5s;"></div>
						</div>
					</td>
					<td width=30>&nbsp;<span id="percent" style="font-size:1.4em">0%</span></td>
				</tr>
			</table>
		</div>
		<div id="result" style="padding-top:10px"></div>




	</td>
	</tr>
<?
// disk permissions
$tabControl->BeginNextTab();
?>
	<tr>
		<td colspan="2"><?echo GetMessage("SC_SUBTITLE_DISK_DESC");?></td>
	</tr>
	<tr>
		<td colspan="2">
		<script>
		var obHelp = new Object;
		obHelp["notopic"] = "<?=CUtil::JSEscape(GetMessage('SC_HELP_NOTOPIC'))?>";
		<?
		foreach(CSiteCheckerTest::GetTestList() as $test)
		{
			$help = GetMessage('SC_HELP_'.mb_strtoupper($test));
			$help = str_replace('<code>','<div class="sc_code">',$help);
			$help = str_replace('</code>','</div>',$help);
			$help = str_replace("\r", "", $help);
			$help = str_replace("\n", "<br>", $help);
			$help = str_replace("<a href=", "<a target=_blank href=", $help);
			echo 'obHelp["'.$test.'"] = "'.CUtil::JSEscape($help).'";'."\n";
		}
		?>

		function onFrameLoad(ob)
		{
			CloseWaitWindow();
			var oDoc;
			if (ob.contentDocument)
				oDoc = ob.contentDocument;
			else
				oDoc = ob.contentWindow.document;

			document.getElementById('access_result').innerHTML = oDoc.body.innerHTML
		}

		function access_check_start(val)
		{
			document.getElementById('access_submit').disabled = val ? 'disabled' : '';
			document.getElementById('access_stop').disabled = val ? '' : 'disabled';

			if (val)
				ShowWaitWindow();
			else
				CloseWaitWindow();
		}
		</script>
			<? // CAdminMessage::ShowMessage(Array("MESSAGE"=>GetMessage("SC_CHECK_FILES_ATTENTION"), "TYPE"=>"ERROR","DETAILS"=>GetMessage("SC_CHECK_FILES_WARNING")));	?>
			<form method="POST" action="site_checker.php" target="access_frame" onsubmit="access_check_start(1)">
			<input type=hidden name=access_check value=Y>
			<input type=hidden name=lang value="<?=LANGUAGE_ID?>">
			<?=bitrix_sessid_post();?>
			<label><input type=radio name=check_type value=full checked> <?=GetMessage("SC_CHECK_FULL")?></label><br>
			<label><input type=radio name=check_type value=upload> <?=GetMessage("SC_CHECK_UPLOAD")?></label><br>
			<label><input type=radio name=check_type value=kernel> <?=GetMessage("SC_CHECK_KERNEL")?></label><br>
			<? if ('/bitrix' != BX_PERSONAL_ROOT): ?>
				<label><input type=radio name=check_type value=cache> <?=GetMessage("SC_CHECK_FOLDER")?> <b><?=BX_PERSONAL_ROOT?></b></label><br>
			<? endif; ?>
			<br>
			<input type=submit value="<?=GetMessage("SC_CHECK_B")?>" id="access_submit">
			<input type=button value="<?=GetMessage("SC_STOP_B")?>" disabled id="access_stop" onclick="access_check_start(0)">
			</form>
			<div width="100%" id="access_result"></div>
			<iframe name="access_frame" style="width:1px;height:1px;visibility:hidden" onload="onFrameLoad(this)"></iframe>
		</td>
	</tr>
<?
?>
<script>
</script>
<?
		?>
<?
//$tabControl->Buttons();
$tabControl->End();
$tabControl->ShowWarnings("fticket", $message);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>
