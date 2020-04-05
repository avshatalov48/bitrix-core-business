<?php
//region HEAD
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/translate/prolog.php");

use Bitrix\Main,
	Bitrix\Main\Localization\Loc,
	Bitrix\Translate;

Loc::loadLanguageFile(__FILE__);

if (!\Bitrix\Main\Loader::includeModule('translate'))
{
	require $_SERVER['DOCUMENT_ROOT']. '/bitrix/modules/main/include/prolog_admin_after.php';

	\CAdminMessage::ShowMessage('Translate module not found');

	require $_SERVER['DOCUMENT_ROOT']. '/bitrix/modules/main/include/epilog_admin.php';
}

/** @global \CMain $APPLICATION */
$permissionRight = $APPLICATION->GetGroupRight('translate');
if ($permissionRight == Translate\Permission::DENY)
{
	$APPLICATION->AuthForm(Loc::getMessage('ACCESS_DENIED'));
}
// if it is POST upload_csv
if ($request->isPost())
{
	if ($request->getPost('upload_csv') !== null)
	{
		if (!($permissionRight >= Translate\Permission::WRITE))
		{
			\CAdminMessage::ShowMessage(Loc::getMessage('TR_TOOLS_ERROR_RIGHTS'));
			require $_SERVER['DOCUMENT_ROOT']. '/bitrix/modules/main/include/epilog_admin.php';
		}
	}
}

$hasPermissionEditPhp = $USER->CanDoOperation('edit_php');



//endregion

//-----------------------------------------------------------------------------------
//region handle GET,POST

@set_time_limit(0);


$request = Main\Context::getCurrent()->getRequest();

$isUtfMode = Translate\Translation::isUtfMode();
$useTranslationRepository = Main\Localization\Translation::useTranslationRepository();
$enabledLanguages = Translate\Translation::getEnabledLanguages();
$allowedEncodings = Translate\Translation::getAllowedEncodings();

$arCSVMessage = false;
$useSearch = false;
$arSearchParam = [];
if ($request->isPost() && check_bitrix_sessid())
{
	//region POST upload_csv
	if ($request->getPost('upload_csv') !== null)
	{
		if (
			isset($_FILES['csvfile']) &&
			isset($_FILES['csvfile']['tmp_name']) &&
			file_exists($_FILES['csvfile']['tmp_name'])
		)
		{
			$rewriteMode = ($request->getPost('rewrite_lang_files') === 'Y');
			$mergeMode = true;
			if (!$rewriteMode)
			{
				$mergeMode = ($request->getPost('rewrite_lang_files') === 'U');
			}

			$encodingIn = '';
			$convertEncoding = ($request->getPost('localize_encoding') === 'Y');
			if ($convertEncoding)
			{
				$encodingIn = ($request->getPost('encoding') !== null ? $request->getPost('encoding') : '');
			}

			$errors = [];

			if (SaveTCSVFile($_FILES['csvfile']['tmp_name'], $encodingIn, $rewriteMode, $mergeMode, $errors))
			{
				$arCSVMessage = array('TYPE' => 'OK', 'MESSAGE' => Loc::getMessage('TR_CSV_UPLOAD_OK'));
			}
			else
			{
				$arCSVMessage = array('TYPE' => 'ERROR', 'MESSAGE' => implode('<br>', $errors));
			}

		}
		else
		{
			$arCSVMessage = array('TYPE' => 'ERROR', 'MESSAGE' => Loc::getMessage('TR_TOOLS_ERROR_EMPTY_FILE'));
		}


	}
	//endregion

	//region POST tr_search
	elseif ($request->get('tr_search') !== null)
	{
		if ($request->get('replace_oper') === 'Y' && $hasPermissionEditPhp)
		{
			$arSearchParam = [
				'is_replace' => true,
				'language' => trim((string)$request->get('search_language2')),
				'search' => trim((string)$request->get('search_phrase2')),
				'replace' => trim((string)$request->get('replace_phrase2')),
				'bSubFolders' => ($request->get('search_subfolders2') === 'Y'),
				'bSearchMessage' => ($request->get('search_message2') === 'Y'),
				'bSearchMnemonic' => ($request->get('search_mnemonic2') === 'Y'),
				'bCaseSens' => ($request->get('search_case_sens2') === 'Y')
			];
		}
		else
		{
			$arSearchParam = [
				'is_replace' => false,
				'language' => trim((string)$request->get('search_language')),
				'search' => trim((string)$request->get('search_phrase')),
				'replace' => '',
				'bSubFolders' => ($request->get('search_subfolders') === 'Y'),
				'bSearchMessage' => ($request->get('search_message') === 'Y'),
				'bSearchMnemonic' => ($request->get('search_mnemonic') === 'Y'),
				'bCaseSens' => ($request->get('search_case_sens') === 'Y')
			];
		}
		if ($arSearchParam['search'] !== '')
		{
			$useSearch = true;
		}
		else
		{
			$arSearchParam = [];
		}
	}
	//endregion
}

//endregion



$languages = $request->get('languages');
if ($languages !== null && is_array($languages) && !empty($languages))
{
	$languages = array_intersect($languages, $enabledLanguages);
}
else
{
	unset($languages);
}



$defaultLanguageList = [];
$iterator = Main\Localization\LanguageTable::getList([
	'select' => ['ID', 'NAME'],
	'filter' => [
		[
			'LOGIC' => 'OR',
			'@ID' => Translate\Translation::getDefaultLanguages(),
			'=DEF' => 'Y'
		],
		'=ACTIVE' => 'Y'
	],
	'order' => ['DEF' => 'DESC', 'SORT' => 'ASC']
]);
while ($row = $iterator->fetch())
{
	$defaultLanguageList[$row['ID']] = $row['NAME'];
}
unset($row, $iterator);

$path = $request->get('path');
$otherPath = $request->get('go_path');

// button going
if (strlen($otherPath) > 0 && !preg_match("#\.\.[\\/]#".BX_UTF_PCRE_MODIFIER, $path))
{
	$path = Translate\Path::addLangId($otherPath, reset($enabledLanguages), $enabledLanguages);
}

if (preg_match("#\.\.[\\/]#".BX_UTF_PCRE_MODIFIER, $path))
{
	$path = '';
}

// no path
if (strlen($path) <= 0)
{
	$path = Translate\TRANSLATE_DEFAULT_PATH;
}

$path = Rel2Abs("/", "/".$path."/");
if (!Translate\Permission::isAllowPath($path))
{
	$path = Translate\TRANSLATE_DEFAULT_PATH;
}

$showDifference = false;
$autoCalculateDifference = (string)Main\Config\Option::get('translate', 'AUTO_CALCULATE') === 'Y';
if (!$autoCalculateDifference)
{
	if ($request->get('SHOW_DIFF') !== null)
	{
		$showDifference = ($request->get('SHOW_DIFF') === 'Y');
		if ($path == Translate\TRANSLATE_DEFAULT_PATH)
		{
			$showDifference = false;
		}
		$_SESSION['BX_SHOW_LANG_DIFF'] = $showDifference;
		if ($showDifference)
		{
			$_SESSION['BX_SHOW_LANG_DIFF_PATH'] = $path;
		}
		else
		{
			if (array_key_exists('BX_SHOW_LANG_DIFF_PATH', $_SESSION))
			{
				unset($_SESSION['BX_SHOW_LANG_DIFF_PATH']);
			}
		}
	}
}
if (isset($_SESSION['BX_SHOW_LANG_DIFF']) && $_SESSION['BX_SHOW_LANG_DIFF'])
{
	if (substr($path, 0, strlen($_SESSION['BX_SHOW_LANG_DIFF_PATH'])) !== $_SESSION['BX_SHOW_LANG_DIFF_PATH'])
	{
		$_SESSION['BX_SHOW_LANG_DIFF'] = false;
		unset($_SESSION['BX_SHOW_LANG_DIFF_PATH']);
	}
}

$showTranslationDifferences = $autoCalculateDifference || $showDifference || (isset($_SESSION['BX_SHOW_LANG_DIFF']) && $_SESSION['BX_SHOW_LANG_DIFF']);
$checkSubfolders = $showTranslationDifferences || ($useSearch && $arSearchParam['bSubFolders']);
if ($useSearch)
{
	$autoCalculateDifference = false;
	$showTranslationDifferences = false;
}
$arLangCounters = array();
$arCommonCounter = array();

$arLangDirFiles = array();
$arFiles = array();
$arDirs = array();
$arLangDirs = array();


$isLangDir = Translate\Path::isLangDir($path);
//no lang
if ($isLangDir)
{
	foreach ($enabledLanguages as $langId)
	{
		$ph = Translate\Path::addLangId($path, $langId, $enabledLanguages);
		if (strlen($ph) > 0)
		{
			GetTDirList($ph, $checkSubfolders);
		}
		$ph = '';
	}
}
else
{
	if ($useSearch)
	{
		GetTDirList($path, $checkSubfolders, [$arSearchParam['language']]);
	}
	else
	{
		GetTDirList($path, $checkSubfolders, $enabledLanguages);
	}
}


$showOnlyUntranslated = Main\Config\Option::get('translate', 'ONLY_ERRORS');
$showOnlyUntranslated = ($showOnlyUntranslated === 'Y') ? 'Y' : '';

GetLangDirs($arDirs, $showTranslationDifferences);

$arLangDirFiles = array_merge($arLangDirs, $arFiles);

// find
if ($useSearch)
{
	$_arLangDirFiles = $arLangDirFiles;
	$arLangDirFiles = array();
	foreach ($_arLangDirFiles as $_v)
	{
		if ($_v['IS_DIR'] == 'Y')
		{
			continue;
		}
		if ($_v['LANG'] != $arSearchParam['language'])
		{
			continue;
		}

		$_coincidence = 0;
		if (!TSEARCH($_v, $_coincidence))
		{
			continue;
		}

		$_v['COINCIDENCE'] = $_coincidence;
		$arLangDirFiles[$_v['PATH']] = $_v;
	}
}
//endregion

//-----------------------------------------------------------------------------------
//region HTML Grid

$sTableID = 'tbl_translate_list';
$lAdmin = new \CAdminList($sTableID);

$listIds = $lAdmin->GroupAction();
if (!empty($listIds))
{
	$action = $request->get('action');
	$actionButton = $request->get('action_button');
	if (!empty($actionButton))
	{
		$action = $actionButton;
	}
	unset($actionButton);

	switch ($action)
	{
		case 'remove_phrase':
			$masterLanguage = (string)$request->get('remove_phrase_lang');
			if ($masterLanguage === '' || !isset($defaultLanguageList[$masterLanguage]))
			{
				$lAdmin->AddGroupError(Loc::getMessage('BX_TRANSLATE_LIST_GROUP_ERR_LANGUAGE_ABSENT'));
				break;
			}

			$errorCollection = [];

			if (!removePhrasesByMasterFile($masterLanguage, $listIds, $errorCollection))
			{
				foreach ($errorCollection as $errMessage)
				{
					$lAdmin->AddGroupError($errMessage);
				}
			}
			break;
	}
}
//endregion

//-----------------------------------------------------------------------------------
//region HTML Grid

$lAdmin->BeginPrologContent();

if (!$useSearch && !empty($arrChain))
{
	?><p><?
	$last_path = '';
	foreach ($arrChain as $row)
	{
		echo ' / ';
		if ($row['PATH'] !== '')
		{
			$last_path = $row['PATH'];
			?><a href="?lang=<?= LANGUAGE_ID ?>&path=<?=urlencode($last_path); ?>" title="<?=Loc::getMessage('TR_FOLDER_TITLE'); ?>"><?=htmlspecialcharsbx($row['NAME']); ?></a><?
		}
		else
		{
			?><?=htmlspecialcharsbx($row['NAME']); ?><?
		}
	}
	?></p><?
}

$lAdmin->EndPrologContent();

$header = array();
$header[] = array(
	'id' => "TRANS_FILE_NAME",
	'content' => Loc::getMessage("TRANS_FILE_NAME"),
	'default' => true,
	'align' => "left"
);
if ($autoCalculateDifference || $showTranslationDifferences)
{
	$header[] = array(
		'id' => "TRANS_TOTAL_MESSAGES",
		'content' => Loc::getMessage("TRANS_TOTAL_MESSAGES"),
		'default' => true,
		'align' => "right"
	);

	foreach($enabledLanguages as $langId)
	{
		$header[] = array(
			'id' => $langId,
			'content' => $langId,
			'default' => true,
			'align' => "left"
		);
	}
}
$lAdmin->AddHeaders($header);

if (strlen($path) > 0 && !$useSearch)
{
	$row =& $lAdmin->AddRow('.', []);
	$row->AddViewField('TRANS_FILE_NAME',
		'<a href="?lang='.LANGUAGE_ID.'&path='.urlencode($last_path).'" title="'.Loc::getMessage("TR_UP_TITLE").'">'.
		'<img src="/bitrix/images/translate/up.gif" width="11" height="13" border=0 alt=""></a>&nbsp;'.
		'<a href="?lang='.LANGUAGE_ID.'&path='.urlencode($last_path).'" title="'.Loc::getMessage("TR_UP_TITLE").'">..</a>'
	);
	$row->bReadOnly = true;
	if ($autoCalculateDifference || $showTranslationDifferences)
	{
		$row->AddViewField('TRANS_TOTAL_MESSAGES', '&nbsp;');
		foreach($enabledLanguages as $langId)
		{
			$row->AddViewField($langId, '&nbsp;');
		}
	}
}


$ORIGINAL_MESS = $MESS;

$showGroupActions = false;

if (is_array($arLangDirFiles))
{
	if ($isLangDir)
	{
		$arPath[] = Translate\Path::addLangId($path, LANGUAGE_ID, $enabledLanguages);
	}
	else
	{
		$arPath[] = $path;
	}


	$arShown = array();
	$messagesUntranslated = array();
	$messagesTotal = 0;

	foreach($arLangDirFiles as $key => $entry)
	{
		if (in_array($entry['PARENT'], $arPath) || $useSearch)
		{
			if ($useSearch && $entry['IS_DIR'] == 'Y')
			{
				continue;
			}

			$ftitle = $useSearch ? $entry["PATH"] : $entry["FILE"];
			if ($isLangDir)
			{
				if (in_array($ftitle, $arShown))
				{
					continue;
				}
				$arShown[] = $ftitle;
			}

			$fpath = $entry["PATH"];
			$fparent = $entry["PARENT"];
			$fkey = Translate\Path::removeLangId($fpath, $enabledLanguages);

			if ($showTranslationDifferences)
			{
				GetPhraseCounters($arLangDirFiles, $entry, $enabledLanguages);
			}

			if ($entry['IS_DIR'] === 'Y')
			{
				$row =& $lAdmin->AddRow($entry['FILE'], [], "translate_list.php?lang=".LANGUAGE_ID."&path=".$fpath, Loc::getMessage("TR_FOLDER_TITLE"));
				$row->AddViewField(
					"TRANS_FILE_NAME",
					'<a href="?lang='.LANGUAGE_ID.'&path='.$fpath.'" title="'.Loc::getMessage("TR_FOLDER_TITLE").'">'.
						'<img src="/bitrix/images/translate/folder.gif" width="16" height="16" border=0 alt=""></a>&nbsp;'.
					'<a href="?lang='.LANGUAGE_ID.'&path='.$fpath.'" title="'.Loc::getMessage("TR_FOLDER_TITLE").'">'.$ftitle.'</a>'
				);
				$row->bReadOnly = true;
			}
			else
			{
				$showGroupActions = true;
				$row =& $lAdmin->AddRow($entry['FILE'], [], "translate_edit.php?lang=".LANGUAGE_ID."&file=".$fpath."&show_error=".$showOnlyUntranslated, Loc::getMessage("TR_FILE_TITLE"));
				$arAction = [
					[
						'TEXT' => Loc::getMessage("TR_MESSAGE_EDIT"),
						'ACTION' => $lAdmin->ActionRedirect('translate_edit.php?lang='.LANGUAGE_ID.'&file='.$fpath.'&show_error='.$showOnlyUntranslated),
						'DEFAULT' => true,
						'ICON' => ''
					],
					[
						'TEXT' => Loc::getMessage("TR_FILE_EDIT"),
						'ACTION' => $lAdmin->ActionRedirect('translate_edit_php.php?lang='.LANGUAGE_ID.'&file='.$fpath),
						'DEFAULT' => false,
						'ICON' => 'edit'
					],
					[
						'TEXT' => Loc::getMessage("TR_FILE_SHOW"),
						'ACTION' => $lAdmin->ActionRedirect('translate_show_php.php?lang='.LANGUAGE_ID.'&file='.$fpath),
						'DEFAULT' => false,
						'ICON' => 'view'
					]
				];
				if ($useSearch)
				{
					$arAction[] = ['SEPARATOR' => true];
					$arAction[] = [
						'TEXT' => Loc::getMessage("TR_PATH_GO"),
						'ACTION' => $lAdmin->ActionRedirect('translate_list.php?lang='.LANGUAGE_ID.'&path='.$fparent),
						'DEFAULT' => false,
						'ICON' => 'go'
					];
				}
				$row->AddActions($arAction);
				$row->AddViewField(
					"TRANS_FILE_NAME",
					'<a href="translate_edit.php?lang='.LANGUAGE_ID.'&file='.$fpath.'&show_error='.$showOnlyUntranslated.'" title="'.Loc::getMessage("TR_FILE_TITLE").'">'.
							'<img src="/bitrix/images/translate/file.gif" width="16" height="16" border=0 alt=""></a>&nbsp;'.
						'<a href="translate_edit.php?lang='.LANGUAGE_ID.'&file='.$fpath.'&show_error='.$showOnlyUntranslated.'" title="'.Loc::getMessage("TR_FILE_TITLE").'">'.
							$ftitle.'</a>'.($useSearch ? ' ('.$entry["COINCIDENCE"].')' : '')
				);
			}
			if ($autoCalculateDifference || $showTranslationDifferences)
			{
				$arr = array();
				foreach($enabledLanguages as $langId)
				{
					$total_sum = 0;
					if(is_array($arCommonCounter[$fkey][$langId]))
					{
						foreach ($arCommonCounter[$fkey][$langId] as $fileName => $fileCounter)
						{
							$total_sum += intval($fileCounter["TOTAL"]);
						}
					}

					$arr[] = $total_sum;
				}

				$messagesCount = max($arr);
				$messagesTotal += $messagesCount;
				$row->AddViewField("TRANS_TOTAL_MESSAGES", $messagesCount);

				foreach ($enabledLanguages as $langId)
				{
					$arFilesDiff = array();
					$arFilesTotal = array();
					$lang_not_translated = 0;
					$lang_total = 0;
					if(is_array($arCommonCounter[$fkey][$langId]))
					{
						foreach ($arCommonCounter[$fkey][$langId] as $file => $fileCounter)
						{
							if (intval($fileCounter["DIFF"]) > 0)
							{
								$arFilesDiff[$file] = intval($fileCounter["DIFF"]);
							}
							if (intval($fileCounter["TOTAL"]) > 0)
							{
								$arFilesTotal[$file] = intval($fileCounter["TOTAL"]);
							}
							$lang_not_translated += intval($fileCounter["DIFF"]);
							$lang_total += intval($fileCounter["TOTAL"]);
						}
					}
					$diff_total = $messagesCount - $lang_total;
					if (intval($lang_not_translated) > 0)
					{
						foreach ($arFilesDiff as $fileName => $counter)
						{
							$arFilesDiff[$fileName] = '<a href="translate_edit.php?lang='.LANGUAGE_ID.'&file='.urlencode($fileName).'&show_error=Y" title="'.$fileName.'">'.$counter.'</a>';
						}
						$sStr = '<span class="required">'.$lang_not_translated.'</span>: '.implode(', ', $arFilesDiff);
						$messagesUntranslated[$langId] += $lang_not_translated;
						$row->AddViewField($langId, $sStr);
					}
					elseif (intval($diff_total) > 0)
					{
						$sStr = '<span class="required">'.$lang_total.'</span>: '.implode(', ', $arFilesTotal);
						$messagesUntranslated[$langId] += $diff_total;
						$row->AddViewField($langId, $sStr);
					}
					else
					{
						$row->AddViewField($langId, "&nbsp;");
					}
				}
			}
		}
	}
	unset($row);
}

$MESS = $ORIGINAL_MESS;
if ($autoCalculateDifference || $showTranslationDifferences)
{
	$row =& $lAdmin->AddRow('0', Array());
	$row->AddViewField("TRANS_FILE_NAME", "<b>".Loc::getMessage("TRANS_TOTAL").":</b>");
	$row->AddViewField("TRANS_TOTAL_MESSAGES", "<b>".$messagesTotal."</b>");
	foreach($enabledLanguages as $langId)
	{
		if (intval($messagesUntranslated[$langId]) > 0)
		{
			$row->AddViewField($langId, "<b>".$messagesUntranslated[$langId]."</b>");
		}
	}
	unset($row);
}

if ($showGroupActions)
{
	$actionList = [];
	$actionListParams = [];

	if (!empty($defaultLanguageList))
	{
		$chooser =
			'<div id="remove_phrase_lang_choose" style="display:none;">&nbsp;'.Loc::getMessage('BX_TRANSLATE_REMOVE_LANG_PHRASES').
				'<select name="remove_phrase_lang">'.
					'<option value="">'.Loc::getMessage('BX_TRANSLATE_PHRASE_LANG_EMPTY').'</option>';

		foreach ($defaultLanguageList as $languageId => $languageName)
		{
			$chooser .= '<option value="'.htmlspecialcharsbx($languageId).'">'.htmlspecialcharsbx($languageName).'</option>';
		}
		unset($languageId, $languageName);

		$chooser .= '</select></div>';

		$actionList['remove_phrase'] = Loc::getMessage('BX_TRANSLATE_GROUP_ACTION_REMOVE_LANG_PHRASES');
		$actionList['remove_phrase_chooser'] = [
			'type' => 'html',
			'value' => $chooser
		];

		$actionListParams['select_onchange'] = "BX('remove_phrase_lang_choose').style.display = (this.value === 'remove_phrase' ? 'block' : 'none');";
	}
	$actionListParams['disable_action_target'] = true;

	if (!empty($actionList))
		$lAdmin->AddGroupActionTable($actionList, $actionListParams);
	unset($actionListParams, $actionList);
}

$lAdmin->BeginEpilogContent();
?>
	<input type="hidden" name="go_path" id="go_path" value="">
<?
$lAdmin->EndEpilogContent();

//endregion

//-----------------------------------------------------------------------------------
//region HTML Grid

$aContext = array();
ob_start();
?>
<form action="<?=$APPLICATION->GetCurPage()?>">
	<input type="hidden" name="lang" value="<?=LANGUAGE_ID?>">
	<table cellspacing="0">
	<tr>
		<td style="padding-left:5px;"><?=Loc::getMessage("TRANS_PATH")?></td>
		<td style="padding-left:5px;"><input class="form-text" type="text" name="path" id="path_to" size="50" value="<?=htmlspecialcharsbx($path)?>"></td>
		<td style="padding-left:3px; padding-right:3px;"><input type="submit" value="<?=Loc::getMessage("TRANS_GO")?>" class="form-button"></td>
	</tr>
	</table>
</form>
<?
$s = ob_get_contents();
ob_end_clean();
$aContext[] = array("HTML"=>$s);
// Search / replace
$sFormValues = 1;
$url = $APPLICATION->GetPopupLink(
					array(
						'URL' => "/bitrix/admin/translate_search.php?lang=".LANGUAGE_ID."&bxpublic=Y&path=".urlencode($path),
								'PARAMS' => array(
									'width' => 470,
									'height' => 310,
									'resizable' => false
								)
					)
				);
$aContext[] = array(
	"TEXT" => $useSearch ? Loc::getMessage("TR_NEW_SEARCH") : Loc::getMessage("TR_SEARCH"),
	"ICON" => "btn_fileman_search",
	"LINK" => 'javascript:'.$url,
	"TITLE" => Loc::getMessage("TR_SEARCH_TITLE")
);

if (!$autoCalculateDifference || $isLangDir)
{
	$aContext[] = array('NEWBAR' => true);
}
if (!$autoCalculateDifference)
{
	if ($showTranslationDifferences)
	{
		$aContext[] = array(
				"TEXT"	=> Loc::getMessage('TR_NO_SHOW_DIFF_TEXT'),
				"LINK"	=> $APPLICATION->GetCurPageParam('SHOW_DIFF=N&path='.urlencode($path), array('SHOW_DIFF', 'mode', 'path')),
				"TITLE"	=> Loc::getMessage('TR_NO_SHOW_DIFF_TITLE'),
			);
	}
	else
	{
		$aContext[] = array(
				"TEXT"	=> Loc::getMessage('TR_SHOW_DIFF_TEXT'),
				"LINK"	=> $APPLICATION->GetCurPageParam('SHOW_DIFF=Y&path=' . urlencode($path), array('SHOW_DIFF', 'mode', 'path')),
				"TITLE"	=> Loc::getMessage('TR_SHOW_DIFF_TITLE'),
				"ICON" => "btn_green"
			);
	}
}

if ($isLangDir)
{
	$aContext[] = array(
			"TEXT"	=> Loc::getMessage('TR_CHECK_FILES_TEXT'),
			"LINK"	=> "translate_check_files.php?lang=".LANGUAGE_ID."&path=" . htmlspecialcharsbx($path),
			"TITLE"	=> Loc::getMessage('TR_CHECK_FILES_TITLE'),
		);
}

$lAdmin->AddAdminContextMenu($aContext, false, false);

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(Loc::getMessage("TRANS_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");


if ($arCSVMessage)
{
	\CAdminMessage::ShowMessage($arCSVMessage);
}

$lAdmin->DisplayList();


$aTabs = array(
	array("DIV" => "fileupl1", "TAB" => Loc::getMessage("TR_FILEUPLFORM_TAB1"),
		  "TITLE" => Loc::getMessage("TR_UPLOAD_FILE"), 'ONSELECT' => "BX('tr_submit').value='".Loc::getMessage("TR_UPLOAD_SUBMIT_BUTTON")."'"),
	array("DIV" => "filedown2", "TAB" => Loc::getMessage("TR_FILEDOWNFORM_TAB2"),
		  "TITLE" => Loc::getMessage("TR_DOWNLOAD_CSV_TEXT"), 'ONSELECT' => "BX('tr_submit').value='".Loc::getMessage("TR_DOWNLOAD_SUBMIT_BUTTON")."'")
);

$tabControl = new \CAdminTabControl("tabControl", $aTabs, false);

if ($permissionRight == Translate\Permission::WRITE)
{
	?>
	<br>
	<?
	$tabControl->Begin();

	//region Form upload csv file

	?>

	<form action="<?=$APPLICATION->GetCurPageParam('go_path='.htmlspecialcharsbx($path), array('go_path'))?>" name="form3" method="post" enctype="multipart/form-data">
		<?=bitrix_sessid_post()?>
		<input type="hidden" name="lang" value="<?=LANGUAGE_ID;?>" >
		<input type="hidden" name="upload_csv" value="1" >

	<?$tabControl->BeginNextTab();?>

		<tr>
			<td width="20%" nowrap><?=Loc::getMessage("TR_UPLOAD_CSV_FILE")?></td>
			<td valign="top"><input type="file" name="csvfile"></td>
		</tr>
		<tr>
			<td valign="top" nowrap><?=Loc::getMessage('TR_FILE_ACTIONS')?></td>
			<td valign="top">
				<input id="F_ACTION_1" type="radio" name="rewrite_lang_files" value="N" checked><label for="F_ACTION_1"><?=Loc::getMessage('TR_NO_REWRITE_LANG_FILES')?></label><br>
				<input id="F_ACTION_3" type="radio" name="rewrite_lang_files" value="U"><label for="F_ACTION_3"><?=Loc::getMessage('TR_UPDATE_LANG_FILES')?></label><br>
				<input id="F_ACTION_2" type="radio" name="rewrite_lang_files" value="Y"><label for="F_ACTION_2"><?=Loc::getMessage('TR_REWRITE_LANG_FILES')?></label>
			</td>
		</tr>
		<?
		if (!$isUtfMode && !$useTranslationRepository)
		{
			?>
			<tr>
				<td><?= Loc::getMessage("TR_CONVERT_FROM_UTF8")?>:</td>
				<td>
					<input type="checkbox" name="encoding" value="utf-8">
				</td>
			</tr>
			<?
		}
		else
		{
			?>
			<tr>
				<td width="40%"><?= Loc::getMessage("TR_CONVERT_ENCODING")?>:</td>
				<td width="60%">
					<select name="encoding">
						<option value=""></option>
						<?
						foreach ($allowedEncodings as $enc)
						{
							$encTitle = Translate\Translation::getEncodingName($enc);

							$isSelected = false;
							if ($enc == $encodingIn)
							{
								$isSelected = true;
							}
							elseif ($encodingIn == '' && ($isUtfMode || $useTranslationRepository))
							{
								$isSelected = ($enc == 'utf-8');
							}

							?><option value="<?= htmlspecialcharsbx($enc); ?>"<?if ($isSelected) echo " selected";?>><?= $encTitle ?></option><?
						}
						?>
					</select>
				</td>
			</tr>
			<?
		}

		?>

	<?$tabControl->EndTab();?>
	</form>
	<?

	//endregion

	//region Form download csv file

	?>
	<form action="translate_csv_download.php" name="form4" method="post">
		<?=bitrix_sessid_post()?>
		<input type="hidden" name="lang" value="<?=LANGUAGE_ID;?>" >
		<input type="hidden" name="path" value="<?=htmlspecialcharsbx($path);?>" >

	<?$tabControl->BeginNextTab();?>

		<tr>
			<td width="20%" valign="top" nowrap><?=Loc::getMessage('TR_FILE_ACTIONS')?></td>
			<td valign="top">
				<input id="F_ACTION_2_1" type="radio" name="download_translate_lang" value="A" checked><label for="F_ACTION_2_1"><?=Loc::getMessage('TR_DOWNLOAD_LANG')?></label><br>
				<input id="F_ACTION_2_2" type="radio" name="download_translate_lang" value="N"><label for="F_ACTION_2_2"><?=Loc::getMessage('TR_DOWNLOAD_NO_TRANSLATE')?></label>
			</td>
		</tr>

		<?if(file_exists(Main\Application::getDocumentRoot(). Translate\COLLECT_CUSTOM_LIST)):?>
			<tr>
				<td valign="top" nowrap>Only <?= Translate\COLLECT_CUSTOM_LIST ?> files:</td>
				<td valign="top">
					<input type="checkbox" name="use_custom_list" value="Y">
				</td>
			</tr>
		<?endif?>


		<tr <?/*if (!$isUtfMode && !$useTranslationRepository):?>style="display: none;"<?endif*/?>>
			<td><?= Loc::getMessage("TR_CONVERT_NATIONAL2_UTF8")?>:</td>
			<td>
				<input type="checkbox" name="convert_encoding" value="Y" checked="checked">
			</td>
		</tr>
		<tr>
			<td><?= Loc::getMessage("TR_SELECT_LANGUAGE")?>:</td>
			<td>
				<select name="languages[]" multiple="multiple" size="<?= (count($enabledLanguages) <= 7 ? count($enabledLanguages) : 7) ?>">
					<option value="" <?= (!isset($languages) || empty($languages) ? ' selected="selected"' : '') ?>><?= Loc::getMessage('TR_SELECT_LANGUAGE_ALL') ?></option>
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
						$isSelected = isset($languages) && in_array($row['ID'], $languages);
						?><option value="<?= $row['ID'] ?>" <?= ($isSelected ? ' selected=""' : '') ?>><?= $row['NAME'] ?> (<?= $row['ID'] ?>)</option><?
					}
					?>
				</select>
			</td>
		</tr>

	<?$tabControl->EndTab();?>
	</form>
	<?

	//endregion

	//region Form buttons

	$tabControl->Buttons();
	?>
	<input type="submit" id="tr_submit" value="<?=Loc::getMessage("TR_UPLOAD_SUBMIT_BUTTON")?>" class="adm-btn-save">
	<script type="text/javascript">
	BX.bind(BX('F_ACTION_2'), 'click', function(){
		if (!confirm('<?= GetMessageJS('CONFRIM_REWRITE_LANG_FILES'); ?>'))
		{
			BX('F_ACTION_2').checked = false;
			BX('F_ACTION_1').checked = true;
		}
	});
	BX.bind(BX('tr_submit'), 'click', function ()
	{
		if (BX('tabControl_active_tab').value === 'fileupl1') {
			document.forms['form3'].submit();
		} else {
			document.forms['form4'].submit();
		}
	});
	</script>
	<?
	$tabControl->End();

	//endregion
}
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");