<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/prolog.php");

if (!$USER->CanDoOperation('fileman_view_file_structure'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/include.php");
IncludeModuleLangFile(__FILE__);

$strWarning = "";
$site ??= $_REQUEST['site'] ?? null;
$site = CFileMan::__CheckSite($site);
$DOC_ROOT = CSite::GetSiteDocRoot($site);
$io = CBXVirtualIo::GetInstance();
$path = CBXVirtualIoFileSystem::ConvertCharset($path, CBXVirtualIoFileSystem::directionDecode);
$path = $io->CombinePath("/", $path);
$arFile = CFile::MakeFileArray($io->GetPhysicalName($DOC_ROOT.$path));
$arFile["tmp_name"] = CBXVirtualIoFileSystem::ConvertCharset($arFile["tmp_name"] ?? '', CBXVirtualIoFileSystem::directionDecode);
$arPath = Array($site, $path);

if(!$USER->CanDoFileOperation('fm_download_file', $arPath))
	$strWarning = GetMessage("ACCESS_DENIED");
else if(!$io->FileExists($arFile["tmp_name"]))
	$strWarning = GetMessage("FILEMAN_FILENOT_FOUND")." ";
elseif(!$USER->CanDoOperation('edit_php') && (HasScriptExtension($path) || mb_substr(CFileman::GetFileName($path), 0, 1) == "."))
	$strWarning .= GetMessage("FILEMAN_FILE_DOWNLOAD_PHPERROR")."\n";

$arFile["name"] = $arFile["name"] ?? '';
if($strWarning == '')
{
	$fileName = str_replace(array("\r", "\n"), "", $arFile["name"]);
	$flTmp = $io->GetFile($arFile["tmp_name"]);
	$fsize = $flTmp->GetFileSize();
	$bufSize = 4194304; //4M

	session_write_close();
	set_time_limit(0);

	header("Content-Type: application/force-download; name=\"".$fileName."\"");
	header("Content-Transfer-Encoding: binary");
	header("Content-Length: ".$fsize);
	header("Content-Disposition: attachment; filename=\"".$fileName."\"");
	header("Expires: 0");
	header("Cache-Control: no-cache, must-revalidate");
	header("Pragma: no-cache");
	header('Connection: close');

	$arFile["tmp_name"] = CBXVirtualIoFileSystem::ConvertCharset($arFile["tmp_name"], CBXVirtualIoFileSystem::directionEncode);

	$f=fopen($arFile["tmp_name"], 'rb');

	while(!feof($f))
	{
		echo fread($f, $bufSize);
		ob_flush();
		flush();
		ob_end_clean ();
	}

	fclose($f);
	die();
}

$APPLICATION->SetTitle(GetMessage("FILEMAN_FILEDOWNLOAD")." \"".$arFile["name"]."\"");
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
?>
<font class="text"><?=$arFile["name"]?></font><br><br>
<?
ShowError($strWarning);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
?>
