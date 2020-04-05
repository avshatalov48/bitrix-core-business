<?php
//region Head
require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/translate/prolog.php';

use Bitrix\Main\Localization\Loc;
use Bitrix\Translate;
use Bitrix\Main;

Loc::loadLanguageFile(__FILE__);

if (!\Bitrix\Main\Loader::includeModule('translate'))
{
	require $_SERVER['DOCUMENT_ROOT']. '/bitrix/modules/main/include/prolog_admin_after.php';

	\CAdminMessage::ShowMessage('Translate module not found');

	require $_SERVER['DOCUMENT_ROOT']. '/bitrix/modules/main/include/epilog_admin.php';
}

$permissionRight = $APPLICATION->GetGroupRight('translate');
if($permissionRight == Translate\Permission::DENY)
{
	$APPLICATION->AuthForm(GetMessage('ACCESS_DENIED'));
}
if (!check_bitrix_sessid())
{
	require $_SERVER['DOCUMENT_ROOT']. '/bitrix/modules/main/include/prolog_admin_after.php';

	\CAdminMessage::ShowMessage(Loc::getMessage('main_include_decode_pass_sess'));

	require $_SERVER['DOCUMENT_ROOT']. '/bitrix/modules/main/include/epilog_admin.php';
	die();
}

//endregion

//-----------------------------------------------------------------------------------
//region handle GET,POST

$request = Main\Context::getCurrent()->getRequest();

$enabledLanguages = Translate\Translation::getEnabledLanguages();

$isUtfMode = Translate\Translation::isUtfMode();

$languages = $request->get('languages');
if ($languages !== null && is_array($languages) && !empty($languages))
{
	$languages = array_intersect($languages, $enabledLanguages);
}
if (empty($languages))
{
	$languages = $enabledLanguages;
}

//$allowedEncodings = Translate\Translation::getAllowedEncodings();

$encodingOut = '';
$convertEncoding = ($request->get('convert_encoding') === 'Y');
if ($convertEncoding)// || ($isUtfMode && !Main\Localization\Translation::useTranslationRepository()))
{
	$encodingOut = 'utf-8';
}


$filterByExistence = ($request->get('download_translate_lang') === 'N');

$path = $request->get('path');
if(preg_match("#\.\.[\\/]#".BX_UTF_PCRE_MODIFIER, $path))
{
	$path = '';
}

$path = Rel2Abs('/', '/'.$path.'/');

if (Translate\Path::isLangDir($path))
{
	foreach ($languages as $langId)
	{
		$ph = Translate\Path::addLangId($path, $langId, $languages);
		if (strlen($ph)>0)
		{
			GetTDirList($ph, true);
		}
		$ph = '';
	}
}
else
{
	GetTDirList($path, true, $languages);
}

$strFile = '';
$arFileFilter = array();
if ($request->get('file') !== null)
{
	$strFile = strval($request->get('file'));
}
if(preg_match("#\.\.[\\/]#".BX_UTF_PCRE_MODIFIER, $strFile))
{
	$strFile = "";
}
if ('' != $strFile)
{
	$strFile = Rel2Abs('/', '/'.$strFile);

	foreach ($languages as $langId)
	{
		$ph = Translate\Path::addLangId($strFile, $langId, $languages);
		if ('' != $ph)
		{
			$arFileFilter[] = $ph;
		}
	}
}

if (!empty($arFileFilter) && !empty($arFiles))
{
	$arTemp = array();
	foreach ($arFiles as &$arOneFile)
	{
		if ('N' == $arOneFile['IS_DIR'] && in_array($arOneFile['PATH'], $arFileFilter))
		{
			$arTemp[] = $arOneFile;
		}
	}
	if (isset($arOneFile))
	{
		unset($arOneFile);
	}
	$arFiles = $arTemp;
}

$customScriptsFile = Main\Application::getDocumentRoot(). '/'. Translate\COLLECT_CUSTOM_LIST;
if (($request->get('use_custom_list') === 'Y') && file_exists($customScriptsFile))
{
	$customScriptsList = array();
	$customScriptsListTemp = file($customScriptsFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
	foreach ($customScriptsListTemp as $pathScript)
	{
		$customScriptsList[Translate\Path::replaceLangId($pathScript, '#LANG_ID#')] = true;
	}

	$arTemp = array();
	foreach ($arFiles as $f)
	{
		$fname = Translate\Path::replaceLangId($f['PATH'], '#LANG_ID#');
		if ($customScriptsList[$fname])
		{
			$arTemp[] = $f;
		}
	}
	$arFiles = $arTemp;
}

/** @var Translate\CsvFile $csvFile */
$csvFile = Translate\CsvFile::generateTemporalFile('translate', '.csv', .5);
$csvFile
	->setFieldDelimiter(Translate\CsvFile::DELIMITER_TZP)
	->setRowDelimiter(Translate\CsvFile::LINE_DELIMITER_WIN)
	->prefaceWithUtf8Bom($encodingOut === 'utf-8')
	->openWrite();


$row = array('file', 'key');
foreach ($languages as $langId)
{
	$row[] = $langId;
}
$csvFile->put($row);

$arProcessed = array();

foreach ($arFiles as $fileParam)
{
	$keyIndex = Translate\Path::replaceLangId($fileParam['PATH'], '#LANG_ID#');
	if (isset($arProcessed[$keyIndex]))
	{
		continue;
	}
	$arrCSV = GetTCSVArray($keyIndex, $encodingOut, $languages);

	/** @var array $arTranslations */
	foreach ($arrCSV as $file => $arTranslations)
	{
		foreach ($arTranslations as $key => $arLangTexts)
		{
			$row = array($file, $key);
			$hasNoTranslate = false;
			foreach ($languages as $langId)
			{
				$row[] = $arLangTexts[$langId];
				if (empty($arLangTexts[$langId]))
				{
					$hasNoTranslate = true;
				}
			}

			if (!$filterByExistence || ($filterByExistence && $hasNoTranslate))
			{
				$csvFile->put($row);
			}
		}
	}

	$arProcessed[$keyIndex] = true;
}

$csvFile->close();

$csvFileName = trim(str_replace('/', '_', $path), '_'). '_'. implode('_', $languages) .'.csv';

ob_get_clean();

if(\CModule::IncludeModule('compression'))
{
	\CCompress::Disable2048Spaces();
}
/*
header('Content-Type: text/csv; charset='.LANG_CHARSET);
header('Content-Disposition: attachment; filename="'.$csvFileName.'"');
*/

\CFile::viewByUser(
	\CFile::makeFileArray($csvFile->getPhysicalPath()),
	array(
		'force_download' => true,
		'cache_time' => 0,
		'attachment_name' => $csvFileName,
		'content_type' => 'text/csv',
	)
);