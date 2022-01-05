<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
require_once($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/install/wizard_sol/wizard.php");

class SelectSiteStep extends CSelectSiteWizardStep
{
	function InitStep()
	{
		parent::InitStep();

		$wizard =& $this->GetWizard();
		$wizard->solutionName = "corp_services";
	}
}


class SelectTemplateStep extends CSelectTemplateWizardStep
{
}

class SelectThemeStep extends CSelectThemeWizardStep
{

}

class SiteSettingsStep extends CSiteSettingsWizardStep
{
	function InitStep()
	{
		$wizard =& $this->GetWizard();
		$wizard->solutionName = "corp_services";
		parent::InitStep();

		$templateID = $wizard->GetVar("templateID");
		$themeID = $wizard->GetVar($templateID."_themeID");

		$siteLogo = $this->GetFileContentImgSrc(WIZARD_SITE_PATH."include/company_name.php", "/bitrix/wizards/bitrix/corp_services/site/templates/corp_services/themes/".$themeID."/lang/".LANGUAGE_ID."/logo.gif");
		if (!file_exists(WIZARD_SITE_PATH."include/logo.gif"))
			$siteLogo = "/bitrix/wizards/bitrix/corp_services/site/templates/corp_services/themes/".$themeID."/lang/".LANGUAGE_ID."/logo.gif";
			
		$siteBanner = $this->GetFileContentImgSrc(WIZARD_SITE_PATH."include/banner.php", "/bitrix/wizards/bitrix/corp_services/site/templates/corp_services/images/banner.png");
		
		$wizard->SetDefaultVars(
			Array(
				"siteLogo" => $siteLogo,
				"siteBanner" => $siteBanner, 
				"siteBannerText" => $this->GetFileContent(WIZARD_SITE_PATH."include/banner_text.php", GetMessage("WIZ_BANNER_TEXT_DEFAULT")),
				"siteSlogan" => $this->GetFileContent(WIZARD_SITE_PATH."include/company_slogan.php", GetMessage("WIZ_COMPANY_SLOGAN_DEF")),
				"siteCopy" => $this->GetFileContent(WIZARD_SITE_PATH."include/copyright.php", GetMessage("WIZ_COMPANY_COPY_DEF")),
				"siteMetaDescription" => GetMessage("wiz_site_desc"),
				"siteMetaKeywords" => GetMessage("wiz_keywords"),  
			)
		);
	}

	function ShowStep()
	{
		$wizard =& $this->GetWizard();
				
		$siteLogo = $wizard->GetVar("siteLogo", true);

		$this->content .= '<div class="wizard-upload-img-block"><div class="wizard-catalog-title">'.GetMessage("WIZ_COMPANY_LOGO").'</div>';
		$this->content .= CFile::ShowImage($siteLogo, 190, 70, "border=0 vspace=15");
		$this->content .= "<br />".$this->ShowFileField("siteLogo", Array("show_file_info" => "N", "id" => "site-logo")).'</div>';

		$siteBanner = $wizard->GetVar("siteBanner", true);

		$this->content .= '<div class="wizard-upload-img-block"><div class="wizard-catalog-title">'.GetMessage("WIZ_SITE_BANNER").'</div>';
		$this->content .= CFile::ShowImage($siteBanner, 485, 175, "border=0 vspace=15");
		$this->content .= "<br />".$this->ShowFileField("siteBanner", Array("show_file_info" => "N", "id" => "site-banner")).'</div>';

		$this->content .= '<div class="wizard-upload-img-block"><div class="wizard-catalog-title">'.GetMessage("WIZ_BANNER_TEXT").'</div>';
		$this->content .= $this->ShowInputField("textarea", "siteBannerText", Array("id" => "site-text", "class" => "wizard-field", "rows"=>"4"));
		$this->content .= '<img src="/bitrix/wizards/bitrix/corp_services/images/'.LANGUAGE_ID.'/banner_processed.png">'.'</div>';

		$this->content .= '<div class="wizard-upload-img-block"><div class="wizard-catalog-title">'.GetMessage("WIZ_COMPANY_SLOGAN").'</div>';
		$this->content .= $this->ShowInputField("textarea", "siteSlogan", Array("id" => "site-slogan", "class" => "wizard-field", "rows"=>"3")).'</div>';

		$this->content .= '<div class="wizard-upload-img-block"><div class="wizard-catalog-title">'.GetMessage("WIZ_COMPANY_COPY").'</div>';
		$this->content .= $this->ShowInputField("textarea", "siteCopy", Array("id" => "site-copy", "class" => "wizard-field", "rows"=>"3")).'</div>';

		$firstStep = COption::GetOptionString("main", "wizard_first".mb_substr($wizard->GetID(), 7)  . "_" . $wizard->GetVar("siteID"), false, $wizard->GetVar("siteID"));

		$styleMeta = 'style="display:block"';
		if($firstStep == "Y") $styleMeta = 'style="display:none"';

		$this->content .= '
		<div  id="bx_metadata" '.$styleMeta.'>
			<div class="wizard-input-form-block">
				<div class="wizard-metadata-title">'.GetMessage("wiz_meta_data").'</div>
				<div class="wizard-upload-img-block">
					<label for="siteMetaDescription" class="wizard-input-title">'.GetMessage("wiz_meta_description").'</label>
					'.$this->ShowInputField("textarea", "siteMetaDescription", Array("id" => "siteMetaDescription", "class" => "wizard-field", "rows"=>"3")).'
				</div>';
			$this->content .= '
				<div class="wizard-upload-img-block">
					<label for="siteMetaKeywords" class="wizard-input-title">'.GetMessage("wiz_meta_keywords").'</label><br>
					'.$this->ShowInputField('text', 'siteMetaKeywords', array("id" => "siteMetaKeywords", "class" => "wizard-field")).'
				</div>
			</div>
		</div>';
		
		if($firstStep == "Y")
		{

			$this->content .= $this->ShowCheckboxField("installDemoData", "Y", 
				(array("id" => "install-demo-data", "onClick" => "if(this.checked == true){document.getElementById('bx_metadata').style.display='block';}else{document.getElementById('bx_metadata').style.display='none';}")));
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

	function OnPostForm()
	{
		$wizard =& $this->GetWizard();
		$res = $this->SaveFile("siteLogo", Array("extensions" => "gif,jpg,jpeg,png", "max_height" => 70, "max_width" => 190, "make_preview" => "Y"));
		$res = $this->SaveFile("siteBanner", Array("extensions" => "gif,jpg,jpeg,png", "max_height" => 600, "max_width" => 600, "make_preview" => "Y"));
//		COption::SetOptionString("main", "wizard_site_logo", $res, "", $wizard->GetVar("siteID")); 
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