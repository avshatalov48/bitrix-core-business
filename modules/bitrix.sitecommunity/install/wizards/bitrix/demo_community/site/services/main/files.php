<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

if (!defined("WIZARD_SITE_ID"))
	return;

if (!defined("WIZARD_SITE_DIR"))
	return;
   
if (WIZARD_INSTALL_DEMO_DATA)
{
	if (COption::GetOptionString("main", "upload_dir") == "")
		COption::SetOptionString("main", "upload_dir", "upload");
	CopyDirFiles(
		WIZARD_ABSOLUTE_PATH."/site/public/".LANGUAGE_ID,
		WIZARD_SITE_PATH,
		$rewrite = true, 
		$recursive = true,
		$delete_after_copy = false
	);

	WizardServices::PatchHtaccess(WIZARD_SITE_PATH);

	CopyDirFiles(
		WIZARD_ABSOLUTE_PATH."/site/services/main/images",
		WIZARD_SITE_ROOT_PATH . "/upload",
		$rewrite = true, 
		$recursive = true,
		$delete_after_copy = false
	);
	
	WizardServices::ReplaceMacrosRecursive(WIZARD_SITE_PATH."groups/", Array("SITE_DIR" => WIZARD_SITE_DIR));
	WizardServices::ReplaceMacrosRecursive(WIZARD_SITE_PATH."people/", Array("SITE_DIR" => WIZARD_SITE_DIR));
	WizardServices::ReplaceMacrosRecursive(WIZARD_SITE_PATH."photo/", Array("SITE_DIR" => WIZARD_SITE_DIR));
	WizardServices::ReplaceMacrosRecursive(WIZARD_SITE_PATH."search/", Array("SITE_DIR" => WIZARD_SITE_DIR));
	CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."_index.php", Array("SITE_DIR" => WIZARD_SITE_DIR));
	CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH.".top.menu.php", Array("SITE_DIR" => WIZARD_SITE_DIR));
	CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH.".bottom.menu.php", Array("SITE_DIR" => WIZARD_SITE_DIR));
	CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."sect_inc.php", Array("SITE_DIR" => WIZARD_SITE_DIR));
	
	$arUrlRewrite = array(); 
	if (file_exists(WIZARD_SITE_ROOT_PATH."/urlrewrite.php"))
	{
		include(WIZARD_SITE_ROOT_PATH."/urlrewrite.php");
	}
	$arNewUrlRewrite = array(
		array(
			"CONDITION" => "#^".WIZARD_SITE_DIR."groups/#",	
			"RULE" => "", 
			"ID" => "bitrix:socialnetwork_group",
			"PATH" => WIZARD_SITE_DIR."groups/group.php"), 
		array(
			"CONDITION" => "#^".WIZARD_SITE_DIR."people/#",
			"RULE" => "", 
			"ID" => "bitrix:socialnetwork_user",
			"PATH" => WIZARD_SITE_DIR."people/user.php"), 
		array(
			"CONDITION" => "#^".WIZARD_SITE_DIR."forum/#",
			"RULE" => "", 
			"ID" => "bitrix:forum",
			"PATH" => WIZARD_SITE_DIR."forum/index.php"), 
		array(
			"CONDITION" => "#^".WIZARD_SITE_DIR."photo/#",
			"RULE" => "", 
			"ID" => "bitrix:photogallery_user",
			"PATH" => WIZARD_SITE_DIR."photo/index.php"), 
		array(
			"CONDITION" => "#^".WIZARD_SITE_DIR."about/#",
			"RULE" => "", 
			"ID" => "",
			"PATH" => WIZARD_SITE_DIR."about.php")); 
	
	foreach ($arNewUrlRewrite as $arUrl)
	{
		if (!in_array($arUrl, $arUrlRewrite))
		{
			CUrlRewriter::Add($arUrl);
		}
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
___writeToAreasFile(WIZARD_SITE_PATH."include/company_name.php", $wizard->GetVar("siteName"));
___writeToAreasFile(WIZARD_SITE_PATH."include/company_description.php", $wizard->GetVar("siteDescription"));

$siteLogo = $wizard->GetVar("siteLogo");
if($siteLogo>0)
{
	$ff = CFile::GetByID($siteLogo);
	if($zr = $ff->Fetch())
	{
		$strOldFile = str_replace("//", "/", WIZARD_SITE_ROOT_PATH."/".(COption::GetOptionString("main", "upload_dir", "upload"))."/".$zr["SUBDIR"]."/".$zr["FILE_NAME"]);
		@copy($strOldFile, WIZARD_SITE_PATH."include/logo.jpg");
		___writeToAreasFile(WIZARD_SITE_PATH."include/company_logo.php", '<img src="'.WIZARD_SITE_DIR.'include/logo.jpg"  />');
		CFile::Delete($siteLogo);
	}
}
elseif(!file_exists(WIZARD_SITE_PATH."include/company_logo.php"))
{
	copy(WIZARD_SITE_ROOT_PATH."/bitrix/wizards/bitrix/demo_community/site/templates/taby/images/logo.jpg", WIZARD_SITE_PATH."include/logo.jpg");
	___writeToAreasFile(WIZARD_SITE_PATH."include/company_logo.php", '<img src="'.WIZARD_SITE_DIR.'include/logo.jpg"  />');
}
if (WIZARD_INSTALL_DEMO_DATA)
{
	CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/.section.php", array("SITE_DESCRIPTION" => htmlspecialcharsbx($wizard->GetVar("siteMetaDescription"))));
	CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/.section.php", array("SITE_KEYWORDS" => htmlspecialcharsbx($wizard->GetVar("siteMetaKeywords"))));
}

?>