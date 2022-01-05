<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?require_once($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/install/wizard_sol/wizard.php");

class SelectSiteStep extends CSelectSiteWizardStep
{
	function InitStep()
	{
		parent::InitStep();

		$wizard =& $this->GetWizard();
		$wizard->solutionName = "community";
	}
}

class SelectTemplateStep extends CSelectTemplateWizardStep { }

class SelectThemeStep extends CSelectThemeWizardStep { }

class SiteSettingsStep extends CSiteSettingsWizardStep
{
	function InitStep()
	{
		$wizard =& $this->GetWizard();
		$wizard->solutionName = "community";
		parent::InitStep();

		$this->SetTitle(GetMessage("wiz_settings"));
		$this->SetNextStep("data_install");
		$this->SetNextCaption(GetMessage("wiz_install"));

		$siteID = $wizard->GetVar("siteID");
		
		$siteLogo = $this->GetFileContentImgSrc(WIZARD_SITE_PATH."include/company_logo.php", "/bitrix/wizards/bitrix/demo_community/site/templates/taby/images/logo.jpg");
	
		$wizard->SetDefaultVars(
			Array(
				"siteName" => $this->GetFileContent(WIZARD_SITE_PATH."include/company_name.php", GetMessage("wiz_name")),
				"siteDescription" => $this->GetFileContent(WIZARD_SITE_PATH."include/company_description.php", GetMessage("wiz_slogan")), 
				"siteLogo" => $siteLogo,
				"siteMetaDescription" => GetMessage("wiz_slogan"),
				"siteMetaKeywords" => GetMessage("wiz_keywords")  
			)
		);
	}

	function OnPostForm()
	{
		$wizard =& $this->GetWizard();
		$res = $this->SaveFile("siteLogo", Array("extensions" => "gif,jpg,jpeg,png", "max_height" => 80, "max_width" => 90, "make_preview" => "Y"));
	}
	
	function ShowStep()
	{
		$wizard =& $this->GetWizard();
		$siteLogo = $wizard->GetVar("siteLogo", true);
		
		$this->content .= '<div class="wizard-input-form">';
		$this->content .= '
		<div class="wizard-upload-img-block">
			<div class="wizard-catalog-title">'.GetMessage("wiz_company_name").'</div>
			'.$this->ShowInputField('text', 'siteName', array("id" => "siteName", "class" => "wizard-field")).'
		</div>';
		$this->content .= '
		<div class="wizard-upload-img-block">
			<div class="wizard-catalog-title">'.GetMessage("wiz_company_description").'</div>
			'.$this->ShowInputField('text', 'siteDescription', array("id" => "siteDescription", "class" => "wizard-field")).'
		</div>';

		if($wizard->GetVar("templateID")=="taby")
		{
		$this->content .= '
		<div class="wizard-upload-img-block">
			<div class="wizard-catalog-title">'.GetMessage("wiz_company_logo").'</div>
			'.CFile::ShowImage($siteLogo, 100, 100, "border=0 vspace=5").'<br/>'
			.$this->ShowFileField("siteLogo",
				Array(
					"show_file_info"=> "N",
					"id" => "siteLogo",
					)).'
		</div>';
		}

		$firstStep = COption::GetOptionString("main", "wizard_first".mb_substr($wizard->GetID(), 7)  . "_" . $wizard->GetVar("siteID"), false, $wizard->GetVar("siteID"));
		$styleMeta = 'style="display:block"';
		if($firstStep == "Y") $styleMeta = 'style="display:none"';
		$this->content .= '
			<div id="bx_metadata" '. $styleMeta .'><div class="wizard-input-form-block">
				<div class="wizard-metadata-title">'.GetMessage("wiz_meta_data").'</div>
				<div class="wizard-upload-img-block">
					<label for="siteMetaDescription" class="wizard-input-title">'.GetMessage("wiz_meta_description").'</label>
					'.$this->ShowInputField("textarea", "siteMetaDescription", Array("id" => "siteMetaDescription", "class" => "wizard-field", "rows"=>"3")).'
				</div>';
			$this->content .= '
				<div class="wizard-upload-img-block">
					<label for="siteMetaKeywords" class="wizard-input-title">'.GetMessage("wiz_meta_keywords").'</label>
					'.$this->ShowInputField('text', 'siteMetaKeywords', array("id" => "siteMetaKeywords", "class" => "wizard-field")).'
				</div>
			</div></div>';
		
 
		if($firstStep == "Y")
		{
			$this->content .= '
			<div class="wizard-input-form-block">'.
						$this->ShowCheckboxField(
							"installDemoData", 
							"Y", 
							(array("id" => "installDemoData", "onClick" => "if(this.checked == true){document.getElementById('bx_metadata').style.display='block';}else{document.getElementById('bx_metadata').style.display='none';}"))
						).
				'
				<label for="installDemoData">'.GetMessage("wiz_structure_data").'</label>
			</div>';
			}
		else
		{
			$this->content .= $this->ShowHiddenField("installDemoData","Y");
		}
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