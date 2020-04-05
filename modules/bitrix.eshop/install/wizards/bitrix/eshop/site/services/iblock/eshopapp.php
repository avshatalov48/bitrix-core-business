<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

if (LANGUAGE_ID != "ru" || !file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/eshopapp"))
	return;
//install eshopapp
$installEshopApp = $wizard->GetVar("installEshopApp");
$installEshopApp = ($installEshopApp == "Y") ? "Y" : "N";
$wizardInstallEshopApp = false;
$currentInstallEshoApp = COption::GetOptionString("eshop", "installEshopApp", "N", WIZARD_SITE_ID);
if ($currentInstallEshoApp != "Y" && $installEshopApp == "Y")
	$wizardInstallEshopApp = true;
COption::SetOptionString("eshop", "installEshopApp", $installEshopApp, false, WIZARD_SITE_ID);

if (CModule::IncludeModule("iblock"))
{
	$installFile = $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/eshopapp/install/index.php";
	if (!file_exists($installFile))
		return false;

	include_once($installFile);

	$moduleIdTmp = str_replace(".", "_", "eshopapp");
	if (!class_exists($moduleIdTmp))
		return false;

	$module = new $moduleIdTmp;

	$rsIBlock = CIBlock::GetList(array(), array("TYPE" => "catalog", 'LID' => WIZARD_SITE_ID));
	if ($arIBlock = $rsIBlock->Fetch())
	{
		$iblockID = $arIBlock["ID"];

		if ($installEshopApp == "Y" )
		{
			if (!IsModuleInstalled("eshopapp"))
			{
				if (!$module->InstallDB())
					return false;
				$module->InstallEvents();
				if (!$module->InstallFiles(WIZARD_SITE_DIR, WIZARD_SITE_ID))
					return false;
				if (!$module->InstallPublic("catalog", $iblockID, WIZARD_SITE_DIR))
					return false;
			}
			elseif ($wizardInstallEshopApp || WIZARD_INSTALL_DEMO_DATA)
			{
				if (!$module->InstallPublic("catalog", $iblockID, WIZARD_SITE_DIR))
					return false;

				$arAppTempalate = Array(
					"SORT" => 1,
					"CONDITION" => "CSite::InDir('".WIZARD_SITE_DIR."eshop_app/')",
					"TEMPLATE" => "eshop_app"
				);

				$arFields = Array("TEMPLATE"=>Array());
				$dbTemplates = CSite::GetTemplateList(WIZARD_SITE_ID);
				$eshopAppFound = false;
				while($template = $dbTemplates->Fetch())
				{
					if ($template["TEMPLATE"] == "eshop_app")
					{
						$eshopAppFound = true;
						$template = $arAppTempalate;
					}

					$arFields["TEMPLATE"][] = array(
						"TEMPLATE" => $template['TEMPLATE'],
						"SORT" => $template['SORT'],
						"CONDITION" => $template['CONDITION']
					);
				}
				if (!$eshopAppFound)
					$arFields["TEMPLATE"][] = $arAppTempalate;

				$obSite = new CSite;
				$arFields["LID"] = WIZARD_SITE_ID;
				$obSite->Update(WIZARD_SITE_ID, $arFields);
			}
		}
		else
		{
			if (!$module->UnInstallPublic(WIZARD_SITE_DIR))
				return false;

			$arFields = Array("TEMPLATE"=>Array());
			$dbTemplates = CSite::GetTemplateList(WIZARD_SITE_ID);
			while($template = $dbTemplates->Fetch())
			{
				if ($template["TEMPLATE"] != "eshop_app")
				{
					$arFields["TEMPLATE"][] = array(
						"TEMPLATE" => $template['TEMPLATE'],
						"SORT" => $template['SORT'],
						"CONDITION" => $template['CONDITION']
					);
				}
			}

			$obSite = new CSite;
			$arFields["LID"] = WIZARD_SITE_ID;
			$obSite->Update(WIZARD_SITE_ID, $arFields);
		}
	}
}
?>
