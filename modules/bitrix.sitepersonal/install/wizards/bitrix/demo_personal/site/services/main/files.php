<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

if (!defined("WIZARD_SITE_ID"))
	return;

if (!defined("WIZARD_SITE_DIR"))
	return;

if (WIZARD_INSTALL_DEMO_DATA)
{
	CopyDirFiles(
		WIZARD_ABSOLUTE_PATH."/site/public/".LANGUAGE_ID,
		WIZARD_SITE_PATH,
		$rewrite = true, 
		$recursive = true,
				$delete_after_copy = false
	);

	WizardServices::PatchHtaccess(WIZARD_SITE_PATH);

	CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."_index.php", Array("SITE_DIR" => WIZARD_SITE_DIR));
	
	$arUrlRewrite = array(); 
	if (file_exists(WIZARD_SITE_ROOT_PATH."/urlrewrite.php"))
	{
		include(WIZARD_SITE_ROOT_PATH."/urlrewrite.php");
	}
	$arNewUrlRewrite = array(
		array(
			"CONDITION" => "#^".WIZARD_SITE_DIR."#",	
			"RULE" => "", 
			"ID" => "bitrix:blog",
			"PATH" => WIZARD_SITE_DIR."index.php"), 
		array(
			"CONDITION" => "#^".WIZARD_SITE_DIR."photo/#",
			"RULE" => "", 
			"ID" => "bitrix:photogallery",
			"PATH" => WIZARD_SITE_DIR."photo.php"), 
	);
	foreach ($arNewUrlRewrite as $arUrl)
	{
		if (!in_array($arUrl, $arUrlRewrite))
		{
			CUrlRewriter::Add($arUrl);
		}
	}
}
?>