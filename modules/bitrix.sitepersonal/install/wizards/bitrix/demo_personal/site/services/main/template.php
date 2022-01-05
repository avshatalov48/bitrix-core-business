<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();
/*
echo "WIZARD_SITE_ID=".WIZARD_SITE_ID." | ";
echo "WIZARD_SITE_PATH=".WIZARD_SITE_PATH." | ";
echo "WIZARD_RELATIVE_PATH=".WIZARD_RELATIVE_PATH." | ";
echo "WIZARD_ABSOLUTE_PATH=".WIZARD_ABSOLUTE_PATH." | ";
echo "WIZARD_TEMPLATE_ID=".WIZARD_TEMPLATE_ID." | ";
echo "WIZARD_TEMPLATE_RELATIVE_PATH=".WIZARD_TEMPLATE_RELATIVE_PATH." | ";
echo "WIZARD_TEMPLATE_ABSOLUTE_PATH=".WIZARD_TEMPLATE_ABSOLUTE_PATH." | ";
echo "WIZARD_THEME_ID=".WIZARD_THEME_ID." | ";
echo "WIZARD_THEME_RELATIVE_PATH=".WIZARD_THEME_RELATIVE_PATH." | ";
echo "WIZARD_THEME_ABSOLUTE_PATH=".WIZARD_THEME_ABSOLUTE_PATH." | ";
echo "WIZARD_SERVICE_RELATIVE_PATH=".WIZARD_SERVICE_RELATIVE_PATH." | ";
echo "WIZARD_SERVICE_ABSOLUTE_PATH=".WIZARD_SERVICE_ABSOLUTE_PATH." | ";
echo "WIZARD_IS_RERUN=".WIZARD_IS_RERUN." | ";
die();
*/
if (!defined("WIZARD_TEMPLATE_ID"))
	return;

$bitrixTemplateDir = $_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/templates/".WIZARD_TEMPLATE_ID."_".WIZARD_THEME_ID;

CopyDirFiles(
	$_SERVER["DOCUMENT_ROOT"].WizardServices::GetTemplatesPath(WIZARD_RELATIVE_PATH."/site")."/".WIZARD_TEMPLATE_ID,
	$bitrixTemplateDir,
	$rewrite = true,
	$recursive = true,
	$delete_after_copy = false,
	$exclude = "themes"
);

CWizardUtil::ReplaceMacros(
	$bitrixTemplateDir.'/include_areas/site_name.php',
	array(
		"COMPANY_NAME" => COption::GetOptionString('main', 'site_personal_name', '', WIZARD_SITE_ID),
	)
);

//wizard customization file
$bxProductConfig = array();
if(file_exists($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/.config.php"))
	include($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/.config.php");

if(isset($bxProductConfig["intranet_public"]["copyright"]))
	$templ_copyright = $bxProductConfig["intranet_public"]["copyright"];
else
	$templ_copyright = COption::GetOptionString('main', 'site_copyright', '', WIZARD_SITE_ID);

CWizardUtil::ReplaceMacros(
	$bitrixTemplateDir.'/include_areas/copyright.php',
	array(
		"COPYRIGHT" => $templ_copyright,
	)
);

//Attach template to default site
$obSite = CSite::GetList("def", "desc", Array("LID" => WIZARD_SITE_ID));
if ($arSite = $obSite->Fetch())
{
	$arTemplates = Array();
	$found = false;
	$foundEmpty = false;
	$obTemplate = CSite::GetTemplateList($arSite["LID"]);
	while($arTemplate = $obTemplate->Fetch())
	{
		if(!$found && trim($arTemplate["CONDITION"]) == '')
		{
			$arTemplate["TEMPLATE"] = WIZARD_TEMPLATE_ID."_".WIZARD_THEME_ID;
			$found = true;
		}
		if($arTemplate["TEMPLATE"] == "empty")
		{
			$foundEmpty = true;
			continue;
		}
		$arTemplates[]= $arTemplate;
	}

	if (!$found)
		$arTemplates[]= Array("CONDITION" => "", "SORT" => 150, "TEMPLATE" => WIZARD_TEMPLATE_ID."_".WIZARD_THEME_ID);

	$arFields = Array(
		"TEMPLATE" => $arTemplates,
		"NAME" => $arSite["NAME"],
	);

	$obSite = new CSite();
	$obSite->Update($arSite["LID"], $arFields);
}
COption::SetOptionString("main", "wizard_template_id", WIZARD_TEMPLATE_ID, false, WIZARD_SITE_ID);
?>
