<?
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
 */

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

if((!$USER->CanDoOperation('edit_other_settings') && !$USER->CanDoOperation('view_other_settings')) || !check_bitrix_sessid())
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$ID = str_replace("\\", "", $_REQUEST["ID"]);
$ID = str_replace("/", "", $ID);
$bUseCompression = true;
if(!extension_loaded('zlib') || !function_exists("gzcompress"))
	$bUseCompression = false;

CheckDirPath($_SERVER['DOCUMENT_ROOT'].BX_PERSONAL_ROOT."/tmp/templates/");
$tmpfname = $_SERVER['DOCUMENT_ROOT'].BX_PERSONAL_ROOT."/tmp/templates/".\Bitrix\Main\Security\Random::getString(32).".tar.gz";

$HTTP_ACCEPT_ENCODING = "";

$strError = "";
$path = getLocalPath("templates/".$ID, BX_PERSONAL_ROOT);
if(is_dir($_SERVER["DOCUMENT_ROOT"].$path))
{
	$oArchiver = new CArchiver($tmpfname, $bUseCompression);
	$tres = $oArchiver->add("\"".$_SERVER["DOCUMENT_ROOT"].$path."\"", false, $_SERVER["DOCUMENT_ROOT"].$path);
	if(!$tres)
	{
		$strError = "Archiver error";
		$arErrors = &$oArchiver->GetErrors();
		if(!empty($arErrors))
		{
			$strError .= ":<br>";
			foreach ($arErrors as $value)
				$strError .= "[".$value[0]."] ".$value[1]."<br>";
		}
		else
			$strError .= ".<br>";
	}

	header('Pragma: public');
	header('Cache-control: private');
	header("Content-Type: application/force-download; name=\"".$ID.".tar.gz\"");
	header("Content-Transfer-Encoding: binary");
	header("Content-Length: ".filesize($tmpfname));
	header("Content-Disposition: attachment; filename=\"".$ID.".tar.gz\"");
	header("Expires: 0");
	
	readfile($tmpfname);
	unlink($tmpfname);
	//	die();
}

if ($strError <> '')
{
	$APPLICATION->SetTitle("Archiver error");
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

	CAdminMessage::ShowMessage($strError);

	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_before.php");
}
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_after.php");
