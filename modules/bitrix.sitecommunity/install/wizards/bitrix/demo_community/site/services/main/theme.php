<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();
if (!defined("WIZARD_TEMPLATE_ID"))
	return;
$templateDir = BX_PERSONAL_ROOT."/templates/".WIZARD_TEMPLATE_ID."_".WIZARD_THEME_ID;
CopyDirFiles(
	WIZARD_THEME_ABSOLUTE_PATH,
	$_SERVER["DOCUMENT_ROOT"].$templateDir,
	$rewrite = true, 
	$recursive = true,
	$delete_after_copy = false,
	$exclude = "description.php"
);
CopyDirFiles(
	WIZARD_TEMPLATE_ABSOLUTE_PATH."/lang/".LANGUAGE_ID."/".substr(WIZARD_THEME_ABSOLUTE_PATH, strlen(WIZARD_TEMPLATE_ABSOLUTE_PATH))."/images/",
	$_SERVER["DOCUMENT_ROOT"].$templateDir,
	$rewrite = true,
	$recursive = true
);

$abs_path = $_SERVER["DOCUMENT_ROOT"].$templateDir."/description.php"; 
if (file_exists($abs_path))
{
	@include(WIZARD_THEME_ABSOLUTE_PATH."/description.php"); 
	$strThemeName = strtolower($arTemplate["NAME"]); 
	@include($abs_path); 
	$arTemplate["NAME"] .= " (".$strThemeName.")"; 
	$strContent = '<?$arTemplate = Array("NAME" => "'.EscapePHPString($arTemplate["NAME"]).'", "DESCRIPTION" => "'.EscapePHPString($arTemplate["DESCRIPTION"]).'");?>'; 
	$fd = @fopen($abs_path, "wb");
	if ($fd)
	{
		fwrite($fd, $strContent); 
		fclose($fd);
		@chmod($abs_path, BX_FILE_PERMISSIONS);
	}
}

/*
if (WIZARD_SITE_LOGO > 0)
	$success = CWizardUtil::CopyFile(WIZARD_SITE_LOGO, $templateDir."/images/logo.gif", false);
else
	$success = @unlink($_SERVER["DOCUMENT_ROOT"].$templateDir."/images/logo.gif");
*/
COption::SetOptionString("main", "wizard_site_logo", WIZARD_SITE_LOGO, "", WIZARD_SITE_ID);
COption::SetOptionString("main", "wizard_".WIZARD_TEMPLATE_ID."_theme_id", WIZARD_THEME_ID, "", WIZARD_SITE_ID);
//Color scheme for main.interface.grid/form
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/".strToLower($GLOBALS["DB"]->type)."/favorites.php");
CUserOptions::SetOption("main.interface", "global", array("theme" => WIZARD_THEME_ID), true);
/************** Forum Theme ****************************************/
COption::SetOptionString("main", "wizard_".WIZARD_TEMPLATE_ID."_".WIZARD_THEME_ID."_forum_theme_id", WIZARD_THEME_ID, "", WIZARD_SITE_ID); 
/************** Forum Theme****************************************/
?>