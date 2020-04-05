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
$abs_path = $_SERVER["DOCUMENT_ROOT"].$templateDir."/description.php"; 
if (file_exists($abs_path))
{
	if (is_file(WIZARD_THEME_ABSOLUTE_PATH."/description.php"))
	{
		if (LANGUAGE_ID != "en" && LANGUAGE_ID != "ru")
		{
			if (file_exists(WIZARD_THEME_ABSOLUTE_PATH."/lang/en/description.php"))
				__IncludeLang(WIZARD_THEME_ABSOLUTE_PATH."/lang/en/description.php");
		}
		if (file_exists(WIZARD_THEME_ABSOLUTE_PATH."/lang/".LANGUAGE_ID."/description.php"))
			__IncludeLang(WIZARD_THEME_ABSOLUTE_PATH."/lang/".LANGUAGE_ID."/description.php");
	}

	@include(WIZARD_THEME_ABSOLUTE_PATH."/description.php"); 
$strThemeName = strtolower($arTemplate["NAME"]); 

	if (is_file(WIZARD_TEMPLATE_ABSOLUTE_PATH."/description.php"))
	{
		if (LANGUAGE_ID != "en" && LANGUAGE_ID != "ru")
		{
			if (file_exists(WIZARD_TEMPLATE_ABSOLUTE_PATH."/lang/en/description.php"))
				__IncludeLang(WIZARD_TEMPLATE_ABSOLUTE_PATH."/lang/en/description.php");
		}
		if (file_exists(WIZARD_TEMPLATE_ABSOLUTE_PATH."/lang/".LANGUAGE_ID."/description.php"))
			__IncludeLang(WIZARD_TEMPLATE_ABSOLUTE_PATH."/lang/".LANGUAGE_ID."/description.php");
	}
	@include(WIZARD_TEMPLATE_ABSOLUTE_PATH."/description.php"); 
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

COption::SetOptionString("main", "wizard_".WIZARD_TEMPLATE_ID."_theme_id", WIZARD_THEME_ID);

//Color scheme for main.interface.grid/form
CUserOptions::SetOption("main.interface", "global", array("theme"=>WIZARD_THEME_ID), true);
?>