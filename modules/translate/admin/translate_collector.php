<?
/** @global CMain $APPLICATION */
use Bitrix\Main,
	Bitrix\Main\Loader;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/translate/prolog.php");
$TRANS_RIGHT = $APPLICATION->GetGroupRight("translate");
if($TRANS_RIGHT != "W")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
Loader::includeModule('translate');
IncludeModuleLangFile(__FILE__);
define("HELP_FILE","translate_list.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/tar_gz.php");

$arrTransEncoding = array(
	'windows-1250' => 'windows-1250 (ISO 8859-2)',
	'windows-1251' => 'windows-1251',
	'windows-1252' => 'windows-1252 (ISO 8859-1)',
	'windows-1253' => 'windows-1253',
	'windows-1254' => 'windows-1254',
	'windows-1255' => 'windows-1255',
	'windows-1256' => 'windows-1256',
	'windows-1257' => 'windows-1257',
	'windows-1258' => 'windows-1258'
);

@set_time_limit(0);

$bUseCompression = True;
if (!extension_loaded('zlib') || !function_exists("gzcompress"))
	$bUseCompression = False;

$butf = defined('BX_UTF') && BX_UTF == 'Y';

function __CopyDirFiles($path_from, $path_to, $ReWrite = True, $Recursive = False, $bConvert = False, $strEncodingIn = '', $strEncodingOut = '')
{
	global $APPLICATION;

	if (strpos($path_to."/", $path_from."/")===0)
		return False;

	if (is_dir($path_from))
	{
		CheckDirPath($path_to."/");
	}
	else
	{
		return True;
	}

	if ($handle = @opendir($path_from))
	{
		while (($file = readdir($handle)) !== false)
		{
			if ($file == "." || $file == ".." || $file == '.access.php' || $file == '.htaccess')
				continue;

			if (is_dir($path_from."/".$file) && $Recursive)
			{
				__CopyDirFiles($path_from."/".$file, $path_to."/".$file, $ReWrite, $Recursive, $bConvert, $strEncodingIn, $strEncodingOut);

			}
			elseif (is_file($path_from."/".$file))
			{
				if (file_exists($path_to."/".$file) && !$ReWrite)
					continue;

				@copy($path_from."/".$file, $path_to."/".$file);
				@chmod($path_to."/".$file, BX_FILE_PERMISSIONS);
				$filesrc_tmp = $APPLICATION->GetFileContent($path_to."/".$file);
				$filesrc_tmp = str_replace("\r\n", "\n", $filesrc_tmp);
				$filesrc_tmp = str_replace("\r", "\n", $filesrc_tmp);
				if ($bConvert)
					$filesrc_tmp = $APPLICATION->ConvertCharset($filesrc_tmp, $strEncodingIn, $strEncodingOut);
				$APPLICATION->SaveFileContent($path_to."/".$file, $filesrc_tmp);
			}
		}
		@closedir($handle);

		return true;
	}

	return false;
}

function __ReWalkDirs($pathFrom, $pathTo, $language_id, $bConvert = false, $strEncodingIn = '', $strEncodingOut = '')
{
	$handle = @opendir($pathFrom);
	if ($handle)
	{
		while (false !== ($dir = readdir($handle)))
		{
			if (!is_dir($pathFrom."/".$dir) || $dir == "." || $dir == ".." || $dir == ".hg" || $dir == ".svn")
				continue;

			if ($dir == "lang" || (strlen($pathFrom) -  strrpos($pathFrom, 'payment')) == 7)
			{
				if (file_exists($pathFrom."/".$dir."/".$language_id))
				{
					CheckDirPath($pathTo."/".$dir."/".$language_id."/");
					__CopyDirFiles($pathFrom."/".$dir."/".$language_id, $pathTo."/".$dir."/".$language_id, true, true, $bConvert, $strEncodingIn, $strEncodingOut);
				}
			}
			else
			{
				__ReWalkDirs($pathFrom."/".$dir, $pathTo."/".$dir, $language_id, $bConvert, $strEncodingIn, $strEncodingOut);
			}
		}
		closedir($handle);
	}
}

$bConvert = false;

$language_id = $_REQUEST["language_id"];
$lang_date = $_REQUEST["lang_date"];
$encoding = $_REQUEST["encoding"];
$pack_files = $_REQUEST["pack_files"];
$strEncodingIn = '';
$strEncodingOut = '';

$strErrorMessage = "";
$strOKMessage = "";
if ($_SERVER["REQUEST_METHOD"]=="POST" && $_REQUEST["start_collect"]=="Y" && check_bitrix_sessid())
{
	if (strlen($language_id)!=2)
		$strErrorMessage .= GetMessage('TR_ERROR_SELECT_LANGUAGE').'<br>';

	$language = Main\Localization\LanguageTable::getList(array(
		'select' => array('LID', 'CHARSET' => 'CULTURE.CHARSET'),
		'filter' => array('=LID' => $language_id)
	))->fetch();
	if (empty($language))
		$strErrorMessage .= GetMessage('TR_ERROR_LANGUAGE_ID');

	$lang_date = preg_replace("/[\D]+/", "", $lang_date);

	if (strlen($lang_date)!=8)
		$strErrorMessage .= GetMessage('TR_ERROR_LANGUAGE_DATE');

	$bConvert = isset($_REQUEST['convert_encoding']) && $_REQUEST['convert_encoding'] == 'Y';
	if ($butf && $bConvert && (!isset($_REQUEST["encoding"]) || !array_key_exists($_REQUEST["encoding"], $arrTransEncoding)))
		$strErrorMessage .= GetMessage('TR_ERROR_ENCODING').'<br>';

	if (strlen($strErrorMessage)<=0)
	{
		$targetLanguagePath = $_SERVER["DOCUMENT_ROOT"]."/bitrix/updates/_langs/".$language_id;
		CheckDirPath($targetLanguagePath."/");
		if (!file_exists($targetLanguagePath) || !is_dir($targetLanguagePath))
			$strErrorMessage .= GetMessage('TR_ERROR_CREATE_TARGET_FOLDER', array('%PATH%' => $targetLanguagePath)).'<br>';
	}

	if (strlen($strErrorMessage)<=0)
	{
		DeleteDirFilesEx("/bitrix/updates/_langs/".$language_id);
		clearstatcache();
		if (file_exists($targetLanguagePath))
			$strErrorMessage .= GetMessage('TR_ERROR_DELETE_TARGET_FOLDER', array('%PATH%' => $targetLanguagePath)).'<br>';
	}

	if (strlen($strErrorMessage)<=0)
	{
		clearstatcache();
		CheckDirPath($targetLanguagePath."/");
		clearstatcache();
		if (!file_exists($targetLanguagePath) || !is_dir($targetLanguagePath))
			$strErrorMessage .= GetMessage('TR_ERROR_CREATE_TARGET_FOLDER', array('%PATH%' => $targetLanguagePath)).'<br>';
	}

	if (strlen($strErrorMessage)<=0)
	{
		if ($bConvert)
		{
			if ($butf)
			{
				$strEncodingIn = 'utf-8';
				$strEncodingOut = $_REQUEST["encoding"];
				if (strtolower($language['CHARSET']) != 'utf-8')
					$strErrorMessage .= GetMessage('TR_ERROR_LANGUAGE_CHARSET_NON_UTF');
			}
			else
			{
				$strEncodingIn = LANG_CHARSET;
				$strEncodingOut = 'utf-8';
				if (LANG_CHARSET == 'utf-8')
					$bConvert = false;
			}
		}
	}

	if ($strErrorMessage == '')
	{
		__ReWalkDirs($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules", $targetLanguagePath, $language_id, $bConvert, $strEncodingIn, $strEncodingOut);

		if ($fp1 = fopen($targetLanguagePath."/main/lang/".$language_id."/supd_lang_date.dat", "wb"))
		{
			fwrite($fp1, $lang_date);
			fclose($fp1);
		}
		else
		{
			$strErrorMessage .= GetMessage('TR_ERROR_OPEN_FILE', array('%FILE%' => $targetLanguagePath."/main/lang/".$language_id."/supd_lang_date.dat")).'<br>';
		}
	}

	if (strlen($strErrorMessage)<=0)
	{
		if ($pack_files=="Y")
		{
			@unlink($_SERVER["DOCUMENT_ROOT"]."/bitrix/updates/_langs/file-".$language_id.".tar.gz");

			$oArc = new CArchiver($_SERVER["DOCUMENT_ROOT"]."/bitrix/updates/_langs/file-".$language_id.".tar.gz", $bUseCompression);
			$oArc->_strSeparator = '|';
			$res = $oArc->Add($_SERVER["DOCUMENT_ROOT"]."/bitrix/updates/_langs/".$language_id, false, $_SERVER["DOCUMENT_ROOT"]."/bitrix/updates/_langs");
			if (!$res)
			{
				$strErrorMessage .= GetMessage('TR_ERROR_ARCHIVE').'<br>';
				if (count($oArc->_arErrors) > 0)
				{
					$strErrorMessage .= ": ";
					foreach ($oArc->_arErrors as $e)
						$strErrorMessage .= $e[1].", ";
				}
			}
			else
			{
				$strOKMessage = GetMessage('TR_LANGUAGE_COLLECTED_ARCHIVE', array('%LANG%' => $language_id,
																				'%FILE_PATH%' => $_SERVER["DOCUMENT_ROOT"]."/bitrix/updates/_langs/file-".$language_id.".tar.gz",
																				'%LINK%' =>	"<a href=\"/bitrix/updates/_langs/file-".$language_id.".tar.gz\">file-".$language_id.".tar.gz</a>"
				));
			}
		}
	}

	if (strlen($strErrorMessage)<=0 && strlen($strOKMessage)<=0)
	{
		$strOKMessage = GetMessage('TR_LANGUAGE_COLLECTED_FOLDER', array('%LANG%' => $language_id, '%PATH%' => $targetLanguagePath));
	}
}
else if ($_SERVER["REQUEST_METHOD"]=="POST" && $_REQUEST["start_download"]=="Y" && check_bitrix_sessid())
{
	if (!(array_key_exists('tarfile', $_FILES) &&
		array_key_exists('tmp_name', $_FILES['tarfile']) &&
		file_exists($_FILES['tarfile']['tmp_name']) &&
		$_FILES['tarfile']['error'] == 0))
		$strErrorMessage .= GetMessage('TR_ERROR_TARFILE').'<br>';

	if ($strErrorMessage == '')
	{
		$tmpFileName = strtolower($_FILES['tarfile']['name']);
		if (
			substr($tmpFileName, -7) !== '.tar.gz'
			&& substr($tmpFileName, -4) !== '.tar'
		)
			$strErrorMessage .= GetMessage('TR_ERROR_TARFILE_EXTENTION').'<br>';
		unset($tmpFileName);
	}

	if (strlen($language_id)!=2)
		$strErrorMessage .= GetMessage('TR_ERROR_SELECT_LANGUAGE').'<br>';

	$language = Main\Localization\LanguageTable::getList(array(
		'select' => array('LID', 'CHARSET' => 'CULTURE.CHARSET'),
		'filter' => array('=LID' => $language_id)
	))->fetch();
	if (empty($language))
		$strErrorMessage .= GetMessage('TR_ERROR_LANGUAGE_ID');

	$bConvert = isset($_REQUEST['convert_encoding']) && $_REQUEST['convert_encoding'] == 'Y';
	if ($butf && $bConvert && (!isset($_REQUEST["encoding"]) || !in_array($_REQUEST["encoding"], $arrTransEncoding)))
		$strErrorMessage .= GetMessage('TR_ERROR_ENCODING').'<br>';

	$tempLanguagePathNoRoot = '/bitrix/tmp/translate/'.time().'/';
	$tempLanguagePath = $_SERVER["DOCUMENT_ROOT"].$tempLanguagePathNoRoot;

	if (strlen($strErrorMessage)<=0)
	{
		CheckDirPath($tempLanguagePath);
		if (!file_exists($tempLanguagePath) || !is_dir($tempLanguagePath))
			$strErrorMessage .= GetMessage('TR_ERROR_CREATE_TEMP_FOLDER', array('%PATH%' => $tempLanguagePath)).'<br>';
	}

	if (strlen($strErrorMessage)<=0)
	{
		$oArc = new CArchiver($_FILES['tarfile']['tmp_name'], true);
		$oArc->_strSeparator = '|';
		$oArc->extractFiles($tempLanguagePath);
		if (count($oArc->_arErrors) > 0)
		{
			$strErrorMessage .= ": ";
			foreach ($oArc->_arErrors as $e)
				$strErrorMessage .= $e[1].", ";
		}
	}

	if (strlen($strErrorMessage)<=0)
	{
		$bConvert = isset($_REQUEST['localize_encoding']) && $_REQUEST['localize_encoding'] == 'Y';
		if ($bConvert)
		{
			if ($butf)
			{
				$strEncodingIn = $_REQUEST["encoding"];
				$strEncodingOut = 'utf-8';
			}
			else
			{
				$strEncodingIn = 'utf-8';
				$strEncodingOut = LANG_CHARSET;
				if (LANG_CHARSET == 'utf-8')
					$bConvert = false;
			}
		}

		__ReWalkDirs($tempLanguagePath.$language_id.'/', $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules", $language_id,
					$bConvert, $strEncodingIn, $strEncodingOut);
	}

	//delete tmp files
	DeleteDirFilesEx($tempLanguagePathNoRoot);
	clearstatcache();
	if (strlen($strErrorMessage)<=0)
		$strOKMessage = GetMessage('TR_LANGUAGE_DOWNLOADED');
}
else
{
	$lang_date = date('Ymd');
	$language_id = LANGUAGE_ID;
	$encoding = '';
}

$APPLICATION->SetTitle(GetMessage('TRANS_TITLE'));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$aTabs = array(
	array("DIV" => "upload", "TAB" => GetMessage("TRANS_UPLOAD"),  "TITLE" => GetMessage("TRANS_UPLOAD_TITLE"),
		'ONSELECT' => "BX('tr_submit').value='".GetMessage("TR_COLLECT_LANGUAGE")."'"),
	array("DIV" => "download", "TAB" => GetMessage("TRANS_DOWNLOAD"), "TITLE" => GetMessage("TRANS_DOWNLOAD_TITLE"),
		'ONSELECT' => "BX('tr_submit').value='".GetMessage("TR_DOWNLOAD_LANGUAGE")."'"),
);

$tabControl = new CAdminTabControl("tabControl", $aTabs, false, true);

if ($strErrorMessage != '')
{
	$message = new CAdminMessage(array('MESSAGE' => $strErrorMessage, 'TYPE' => 'ERROR'));
	echo $message->Show();
}
if ($strOKMessage != '')
{
	$message = new CAdminMessage(array('MESSAGE' => $strOKMessage, 'TYPE' => 'OK', 'HTML' => true));
	echo $message->Show();
}

$tabControl->Begin();
$tabControl->BeginNextTab();

?>
<form method="post" action="" name="form1">
<input type="hidden" name="start_collect" value="Y">
<input type="hidden" name="tabControl_active_tab" value="upload">
<?=bitrix_sessid_post()?>
	<tr class="adm-required-field">
		<td width="40%"><?echo GetMessage("TR_SELECT_LANGUAGE")?>:</td>
		<td width="60%">
		<select name="language_id">
			<?
			$rsLang = CLanguage::GetList($by="sort", $order="desc");
			while ($arLang = $rsLang->Fetch())
			{
				if (is_dir($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/lang/".$arLang['LANGUAGE_ID']))
				{
					?><option value="<?=htmlspecialcharsbx($arLang['LANGUAGE_ID']); ?>"<?if ($arLang['LANGUAGE_ID']==$language_id) echo " selected";?>><?=htmlspecialcharsbx($arLang['LANGUAGE_ID']); ?></option><?
				}
			}
			?>
		</select>
		</td>
	</tr>

	<tr class="adm-required-field">
		<td><?echo GetMessage("TR_COLLECT_DATE")?>:</td>
		<td><input type="text" name="lang_date" size="10" maxlength="8" value="<?= htmlspecialcharsbx($lang_date) ?>"></td>
	</tr>
	<? if (!$butf)
	{ ?>
	<tr>
		<td><?echo GetMessage("TR_CONVERT_UTF8")?>:</td>
		<td><input type="checkbox" name="convert_encoding" value="Y" <? echo $bConvert ? 'checked="checked"' : '' ?>></td>
	</tr>
	<? }
	else
	{ ?>
	<tr>
		<td><?echo GetMessage("TR_CONVERT_NATIONAL")?>:</td>
		<td><input type="checkbox" name="convert_encoding" value="Y" <? echo $bConvert ? 'checked="checked"' : '' ?> onClick="EncodeClicked()"></td>
	</tr>
	<tr>
		<td width="40%"><?echo GetMessage("TR_CONVERT_ENCODING")?>:</td>
		<td width="60%">
		<select name="encoding">
		<?
		foreach ($arrTransEncoding as $_k => $v)
		{
			?><option value="<?=htmlspecialcharsbx($_k); ?>"<?if ($_k==$encoding) echo " selected";?>><?= $v ?></option><?
		}
		?>
		</select>
		<script type="text/javascript">
			function EncodeClicked()
			{
				document.form1.encoding.disabled = !document.form1.convert_encoding.checked;
			}
			EncodeClicked();
		</script>
		</td>
	</tr>
	<? } ?>
	<tr>
		<td><?echo GetMessage("TR_PACK_FILES")?>:</td>
		<td><input type="checkbox" name="pack_files" value="Y" <? echo $pack_files=="Y" ?  'checked="checked"' : '' ?>></td>
	</tr>
</form>
<?

$tabControl->EndTab();
?>
<form method="post" action="" name="form2" enctype="multipart/form-data">
<?
$tabControl->BeginNextTab();

?>

<input type="hidden" name="start_download" value="Y">
<input type="hidden" name="tabControl_active_tab" value="download">
<?=bitrix_sessid_post()?>
<tr class="adm-required-field">
	<td width="10%" nowrap><?=GetMessage("TR_UPLOAD_FILE")?>:</td>
	<td valign="top" width="90%"><input type="file" name="tarfile"></td>
</tr>
<tr class="adm-required-field">
	<td width="40%"><?=GetMessage("TR_SELECT_LANGUAGE")?> <?=GetMessage("TR_SELECT_LANGUAGE_DESCRIPTION")?>:</td>
	<td width="60%">
	<select name="language_id">
		<?
		$rsLang = CLanguage::GetList($by="sort", $order="desc");
		while ($arLang = $rsLang->Fetch())
		{
			?><option value="<?=htmlspecialcharsbx($arLang['LANGUAGE_ID']); ?>"<?if ($arLang['LANGUAGE_ID']==$language_id) echo " selected";?>><?=htmlspecialcharsbx($arLang['LANGUAGE_ID']); ?></option><?
		}
		?>
	</select>
	</td>
</tr><?
if (!$butf)
{
	?><tr>
	<td><?echo GetMessage("TR_CONVERT_FROM_UTF8")?>:</td>
	<td><input type="checkbox" name="localize_encoding" value="Y" <?=($bConvert ? 'checked="checked"' : ''); ?>></td>
	</tr><?
}
else
{
	?><tr>
	<td><?echo GetMessage("TR_CONVERT_FROM_NATIONAL")?>:</td>
	<td><input type="checkbox" id="localize_encoding" name="localize_encoding" value="Y" <?=($bConvert ? 'checked="checked"' : ''); ?>></td>
	</tr>
	<tr id="tr_encoding" style="display: <?=($bConvert ? 'table-row' : 'none'); ?>;">
	<td width="40%"><?echo GetMessage("TR_CONVERT_ENCODING")?>:</td>
	<td width="60%">
	<select name="encoding"><?
	foreach ($arrTransEncoding as $_k => $v)
	{
		?><option value="<?=htmlspecialcharsbx($_k); ?>"<?if ($_k==$encoding) echo " selected";?>><?= $v ?></option><?
	}
	?></select>
	</td>
	</tr><?
}

$tabControl->EndTab();
?>
</form>
<?
$tabControl->Buttons();
?>
<input type="submit" id="tr_submit" class="adm-btn-green" value="<?=isset($_REQUEST["tabControl_active_tab"]) && $_REQUEST["tabControl_active_tab"] == 'download' ? GetMessage("TR_DOWNLOAD_LANGUAGE") : GetMessage("TR_COLLECT_LANGUAGE")?>">
<?
$tabControl->End();
?>
<script type="text/javascript">
function showConvertCharset()
{
	var target = this,
		list = BX('tr_encoding');

	if (!BX.type.isElementNode(list))
		return;
	BX.style(list, 'display', (target.checked ? 'table-row' : 'none'));
}
BX.ready(function(){
	var btnConvert = BX('localize_encoding');

	BX.bind(BX('tr_submit'), 'click', function ()
	{
		BX('tr_submit').disabled = true;
		if (BX('tabControl_active_tab').value == 'upload') {
			BX.showWait(null, "<?=GetMessage('TR_COLLECT_LOADING') ?>");
			tabControl.DisableTab("download");
			BX.submit(document.forms['form1']);
		} else {
			BX.showWait();
			tabControl.DisableTab("upload");
			BX.submit(document.forms['form2']);
		}
	});

	if (BX.type.isElementNode(btnConvert))
		BX.bind(btnConvert, 'click', showConvertCharset);
	btnConvert = null;
});
</script>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");