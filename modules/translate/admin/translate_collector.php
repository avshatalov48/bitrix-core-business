<?php
//region Head
require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/translate/prolog.php';

use Bitrix\Main,
	Bitrix\Main\Error,
	Bitrix\Main\Localization\Loc,
	Bitrix\Translate;

if (!\Bitrix\Main\Loader::includeModule('translate'))
{
	require $_SERVER['DOCUMENT_ROOT']. '/bitrix/modules/main/include/prolog_admin_after.php';

	\CAdminMessage::ShowMessage('Translate module not found');

	require $_SERVER['DOCUMENT_ROOT']. '/bitrix/modules/main/include/epilog_admin.php';
}

/** @global \CMain $APPLICATION */
$permissionRight = $APPLICATION->GetGroupRight('translate');
if ($permissionRight != Translate\Permission::WRITE)
{
	$APPLICATION->AuthForm(Loc::getMessage('ACCESS_DENIED'));
}

Loc::loadLanguageFile(__FILE__);

define('HELP_FILE', 'translate_list.php');


//endregion

//-----------------------------------------------------------------------------------
//region handle POST

$request = Main\Context::getCurrent()->getRequest();

$isPost = ($request->isPost() && check_bitrix_sessid());

$isUtfMode = Translate\Translation::isUtfMode();
$useTranslationRepository = Main\Localization\Translation::useTranslationRepository();

$enabledLanguages = Translate\Translation::getEnabledLanguages();
$availableLanguages = Translate\Translation::getAvailableLanguages();
$allLanguages = Translate\Translation::getLanguages();
$allowedEncodings = Translate\Translation::getAllowedEncodings();

$languageId = $request->get('language_id') !== null ? $request->get('language_id') : '';
$langDate = ($request->get('lang_date') !== null ? $request->get('lang_date') : '');
$encoding = ($request->get('encoding') !== null ? $request->get('encoding') : '');
$packFiles = ($request->getPost('pack_files') === 'Y');
$convertEncoding = ($request->getPost('convert_encoding') === 'Y');
$encodingIn = '';
$encodingOut = '';

$strOKMessage = '';

$errors = new Main\ErrorCollection();

//-----------------------------------------------------------------------------------
//region Action start_collect

if ($isPost && $request->getPost('start_collect') === 'Y')
{
	@set_time_limit(0);

	if (strlen($languageId) != 2)
	{
		$errors[] = new Error(Loc::getMessage('TR_ERROR_SELECT_LANGUAGE'));
	}

	if (!in_array($languageId, $allLanguages))
	{
		$errors[] = new Error(Loc::getMessage('TR_ERROR_LANGUAGE_ID'));
	}

	$langDate = preg_replace("/[\D]+/", "", $langDate);

	if (strlen($langDate) != 8)
	{
		$errors[] = new Error(Loc::getMessage('TR_ERROR_LANGUAGE_DATE'));
	}

	if ($convertEncoding && (empty($encoding) || !in_array($encoding, $allowedEncodings)))
	{
		$errors[] = new Error(Loc::getMessage('TR_ERROR_ENCODING'));
	}

	if ($errors->isEmpty())
	{
		$targetLanguagePath = Main\Application::getDocumentRoot(). Translate\WORKING_DIR. $languageId;
		CheckDirPath($targetLanguagePath."/");
		if (!file_exists($targetLanguagePath) || !is_dir($targetLanguagePath))
		{
			$errors[] = new Error(Loc::getMessage('TR_ERROR_CREATE_TARGET_FOLDER', array('%PATH%' => $targetLanguagePath)));
		}
	}

	if ($errors->isEmpty())
	{
		DeleteDirFilesEx(Translate\WORKING_DIR. $languageId);
		clearstatcache();
		if (file_exists($targetLanguagePath))
		{
			$errors[] = new Error(Loc::getMessage('TR_ERROR_DELETE_TARGET_FOLDER', array('%PATH%' => $targetLanguagePath)));
		}
	}

	if ($errors->isEmpty())
	{
		clearstatcache();
		CheckDirPath($targetLanguagePath."/");
		clearstatcache();
		if (!file_exists($targetLanguagePath) || !is_dir($targetLanguagePath))
		{
			$errors[] = new Error(Loc::getMessage('TR_ERROR_CREATE_TARGET_FOLDER', array('%PATH%' => $targetLanguagePath)));
		}
	}

	if ($errors->isEmpty())
	{
		if ($convertEncoding)
		{
			if ($useTranslationRepository)
			{
				$encodingIn = Main\Localization\Translation::getSourceEncoding($languageId);
				$encodingOut = $encoding;
				if ($encodingIn === 'utf-8' && $encodingOut !== 'utf-8')
				{
					$errors[] = new Error(Loc::getMessage('TR_ERROR_LANGUAGE_CHARSET_NON_UTF'));
				}
			}
			elseif ($isUtfMode)
			{
				$encodingIn = 'utf-8';
				$encodingOut = $encoding;
				if (Translate\Translation::getCultureEncoding($languageId) !== 'utf-8')
				{
					$errors[] = new Error(Loc::getMessage('TR_ERROR_LANGUAGE_CHARSET_NON_UTF'));
				}
			}
			else
			{
				$encodingIn = Translate\Translation::getCultureEncoding($languageId);
				if (!$encodingIn)
				{
					$encodingIn = Main\Localization\Translation::getCurrentEncoding();
				}
				$encodingOut = 'utf-8';
			}

			$convertEncoding = ($encodingIn !== $encodingOut);
		}
	}

	if ($errors->isEmpty())
	{
		if(
			$useTranslationRepository &&
			Main\Localization\Translation::isDefaultTranslationLang($languageId) !== true
		)
		{
			$sourceDirectory = new Translate\Directory(Main\Localization\Translation::getTranslationRepositoryPath(). '/'. $languageId);
		}
		else
		{
			$sourceDirectory = new Translate\Directory(Main\Application::getDocumentRoot(). '/bitrix/modules');
		}

		$targetDirectory = new Main\IO\Directory($targetLanguagePath);

		$res = $sourceDirectory->copyLangOnly(
			$targetDirectory,
			$languageId,
			$convertEncoding,
			$encodingIn,
			$encodingOut
		);
		if (!$res)
		{
			$errors->add($sourceDirectory->getErrors());
		}

		$fileDateMark = new Main\IO\File($targetDirectory->getPhysicalPath(). str_replace('#LANG_ID#', $languageId, Translate\SUPD_LANG_DATE_MARK));

		if ($fileDateMark->putContents($langDate) === false)
		{
			$errors[] = new Error(Loc::getMessage('TR_ERROR_OPEN_FILE', array(
				'%FILE%' => $targetLanguagePath. str_replace('#LANG_ID#', $languageId, Translate\SUPD_LANG_DATE_MARK),
			)));
		}
	}

	if ($errors->isEmpty())
	{
		if ($packFiles)
		{
			if (Translate\Archiver::libAvailable())
			{
				$fileName = 'file-'.$languageId.'.tar.gz';
			}
			else
			{
				$fileName = 'file-'.$languageId.'.tar';
			}
			$archive = new Translate\Archiver(Main\Application::getDocumentRoot(). Translate\WORKING_DIR. '/'. $fileName);

			if ($archive->isExists())
			{
				$archive->delete();
			}

			$langDir = new Translate\Directory(Main\Application::getDocumentRoot(). Translate\WORKING_DIR. '/'. $languageId);

			$res = $archive->pack($langDir);
			if (!$res)
			{
				if (count($archive->getErrors()) > 0)
				{
					$strErrorMessage = '';
					foreach ($archive->getErrors() as $err)
					{
						$strErrorMessage .= $err->getMessage(). ', ';
					}
					$errors[] = new Error(Loc::getMessage('TR_ERROR_ARCHIVE'). ': '. $strErrorMessage);
				}
			}
			else
			{
				$strOKMessage = Loc::getMessage('TR_LANGUAGE_COLLECTED_ARCHIVE', array(
					'%LANG%' => $languageId,
					'%FILE_PATH%' => $archive->getPhysicalPath(),
					'%LINK%' =>	"<a href=\"". Translate\WORKING_DIR. $archive->getName()."\">". $archive->getName(). "</a>",
				));
			}
		}
	}

	if ($errors->isEmpty() && strlen($strOKMessage) <= 0)
	{
		$strOKMessage = Loc::getMessage('TR_LANGUAGE_COLLECTED_FOLDER', array(
			'%LANG%' => $languageId,
			'%PATH%' => $targetLanguagePath
		));
	}
}
//endregion

//-----------------------------------------------------------------------------------
//region Action start_download
else if ($isPost && $request->getPost('start_download') === 'Y')
{
	@set_time_limit(0);

	if (
		!(
			array_key_exists('tarfile', $_FILES) &&
			array_key_exists('tmp_name', $_FILES['tarfile']) &&
			file_exists($_FILES['tarfile']['tmp_name']) &&
			$_FILES['tarfile']['error'] == 0
		)
	)
	{
		$errors[] = new Error(Loc::getMessage('TR_ERROR_TARFILE'));
	}

	if ($errors->isEmpty())
	{
		$tmpFileName = strtolower($_FILES['tarfile']['name']);
		if (
			substr($tmpFileName, -7) !== '.tar.gz'
			&& substr($tmpFileName, -4) !== '.tar'
		)
		{
			$errors[] = new Error(Loc::getMessage('TR_ERROR_TARFILE_EXTENTION'));
		}
		unset($tmpFileName);
	}

	if (strlen($languageId) != 2)
	{
		$errors[] = new Error(Loc::getMessage('TR_ERROR_SELECT_LANGUAGE'));
	}

	if (!in_array($languageId, $allLanguages))
	{
		$errors[] = new Error(Loc::getMessage('TR_ERROR_LANGUAGE_ID'));
	}


	$tempLanguageDir = Translate\Directory::generateTemporalDirectory('translate');

	if ($errors->isEmpty())
	{
		if (!$tempLanguageDir->isExists())
		{
			$tempLanguageDir->create();
		}
		if (!$tempLanguageDir->isExists() || !$tempLanguageDir->isDirectory())
		{
			$errors[] = new Error(Loc::getMessage('TR_ERROR_CREATE_TEMP_FOLDER', array('%PATH%' => $tempLanguageDir->getPhysicalPath())));
		}
	}

	if ($errors->isEmpty())
	{
		$archive = new Translate\Archiver($_FILES['tarfile']['tmp_name']);
		$res = $archive->extract($tempLanguageDir);
		if (!$res)
		{
			if (count($archive->getErrors()) > 0)
			{
				$strErrorMessage = '';
				foreach ($archive->getErrors() as $err)
				{
					$strErrorMessage .= $err->getMessage(). ', ';
				}
				$errors[] = new Error(Loc::getMessage('TR_ERROR_ARCHIVE'). ': '. $strErrorMessage);
			}
		}
	}

	if ($errors->isEmpty())
	{
		$convertEncoding = ($request->getPost('localize_encoding') === 'Y');
		if ($convertEncoding)
		{
			if ($useTranslationRepository)
			{
				$encodingIn = $encoding;
				$encodingOut = Main\Localization\Translation::getSourceEncoding($languageId);
			}
			elseif ($isUtfMode)
			{
				$encodingIn = $encoding;
				$encodingOut = 'utf-8';
			}
			else
			{
				$encodingIn = 'utf-8';
				$encodingOut = Translate\Translation::getCultureEncoding($languageId);
				if (!$encodingOut)
				{
					$encodingOut = Main\Localization\Translation::getCurrentEncoding();
				}
			}
			$convertEncoding = ($encodingIn !== $encodingOut);
		}

		if(
			$useTranslationRepository &&
			Main\Localization\Translation::isDefaultTranslationLang($languageId) !== true
		)
		{
			$targetDirectory = new Main\IO\Directory(Main\Localization\Translation::getTranslationRepositoryPath(). '/'. $languageId. '/');
			if (!$targetDirectory->isExists())
			{
				$targetDirectory->create();
			}
		}
		else
		{
			$targetDirectory = new Main\IO\Directory(Main\Application::getDocumentRoot(). '/bitrix/modules');
		}

		$sourceDirectory = new Translate\Directory($tempLanguageDir->getPhysicalPath(). '/'. $languageId. '/');

		$res = $sourceDirectory->copyLangOnly(
			$targetDirectory,
			$languageId,
			$convertEncoding,
			$encodingIn,
			$encodingOut
		);
		if (!$res)
		{
			$errors->add($sourceDirectory->getErrors());
		}
	}

	//delete tmp files
	$tempLanguageDir->delete();
	//clearstatcache();

	if ($errors->isEmpty())
	{
		$strOKMessage = Loc::getMessage('TR_LANGUAGE_DOWNLOADED');
	}
}
else
{
	$langDate = date('Ymd');
	$languageId = LANGUAGE_ID;
	$encoding = '';
}

//endregion
//endregion POST

//-----------------------------------------------------------------------------------
//region Start page

$APPLICATION->SetTitle(Loc::getMessage('TRANS_TITLE'));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");


if (!$errors->isEmpty())
{
	$strErrorMessage = '';
	/** @var Main\Error $err */
	foreach ($errors as $err)
	{
		$strErrorMessage .= $err->getMessage(). '<br>';
	}
	$message = new \CAdminMessage(array('MESSAGE' => $strErrorMessage, 'TYPE' => 'ERROR'));
	echo $message->Show();
}
if ($strOKMessage != '')
{
	$message = new \CAdminMessage(array('MESSAGE' => $strOKMessage, 'TYPE' => 'OK', 'HTML' => true));
	echo $message->Show();
}

//endregion

$aTabs = array(
	array(
		"DIV" => "upload",
		"TAB" => Loc::getMessage("TRANS_UPLOAD"),
		"TITLE" => Loc::getMessage("TRANS_UPLOAD_TITLE"),
		'ONSELECT' => "BX('tr_submit').value='".Loc::getMessage("TR_COLLECT_LANGUAGE")."'"
	),
	array(
		"DIV" => "download",
		"TAB" => Loc::getMessage("TRANS_DOWNLOAD"),
		"TITLE" => Loc::getMessage("TRANS_DOWNLOAD_TITLE"),
		'ONSELECT' => "BX('tr_submit').value='".Loc::getMessage("TR_DOWNLOAD_LANGUAGE")."'"
	),
);

$tabControl = new \CAdminTabControl("tabControl", $aTabs, false, true);

$tabControl->Begin();

//region Form COLLECT LANGUAGE
?>
<form method="post" action="" name="form1">
	<input type="hidden" name="start_collect" value="Y">
	<input type="hidden" name="tabControl_active_tab" value="upload">
	<?=bitrix_sessid_post()?>
	<?

	$tabControl->BeginNextTab();

	?>
	<tr class="adm-required-field">
		<td width="40%"><?= Loc::getMessage("TR_SELECT_LANGUAGE")?>:</td>
		<td width="60%">
			<select name="language_id">
				<?
				$iterator = Main\Localization\LanguageTable::getList([
					'select' => ['ID', 'NAME'],
					'filter' => [
						'ID' => array_intersect($availableLanguages, $enabledLanguages),
						'=ACTIVE' => 'Y'
					],
					'order' => ['DEF' => 'DESC', 'SORT' => 'ASC']
				]);
				while ($row = $iterator->fetch())
				{
					?><option value="<?= $row['ID'] ?>"<?=($row['ID'] == $languageId ? ' selected' : ''); ?>><?= $row['NAME'] ?> (<?= $row['ID'] ?>)</option><?
				}
				?>
			</select>
		</td>
	</tr>

	<tr class="adm-required-field">
		<td><?= Loc::getMessage("TR_COLLECT_DATE")?>:</td>
		<td><input type="text" name="lang_date" size="10" maxlength="8" value="<?= htmlspecialcharsbx($langDate) ?>"></td>
	</tr>
	<?
	if (!$isUtfMode && !$useTranslationRepository)
	{
		?>
		<tr>
			<td><?= Loc::getMessage("TR_CONVERT_UTF8")?>:</td>
			<td><input type="checkbox" name="convert_encoding" value="Y" <?= ($convertEncoding ? 'checked="checked"' : '') ?>></td>
		</tr>
		<?
	}
	else
	{
		?>
		<tr>
			<td><?echo Loc::getMessage("TR_CONVERT_NATIONAL")?>:</td>
			<td><input type="checkbox" name="convert_encoding" value="Y" <?= ($convertEncoding ? 'checked="checked"' : '') ?> onClick="EncodeClicked()"></td>
		</tr>
		<tr>
			<td width="40%"><?echo Loc::getMessage("TR_CONVERT_ENCODING")?>:</td>
			<td width="60%">
			<select name="encoding">
				<?
				foreach ($allowedEncodings as $enc)
				{
					$encTitle = Translate\Translation::getEncodingName($enc);
					?><option value="<?= htmlspecialcharsbx($enc); ?>"<?if ($enc == $encoding) echo " selected";?>><?= $encTitle ?></option><?
				}
				?>
			</select>
			<script type="text/javascript">
				function EncodeClicked()
				{
					document.form1.encoding.disabled = !document.form1.convert_encoding.checked;
				}
				BX.ready(function(){
					EncodeClicked();
				});
			</script>
			</td>
		</tr>
		<?
	}
	?>
	<tr>
		<td><?= Loc::getMessage("TR_PACK_FILES")?>:</td>
		<td><input type="checkbox" name="pack_files" value="Y" <?= ($packFiles ?  'checked="checked"' : '') ?>></td>
	</tr>
	<?

$tabControl->EndTab();

?>
</form>
<?

//endregion


//region Form UPLOAD FILE

?>
<form method="post" action="" name="form2" enctype="multipart/form-data">
	<input type="hidden" name="start_download" value="Y">
	<input type="hidden" name="tabControl_active_tab" value="download">
	<?=bitrix_sessid_post()?>
<?

$tabControl->BeginNextTab();

	?>
	<tr class="adm-required-field">
		<td width="10%" nowrap><?=Loc::getMessage("TR_UPLOAD_FILE")?>:</td>
		<td valign="top" width="90%"><input type="file" name="tarfile"></td>
	</tr>
	<tr class="adm-required-field">
		<td width="40%"><?=Loc::getMessage("TR_SELECT_LANGUAGE")?> <?=Loc::getMessage("TR_SELECT_LANGUAGE_DESCRIPTION")?>:</td>
		<td width="60%">
			<select name="language_id">
				<?
				$iterator = Main\Localization\LanguageTable::getList([
					'select' => ['ID', 'NAME'],
					'filter' => [
						'ID' => $enabledLanguages,
						'=ACTIVE' => 'Y'
					],
					'order' => ['DEF' => 'DESC', 'SORT' => 'ASC']
				]);
				while ($row = $iterator->fetch())
				{
					?><option value="<?= $row['ID'] ?>"<?=($row['ID'] == $languageId ? ' selected' : ''); ?>><?= $row['NAME'] ?> (<?= $row['ID'] ?>)</option><?
				}
				?>
			</select>
		</td>
	</tr>
	<?

	if (!$isUtfMode && !$useTranslationRepository)
	{
		?>
		<tr>
			<td><?= Loc::getMessage("TR_CONVERT_FROM_UTF8")?>:</td>
			<td><input type="checkbox" name="localize_encoding" value="Y" <?=($convertEncoding ? 'checked="checked"' : ''); ?>></td>
		</tr>
		<?
	}
	else
	{
		?>
		<tr>
			<td><?= Loc::getMessage("TR_CONVERT_FROM_NATIONAL")?>:</td>
			<td><input type="checkbox" id="localize_encoding" name="localize_encoding" value="Y" <?=($convertEncoding ? 'checked="checked"' : ''); ?>></td>
		</tr>
		<tr id="tr_encoding" style="display: <?=($convertEncoding ? 'table-row' : 'none'); ?>;">
			<td width="40%"><?= Loc::getMessage("TR_CONVERT_ENCODING")?>:</td>
			<td width="60%">
				<select name="encoding"><?
					foreach ($allowedEncodings as $enc)
					{
						$encTitle = Translate\Translation::getEncodingName($enc);

						?><option value="<?=htmlspecialcharsbx($enc); ?>"<?if ($enc == $encoding) echo " selected";?>><?= $encTitle ?></option><?
					}
				?></select>
			</td>
		</tr>
		<?
	}

$tabControl->EndTab();

?>
</form>
<?

//endregion


$tabControl->Buttons();
?>
<input type="submit" id="tr_submit" class="adm-btn-green" value="<?= ($request->get('tabControl_active_tab') === 'download' ? Loc::getMessage("TR_DOWNLOAD_LANGUAGE") : Loc::getMessage("TR_COLLECT_LANGUAGE"))?>">
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
			BX.showWait(null, "<?=Loc::getMessage('TR_COLLECT_LOADING') ?>");
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