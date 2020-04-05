<?
/** @global CMain $APPLICATION */
use Bitrix\Main\Loader;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/translate/prolog.php");
$TRANS_RIGHT = $APPLICATION->GetGroupRight("translate");
if($TRANS_RIGHT=="D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
if (!check_bitrix_sessid()) die();
Loader::includeModule('translate');

$arTLangs = GetTLangList();

$NO_TRANSLATE = array_key_exists('download_translate_lang', $_REQUEST) && $_REQUEST['download_translate_lang'] == 'N';

$path = $_REQUEST["path"];
if(preg_match("#\.\.[\\/]#".BX_UTF_PCRE_MODIFIER, $path))
	$path = "";

$path = Rel2Abs("/", "/".$path."/");

$IS_LANG_DIR = is_lang_dir($path);

if ($IS_LANG_DIR)
{
	foreach ($arTLangs as $hlang)
	{
		$ph = add_lang_id($path, $hlang, $arTLangs);
		if (strlen($ph)>0) GetTDirList($ph, true);
		$ph = "";
	}
}
else GetTDirList($path, true);

$strFile = '';
$arFileFilter = array();
if (isset($_REQUEST['file']))
	$strFile = strval($_REQUEST['file']);
if(preg_match("#\.\.[\\/]#".BX_UTF_PCRE_MODIFIER, $strFile))
	$strFile = "";
if ('' != $strFile)
{
	$strFile = Rel2Abs("/", "/".$strFile);

	foreach ($arTLangs as $hlang)
	{
		$ph = add_lang_id($strFile, $hlang, $arTLangs);
		if ('' != $ph)
			$arFileFilter[] = $ph;
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
		unset($arOneFile);
	$arFiles = $arTemp;
}

$customScriptsFile = $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/langs.txt";
if(file_exists($customScriptsFile) && $_REQUEST["use_custom_list"]=="Y")
{
	$customScriptsList = array();
	$customScriptsListTemp = file($customScriptsFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
	foreach($customScriptsListTemp as $pathScript)
		$customScriptsList[replace_lang_id($pathScript, '#LANG_ID#')] = true;

	$arTemp = array();
	foreach ($arFiles as $f)
	{
		$fname = replace_lang_id($f['PATH'], '#LANG_ID#');
		if($customScriptsList[$fname])
			$arTemp[] = $f;
	}
	$arFiles = $arTemp;
}

$arrCSV = GetTCSVArray();
$strCSV = '"file";"key";';
foreach ($arTLangs as $l)
{
	$strCSV .= '"'.$l.'";';
}
$strCSV .= "\r\n";


foreach ($arrCSV as $file => $arTranslations)
{
	foreach ($arTranslations as $key => $arLangTexts)
	{
		$_strCSV = '';
		$_strCSV .= '"'.$file.'";"'.$key.'";';
		$_noTranslate = false;
		foreach ($arTLangs as $l)
		{
			$val = str_replace('"', '""', $arLangTexts[$l]);
			$val = str_replace("\\", "\\\\", $val);
			$_strCSV .= '"'.$val.'";';
			if (empty($val))
				$_noTranslate = true;
		}
		$_strCSV .= "\r\n";
		if (!$NO_TRANSLATE || ( $NO_TRANSLATE && $_noTranslate))
			$strCSV .= $_strCSV;
	}
}

$csv_fn = trim(str_replace('/', '_', $path), '_') . '.csv';

ob_get_clean();

if(CModule::IncludeModule("compression"))
	CCompress::Disable2048Spaces();

header("Content-Type: text/csv; charset=".LANG_CHARSET);
header('Content-Disposition: attachment; filename="'.$csv_fn.'"');

echo $strCSV;

//require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin_after.php");
