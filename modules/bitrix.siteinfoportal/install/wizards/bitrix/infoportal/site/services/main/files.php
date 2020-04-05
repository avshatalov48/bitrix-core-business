<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

if (!defined("WIZARD_SITE_ID"))
	return;

if (!defined("WIZARD_SITE_DIR"))
	return;

$lang = (in_array(LANGUAGE_ID, array("ru", "en", "de"))) ? LANGUAGE_ID : \Bitrix\Main\Localization\Loc::getDefaultLang(LANGUAGE_ID);

if(COption::GetOptionString("infoportal", "wizard_installed", "N", WIZARD_SITE_ID) == "Y")
{
	$wizard =& $this->GetWizard();
	
	___writeToAreasFile(WIZARD_SITE_PATH."include/infoportal_name.php", $wizard->GetVar("siteName"));
	___writeToAreasFile(WIZARD_SITE_PATH."include/copyright.php", $wizard->GetVar("siteCopy"));

	if($wizard->GetVar('rewriteIndex', true)){
		CopyDirFiles(
			WIZARD_ABSOLUTE_PATH."/site/public/".$lang."/_index.php",
			WIZARD_SITE_PATH."/_index.php",
			$rewrite = true,
			$recursive = true,
			$delete_after_copy = false
		);
		CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/_index.php", Array("SITE_DIR" => WIZARD_SITE_DIR));
	}
	//die;
	return;
}

$path = str_replace("//", "/", WIZARD_ABSOLUTE_PATH."/site/public/".$lang."/");

$handle = @opendir($path);
if ($handle)
{
	while ($file = readdir($handle))
	{
		if (in_array($file, array(".", "..")))
			continue; 

		CopyDirFiles(
			$path.$file,
			WIZARD_SITE_PATH."/".$file,
			$rewrite = true, 
			$recursive = true,
			$delete_after_copy = false
		);
	}
}

WizardServices::PatchHtaccess(WIZARD_SITE_PATH);

WizardServices::ReplaceMacrosRecursive(WIZARD_SITE_PATH."about/", Array("SITE_DIR" => WIZARD_SITE_DIR));
WizardServices::ReplaceMacrosRecursive(WIZARD_SITE_PATH."blogs/", Array("SITE_DIR" => WIZARD_SITE_DIR));
WizardServices::ReplaceMacrosRecursive(WIZARD_SITE_PATH."board/", Array("SITE_DIR" => WIZARD_SITE_DIR));
WizardServices::ReplaceMacrosRecursive(WIZARD_SITE_PATH."forum/", Array("SITE_DIR" => WIZARD_SITE_DIR));
WizardServices::ReplaceMacrosRecursive(WIZARD_SITE_PATH."information/", Array("SITE_DIR" => WIZARD_SITE_DIR));
WizardServices::ReplaceMacrosRecursive(WIZARD_SITE_PATH."job/", Array("SITE_DIR" => WIZARD_SITE_DIR));
WizardServices::ReplaceMacrosRecursive(WIZARD_SITE_PATH."nationalnews/", Array("SITE_DIR" => WIZARD_SITE_DIR));
WizardServices::ReplaceMacrosRecursive(WIZARD_SITE_PATH."news/", Array("SITE_DIR" => WIZARD_SITE_DIR));
WizardServices::ReplaceMacrosRecursive(WIZARD_SITE_PATH."personal/", Array("SITE_DIR" => WIZARD_SITE_DIR));
WizardServices::ReplaceMacrosRecursive(WIZARD_SITE_PATH."photo/", Array("SITE_DIR" => WIZARD_SITE_DIR));
WizardServices::ReplaceMacrosRecursive(WIZARD_SITE_PATH."search/", Array("SITE_DIR" => WIZARD_SITE_DIR));
WizardServices::ReplaceMacrosRecursive(WIZARD_SITE_PATH."themes/", Array("SITE_DIR" => WIZARD_SITE_DIR));
CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."_index.php", Array("SITE_DIR" => WIZARD_SITE_DIR));
CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH.".top.menu_ext.php", Array("SITE_DIR" => WIZARD_SITE_DIR));

copy(WIZARD_THEME_ABSOLUTE_PATH."/favicon.ico", WIZARD_SITE_PATH."favicon.ico");

$arUrlRewrite = array(); 
if (file_exists(WIZARD_SITE_ROOT_PATH."/urlrewrite.php"))
{
	include(WIZARD_SITE_ROOT_PATH."/urlrewrite.php");
}

$arNewUrlRewrite = array(
	array(
		"CONDITION"	=>	"#^".WIZARD_SITE_DIR."news/#",
		"RULE"	=>	"",
		"ID"	=>	"bitrix:news",
		"PATH"	=>	 WIZARD_SITE_DIR."news/index.php",
		), 
	array(
		"CONDITION"	=>	"#^".WIZARD_SITE_DIR."themes/#",
		"RULE"	=>	"",
		"ID"	=>	"bitrix:news",
		"PATH"	=>	 WIZARD_SITE_DIR."themes/index.php",
		), 
	array(
		"CONDITION"	=>	"#^".WIZARD_SITE_DIR."nationalnews/#",
		"RULE"	=>	"",
		"ID"	=>	"bitrix:news",
		"PATH"	=>	 WIZARD_SITE_DIR."nationalnews/index.php",
		), 
	array(
		"CONDITION"	=>	"#^".WIZARD_SITE_DIR."forum/#",
		"RULE"	=>	"",
		"ID"	=>	"bitrix:forum",
		"PATH"	=>	 WIZARD_SITE_DIR."forum/index.php",
		),
	array(
		"CONDITION"	=>	"#^".WIZARD_SITE_DIR."job/resume/#",
		"RULE"	=>	"",
		"ID"	=>	"bitrix:catalog",
		"PATH"	=>	 WIZARD_SITE_DIR."job/resume/index.php",
		),
	array(
		"CONDITION"	=>	"#^".WIZARD_SITE_DIR."job/vacancy/#",
		"RULE"	=>	"",
		"ID"	=>	"bitrix:catalog",
		"PATH"	=>	 WIZARD_SITE_DIR."job/vacancy/index.php",
		),
	array(
		"CONDITION"	=>	"#^".WIZARD_SITE_DIR."photo/#",
		"RULE"	=>	"",
		"ID"	=>	"bitrix:photogallery_user",
		"PATH"	=>	 WIZARD_SITE_DIR."photo/index.php",
		),
	array(
		"CONDITION"	=>	"#^".WIZARD_SITE_DIR."blogs/#",
		"RULE"	=>	"",
		"ID"	=>	"bitrix:blog",
		"PATH"	=>	 WIZARD_SITE_DIR."blogs/index.php",
		),
	array(
		"CONDITION"	=>	"#^".WIZARD_SITE_DIR."board/([a-zA-Z0-9_]+)/\?{0,1}(.*)$#",
		"RULE"	=>	WIZARD_SITE_DIR.'board/index.php?SECTION_CODE=\1&\2',
		"ID"	=>	"",
		"PATH"	=>	"",
		),
	array(
		"CONDITION"	=>	"#^".WIZARD_SITE_DIR."information/links/([a-zA-Z0-9_]+)/\?{0,1}(.*)$#",
		"RULE"	=>	WIZARD_SITE_DIR.'information/links/index.php?SECTION_CODE=\1&\2',
		"ID"	=>	"",
		"PATH"	=>	"",
		),
	); 

foreach ($arNewUrlRewrite as $arUrl)
{
	if (!in_array($arUrl, $arUrlRewrite))
	{
		CUrlRewriter::Add($arUrl);
	}
}


function ___writeToAreasFile($fn, $text)
{
	if(file_exists($fn) && !is_writable($abs_path) && defined("BX_FILE_PERMISSIONS"))
		@chmod($abs_path, BX_FILE_PERMISSIONS);

	$fd = @fopen($fn, "wb");
	if(!$fd)
		return false;

	if(false === fwrite($fd, $text))
	{
		fclose($fd);
		return false;
	}

	fclose($fd);

	if(defined("BX_FILE_PERMISSIONS"))
		@chmod($fn, BX_FILE_PERMISSIONS);
}

CheckDirPath(WIZARD_SITE_PATH."include/");

$wizard =& $this->GetWizard();


___writeToAreasFile(WIZARD_SITE_PATH."include/infoportal_name.php", $wizard->GetVar("siteName"));
___writeToAreasFile(WIZARD_SITE_PATH."include/copyright.php", $wizard->GetVar("siteCopy"));

CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/index.php", Array("SITE_DIR" => WIZARD_SITE_DIR));
CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/.section.php", array("SITE_DESCRIPTION" => htmlspecialcharsbx($wizard->GetVar("siteMetaDescription"))));
CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/.section.php", array("SITE_KEYWORDS" => htmlspecialcharsbx($wizard->GetVar("siteMetaKeywords"))));

/*if(CModule::IncludeModule("workflow")){
	$SettingsStatus = '"STATUS" => array(0 => "1",), "STATUS_NEW" => "1",';
} else {
	$SettingsStatus = '"STATUS" => "ANY",	"STATUS_NEW" => "N",';
}*/

$SettingsStatus = '"STATUS" => "ANY",	"STATUS_NEW" => "N",';

CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/job/resume/my/index.php", array("STATUS_SETTINGS" => $SettingsStatus));
CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/job/vacancy/my/index.php", array("STATUS_SETTINGS" => $SettingsStatus));
CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/board/my/index.php", array("STATUS_SETTINGS" => $SettingsStatus));
CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/information/links/my/index.php", array("STATUS_SETTINGS" => $SettingsStatus));

/*
if(CModule::IncludeModule("workflow")){
	$SettingsStatus = '"STATUS" => array(0 => "2",), "STATUS_NEW" => "2",';
} else {
	$SettingsStatus = '"STATUS" => "INACTIVE",	"STATUS_NEW" => "ANY",';
}*/
$SettingsStatus = '"STATUS" => "INACTIVE",	"STATUS_NEW" => "ANY",';

CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/nationalnews/add_news/index.php", array("STATUS_SETTINGS" => $SettingsStatus));

CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/nationalnews/add_news/index.php", array("EMAIL_TO" => COption::GetOptionString("main", "email_from")));
CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/job/resume/my/index.php", array("EMAIL_TO" => COption::GetOptionString("main", "email_from")));
CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/job/vacancy/my/index.php", array("EMAIL_TO" => COption::GetOptionString("main", "email_from")));
CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/board/my/index.php", array("EMAIL_TO" => COption::GetOptionString("main", "email_from")));
CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/information/links/my/index.php", array("EMAIL_TO" => COption::GetOptionString("main", "email_from")));

if(CModule::IncludeModule('subscribe'))
{
	$templates_dir = $_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/php_interface/subscribe/templates";
	$template = $templates_dir."/news";
	//Copy template from module if where was no template
	if(!file_exists($template))
	{
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/subscribe/install/php_interface/subscribe/templates/news", $template, false, true);
		$fname = $template."/template.php";
		if(file_exists($fname) && is_file($fname) && ($fh = fopen($fname, "rb")))
		{
			$php_source = fread($fh, filesize($fname));
			$php_source = preg_replace("#([\"'])(SITE_ID)(\\1)(\\s*=>\s*)([\"'])(.*?)(\\5)#", "\\1\\2\\3\\4\\5".WIZARD_SITE_ID."\\7", $php_source);
			$php_source = str_replace("Windows-1251", $arSite["CHARSET"], $php_source);
			$php_source = str_replace("Hello!", GetMessage("SUBSCR_1"), $php_source);
			$php_source = str_replace("<P>Best Regards!</P>", "", $php_source);
			fclose($fh);
			$fh = fopen($fname, "wb");
			if($fh)
			{
				fwrite($fh, $php_source);
				fclose($fh);
			}
		}
	}
}
?>