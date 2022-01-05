<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
require_once($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/install/wizard_sol/wizard.php");

class SelectSiteStep extends CSelectSiteWizardStep
{
	function InitStep()
	{
		parent::InitStep();

		$wizard =& $this->GetWizard();
		$wizard->solutionName = "personal";
	}
}

class SelectTemplateStep extends CSelectTemplateWizardStep { }

class SelectThemeStep extends CSelectThemeWizardStep { }

class SiteSettingsStep extends CSiteSettingsWizardStep
{
	function InitStep()
	{
		$wizard =& $this->GetWizard();
		$wizard->solutionName = "personal";
		parent::InitStep();
		
		/*$this->SetStepID("site_settings");
		$this->SetTitle(GetMessage("wiz_settings"));
		$this->SetSubTitle(GetMessage("wiz_settings"));
		$this->SetNextStep("data_install");
		$this->SetPrevStep("select_theme");
		$this->SetNextCaption(GetMessage("wiz_install"));
		$this->SetPrevCaption(GetMessage("PREVIOUS_BUTTON"));
*/
		$this->SetTitle(GetMessage("wiz_settings"));
		$this->SetNextStep("data_install");
		$this->SetNextCaption(GetMessage("wiz_install"));

		$siteID = $wizard->GetVar("siteID");


		$wizard->SetDefaultVars(
			Array(
				"siteName" => COption::GetOptionString("main", "site_personal_name", GetMessage("wiz_name"), $wizard->GetVar("siteID")),
				"copyright" => COption::GetOptionString("main", "site_copyright", GetMessage("wiz_copyright"), $wizard->GetVar("siteID")),
				"installDemoData" => COption::GetOptionString("main", "wizard_demo_data", "N")
			)
		);
	}

	function OnPostForm()
	{
		$wizard =& $this->GetWizard();
		
		if ($wizard->IsNextButtonClick())
		{
			COption::SetOptionString("main", "site_personal_name", str_replace(Array("<"), Array("&lt;"), $wizard->GetVar("siteName")));
			COption::SetOptionString("main", "site_copyright", str_replace(Array("<"), Array("&lt;"), $wizard->GetVar("copyright")));
		}
	}

	function ShowStep()
	{
		$wizard =& $this->GetWizard();

		$wizard->SetVar("siteName", COption::GetOptionString("main", "site_personal_name", GetMessage("wiz_name"), $wizard->GetVar("siteID")));
		$wizard->SetVar("copyright", COption::GetOptionString("main", "site_copyright", GetMessage("wiz_copyright"), $wizard->GetVar("siteID")));				

		$this->content .= '<div class="wizard-upload-img-block"><div class="wizard-catalog-title">'.GetMessage("wiz_company_name").'</div>';
		$this->content .= $this->ShowInputField("text", "siteName", Array("id" => "site-name", "class" => "wizard-field"))."</div>";

		$this->content .= '<div class="wizard-upload-img-block"><div class="wizard-catalog-title">'.GetMessage("wiz_company_copyright").'</div>';
		$this->content .= $this->ShowInputField("text", "copyright", Array("id" => "site-copyright", "class" => "wizard-field"))."</div>";

		$firstStep = COption::GetOptionString("main", "wizard_first".mb_substr($wizard->GetID(), 7)  . "_" . $wizard->GetVar("siteID"), false, $wizard->GetVar("siteID"));
		if($firstStep == "Y")
		{
			$this->content .= $this->ShowCheckboxField(
									"installDemoData", 
									"Y", 
									(array("id" => "installDemoData"))
								);
			$this->content .= '<label for="install-demo-data">'.GetMessage("wiz_structure_data").'</label><br />';
		}
		else
		{
			$this->content .= $this->ShowHiddenField("installDemoData","Y");

		}

		$formName = $wizard->GetFormName();
		$installCaption = $this->GetNextCaption();
		$nextCaption = GetMessage("NEXT_BUTTON");
	}
}

class DataInstallStep extends CDataInstallWizardStep
{
	function CorrectServices(&$arServices)
	{
		$wizard =& $this->GetWizard();
		if($wizard->GetVar("installDemoData") != "Y")
		{
		}
	}
}

class FinishStep extends CFinishWizardStep
{
}
?>