<?
/** @global CMain $APPLICATION */
use Bitrix\Main\Loader;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/translate/prolog.php");
$TRANS_RIGHT = $APPLICATION->GetGroupRight("translate");
if($TRANS_RIGHT=="D") $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
Loader::includeModule('translate');
IncludeModuleLangFile(__FILE__);
define("HELP_FILE","translate_list.php");

/***************************************************************************
								GET | POST
***************************************************************************/
$strError = "";


$APPLICATION->SetTitle(GetMessage("TRANS_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

if(preg_match("#\.\.[\\/]#".BX_UTF_PCRE_MODIFIER, $path))
	$path = "";
// If not specified then
if (strlen($path)<=0)
	$path = TRANSLATE_DEFAULT_PATH;

$path = Rel2Abs("/", "/".$path."/");
if (!isAllowPath($path))
	$path = TRANSLATE_DEFAULT_PATH;

$IS_LANG_DIR = is_lang_dir($path);
if (!$IS_LANG_DIR)
	$strError = GetMessage('TR_NO_LANG_DIR');

$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("TRANS_TITLE"), "ICON" => "translate_edit", "TITLE" => GetMessage("TRANS_TITLE_TITLE")),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);

$chain = "";
$arPath = array();

if($strError == "")
{
	//a way to get back
	$arSlash = explode("/",$path);
	if (is_array($arSlash))
	{
		$arSlash_tmp = $arSlash;
		$lang_key = array_search("lang", $arSlash) + 1;
		unset($arSlash_tmp[$lang_key]);
		if ($lang_key==sizeof($arSlash)-1)
		{
			unset($arSlash[$lang_key]);
			$path_back = implode("/",$arSlash);
		}
		$i = 0;
		foreach($arSlash_tmp as $dir)
		{
			$i++;
			if ($i==1)
			{
				$chain .= '/ ';
			}
			else
			{
				$arPath[] = htmlspecialcharsbx($dir);
				if ($i>2)
					$chain .= " / ";
				$chain .= '<a href="translate_list.php?lang='.LANGUAGE_ID."&path="."/".implode('/',$arPath)."/".'" title="'.GetMessage("TRANS_CHAIN_FOLDER").'">'.htmlspecialcharsbx($dir).'</a>';
			}
		}
	}
}

$APPLICATION->SetTitle(GetMessage("TRANS_TITLE"));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

if($strError <> "")
{
	CAdminMessage::ShowMessage($strError);
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}

$arFiles = array();
$arDirs = array();
$arDirFiles = array();
$arLangDirs = array();
$arTLangs = GetTLangList();
GetTDirList($path, true);
GetLangDirs($arDirs);
$arLangDirFiles = array_merge($arLangDirs, $arFiles);
GetTLangFiles($path, $IS_LANG_DIR);

/***************************************************************************
								HTML
****************************************************************************/
$aMenu = array();

$aMenu[] = Array(
	"TEXT"	=> GetMessage("TRANS_LIST"),
	"LINK"	=> "/bitrix/admin/translate_list.php?lang=".LANGUAGE_ID."&path=/".implode("/",$arPath)."/",
	"TITLE"	=> GetMessage("TRANS_LIST_TITLE"),
	"ICON"	=> "btn_list"
	);

$context = new CAdminContextMenu($aMenu);
$context->Show();
?>
<p><?=$chain?></p>
<?

$_corrupt = false;
$_strMess = '';
foreach ($arDirFiles as $_file)
{
	$_content = $APPLICATION->GetFileContent(CSite::GetSiteDocRoot(false).$_file);
	$_len = strlen($_content);
	if (($_pos = strrpos($_content, '>')) !== false)
	{
		if ($_len > $_pos + 1)
		{
			$_corrupt = true;
			$_strMess .= '<a class="text" href="translate_edit_php.php?lang='.LANGUAGE_ID.'&file='.$_file.'">'. $_file."</a><br/>";
		}
	}
}

if (!$_corrupt)
{
	CAdminMessage::ShowMessage(array('MESSAGE' => GetMessage('TR_FILES_NOT_FOUND'), 'TYPE' => 'OK'));
	require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	die();
}
else
{
	CAdminMessage::ShowMessage(GetMessage('TR_FILES_FOUND'));
}

$tabControl->Begin();
$tabControl->BeginNextTab();
echo $_strMess;
$tabControl->End();

require_once ($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");