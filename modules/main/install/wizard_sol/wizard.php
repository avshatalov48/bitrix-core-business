<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2013 Bitrix
 */

use Bitrix\Main\Localization\CultureTable;

require_once("utils.php");

class CSelectSiteWizardStep extends CWizardStep
{
	function InitStep()
	{
		$this->SetStepID("select_site");
		$this->SetTitle(GetMessage("SELECT_SITE_TITLE"));
		$this->SetSubTitle(GetMessage("SELECT_SITE_SUBTITLE"));
		$this->SetNextStep("select_template");
		$this->SetNextCaption(GetMessage("NEXT_BUTTON"));
	}

	function OnPostForm()
	{
		$wizard =& $this->GetWizard();

		if ($wizard->IsNextButtonClick())
		{
			$siteID = $wizard->GetVar("siteID");
			$siteFolder = str_replace(array("\\", "///", "//"), "/", "/".$wizard->GetVar("siteFolder")."/");
			$siteNewID = $wizard->GetVar("siteNewID");
			$createSite = $wizard->GetVar("createSite");
			
			if ($createSite == "Y")
			{
				if (strlen($siteNewID) != 2)
				{
					$this->SetError(GetMessage("wiz_site_id_error"));
					return;
				}
				$bError = false;
				$rsSites = CSite::GetList($by="sort", $order="desc", array());
				while($arSite = $rsSites->Fetch())
				{
					if (trim($arSite["DIR"], "/") == trim($siteFolder, "/"))
					{
						$this->SetError(GetMessage("wiz_site_folder_already_exists"));
						$bError = true;
					}

					if ($arSite["ID"] == trim($siteNewID))
					{
						$this->SetError(GetMessage("wiz_site_id_already_exists"));
						$bError = true;
					}
				}
				if ($bError)
					return; 
				$wizard->SetVar("siteID", $siteNewID);
				$wizard->SetVar("siteCreate", "Y"); 
				$wizard->SetVar("siteFolder", $siteFolder); 
			}
			elseif (strlen($siteID) > 0)
			{
				$db_res = CSite::GetList($by="sort", $order="desc", array("LID" => $siteID));
				if (!($db_res && $res = $db_res->Fetch()))
					$this->SetError(GetMessage("wiz_site_id_not_exists_error"));
				return;
			}
			else
			{
				$siteID = WizardServices::GetCurrentSiteID();
				$wizard->SetVar("siteID", $siteID);
			}
		}
	}

	function ShowStep()
	{
		$wizard =& $this->GetWizard();

		$arSites = array(); 
		$arSitesSelect = array(); 
		$db_res = CSite::GetList($by="sort", $order="desc", array("ACTIVE" => "Y"));
		if ($db_res && $res = $db_res->GetNext())
		{
			do 
			{
				$arSites[$res["ID"]] = $res; 
				$arSitesSelect[$res["ID"]] = '['.$res["ID"].'] '.$res["NAME"];
			} while ($res = $db_res->GetNext()); 
		}
		
		$createSite = $wizard->GetVar("createSite"); 
		$createSite = ($createSite == "Y" ? "Y" : "N"); 
		
		
$this->content = 
'<script type="text/javascript">
function SelectCreateSite(element, solutionId)
{
	var container = document.getElementById("solutions-container");
	var nodes = container.childNodes;
	for (var i = 0; i < nodes.length; i++)
	{
		if (!nodes[i].className)
			continue;
		nodes[i].className = "solution-item";
	}
	element.className = "solution-item solution-item-selected";
	var check = document.getElementById("createSite" + solutionId);
	if (check)
		check.checked = true;
}
</script>';
		$this->content .= '<div id="solutions-container">';
			$this->content .= "<div onclick=\"SelectCreateSite(this, 'N');\" ";
				$this->content .= 'class="solution-item'.($createSite != "Y" ? " solution-item-selected" : "").'">'; 
				$this->content .= '<b class="r3"></b><b class="r1"></b><b class="r1"></b>'; 
				$this->content .= '<div class="solution-inner-item">'; 
					$this->content .= $this->ShowRadioField("createSite", "N", (array("id" => "createSiteN", "class" => "solution-radio") + 
						($createSite != "Y" ? array("checked" => "checked") : array()))); 
					$this->content .= '<h4>'.GetMessage("wiz_site_existing").'</h4>'; 
				if (count($arSites) < 2)
					$this->content .= '<p>'.GetMessage("wiz_site_existing_title").' '.implode("", $arSitesSelect).'</p>'; 
				else
				{
					$this->content .= '<p>'.GetMessage("wiz_site_existing_title");
					$this->content .= "<br />". $this->ShowSelectField("siteID", $arSitesSelect)."</p>";
				}
				$this->content .= '</div>'; 
				$this->content .= '<b class="r1"></b><b class="r1"></b><b class="r3"></b>'; 
			$this->content .= '</div>';
		if (count($arSites) < COption::GetOptionInt("main", "PARAM_MAX_SITES", 100) || COption::GetOptionInt("main", "PARAM_MAX_SITES", 100) <= 0)
		{
			$this->content .= "<div onclick=\"SelectCreateSite(this, 'Y');\" ";
				$this->content .= 'class="solution-item'.($createSite == "Y" ? " solution-item-selected" : "").'">'; 
				$this->content .= '<b class="r3"></b><b class="r1"></b><b class="r1"></b>'; 
				$this->content .= '<div class="solution-inner-item">'; 
					$this->content .= $this->ShowRadioField("createSite", "Y", (array("id" => "createSiteY", "class" => "solution-radio") + 
						($createSite == "Y" ? array("checked" => "checked") : array()))); 
					$this->content .= '<h4>'.GetMessage("wiz_site_new").'</h4>'; 
					$this->content .= '<p>';
						$this->content .= str_replace(
							array(
								"#SITE_ID#", 
								"#SITE_DIR#"), 
							array(
								$this->ShowInputField("text", "siteNewID", array("size" => 2, "maxlength" => 2, "id" => "siteNewID")), 
								$this->ShowInputField("text", "siteFolder", array("id" => "siteFolder"))), 
							GetMessage("wiz_site_new_title")); 
					$this->content .= '</p>'; 
				$this->content .= '</div>'; 
				$this->content .= '<b class="r1"></b><b class="r1"></b><b class="r3"></b>'; 
			$this->content .= '</div>';
		}
		$this->content .= '</div>';
	}
}


class CSelectTemplateWizardStep extends CWizardStep
{
	function InitStep()
	{
		$this->SetStepID("select_template");
		$this->SetTitle(GetMessage("SELECT_TEMPLATE_TITLE"));
		$this->SetSubTitle(GetMessage("SELECT_TEMPLATE_SUBTITLE"));
		if (!defined("WIZARD_DEFAULT_SITE_ID"))
		{
			$this->SetPrevStep("select_site");
			$this->SetPrevCaption(GetMessage("PREVIOUS_BUTTON"));
		}
		else
		{
			$wizard =& $this->GetWizard(); 
			$wizard->SetVar("siteID", WIZARD_DEFAULT_SITE_ID); 
		}

		$this->SetNextStep("select_theme");
		$this->SetNextCaption(GetMessage("NEXT_BUTTON"));
	}

	function OnPostForm()
	{
		$wizard =& $this->GetWizard();

		if ($wizard->IsNextButtonClick())
		{
			$templatesPath = WizardServices::GetTemplatesPath($wizard->GetPath()."/site");
			$arTemplates = WizardServices::GetTemplates($templatesPath);

			$templateID = $wizard->GetVar("templateID");

			if (!array_key_exists($templateID, $arTemplates))
				$this->SetError(GetMessage("wiz_template"));
		}
	}

	function ShowStep()
	{
		$wizard =& $this->GetWizard();

		$templatesPath = WizardServices::GetTemplatesPath($wizard->GetPath()."/site");
		$arTemplates = WizardServices::GetTemplates($templatesPath);

		if (empty($arTemplates))
			return;

		$templateID = $wizard->GetVar("templateID");
		if(isset($templateID) && array_key_exists($templateID, $arTemplates)){
		
			$defaultTemplateID = $templateID;
			$wizard->SetDefaultVar("templateID", $templateID);
			
		} else {
		
			$defaultTemplateID = COption::GetOptionString("main", "wizard_template_id", "", $wizard->GetVar("siteID")); 
			if (!(strlen($defaultTemplateID) > 0 && array_key_exists($defaultTemplateID, $arTemplates)))
			{
				if (strlen($defaultTemplateID) > 0 && array_key_exists($defaultTemplateID, $arTemplates))
					$wizard->SetDefaultVar("templateID", $defaultTemplateID);
				else
					$defaultTemplateID = "";
			}
		}

		CFile::DisableJSFunction();

		$this->content .= '<div id="solutions-container" class="inst-template-list-block">';
		foreach ($arTemplates as $templateID => $arTemplate)
		{
			if ($defaultTemplateID == "")
			{
				$defaultTemplateID = $templateID;
				$wizard->SetDefaultVar("templateID", $defaultTemplateID);
			}

			$this->content .= '<div class="inst-template-description">';
			$this->content .= $this->ShowRadioField("templateID", $templateID, Array("id" => $templateID, "class" => "inst-template-list-inp"));
			if ($arTemplate["SCREENSHOT"] && $arTemplate["PREVIEW"])
				$this->content .= CFile::Show2Images($arTemplate["PREVIEW"], $arTemplate["SCREENSHOT"], 150, 150, ' class="inst-template-list-img"');
			else
				$this->content .= CFile::ShowImage($arTemplate["SCREENSHOT"], 150, 150, ' class="inst-template-list-img"', "", true);

			$this->content .= '<label for="'.$templateID.'" class="inst-template-list-label">'.$arTemplate["NAME"].'<p>'.$arTemplate["DESCRIPTION"].'</p></label>';
			$this->content .= "</div>";

		}
		
		$this->content .= '</div>'; 
	}
}

class CSelectThemeWizardStep extends CWizardStep
{
	function InitStep()
	{
		$this->SetStepID("select_theme");
		$this->SetTitle(GetMessage("SELECT_THEME_TITLE"));
		$this->SetSubTitle(GetMessage("SELECT_THEME_SUBTITLE"));
		$this->SetPrevStep("select_template");
		$this->SetPrevCaption(GetMessage("PREVIOUS_BUTTON"));
		$this->SetNextStep("site_settings");
		$this->SetNextCaption(GetMessage("NEXT_BUTTON"));
	}

	function OnPostForm()
	{
		$wizard =& $this->GetWizard();

		if ($wizard->IsNextButtonClick())
		{
			$templateID = $wizard->GetVar("templateID");
			$themeVarName = $templateID."_themeID";
			$themeID = $wizard->GetVar($themeVarName);

			$templatesPath = WizardServices::GetTemplatesPath($wizard->GetPath()."/site");
			$arThemes = WizardServices::GetThemes($templatesPath."/".$templateID."/themes");

			if (!array_key_exists($themeID, $arThemes))
				$this->SetError(GetMessage("wiz_template_color"));
		}
	}

	function ShowStep()
	{
		$wizard =& $this->GetWizard();
		$templateID = $wizard->GetVar("templateID");

		$templatesPath = WizardServices::GetTemplatesPath($wizard->GetPath()."/site");
		$arThemes = WizardServices::GetThemes($templatesPath."/".$templateID."/themes");

		if (empty($arThemes))
			return;

		$themeVarName = $templateID."_themeID";
		$ThemeID = $wizard->GetVar($templateID."_themeID");

		if(isset($ThemeID) && array_key_exists($ThemeID, $arThemes)){
			$defaultThemeID = $ThemeID;
			$wizard->SetDefaultVar($themeVarName, $ThemeID);
		} else {
			$defaultThemeID = COption::GetOptionString("main", "wizard_".$templateID."_theme_id", "", $wizard->GetVar("siteID")); 
	
			if (!(strlen($defaultThemeID) > 0 && array_key_exists($defaultThemeID, $arThemes)))
			{
				$defaultThemeID = COption::GetOptionString("main", "wizard_".$templateID."_theme_id", "");
				if (strlen($defaultThemeID) > 0 && array_key_exists($defaultThemeID, $arThemes))
					$wizard->SetDefaultVar($themeVarName, $defaultThemeID);
				else
					$defaultThemeID = "";
			}
		}

		$this->content = 
'<script type="text/javascript">
function SelectTheme(element, solutionId, imageUrl)
{
	var container = document.getElementById("solutions-container");
	var anchors = container.getElementsByTagName("SPAN");
	for (var i = 0; i < anchors.length; i++)
	{
		if (anchors[i].parentNode == container)
			anchors[i].className = "inst-template-color";
	}
	element.className = "inst-template-color inst-template-color-selected";
	var hidden = document.getElementById("selected-solution");
	if (!hidden) 
	{
		hidden = document.createElement("INPUT");
		hidden.type = "hidden"
		hidden.id = "selected-solution";
		hidden.name = "selected-solution";
		container.appendChild(hidden);
	}
	hidden.value = solutionId;

	var preview = document.getElementById("solution-preview");
	if (!imageUrl)
		preview.style.display = "none";
	else 
	{
		document.getElementById("solution-preview-image").src = imageUrl;
		preview.style.display = "";
	}
}
</script>'.
'<div id="html_container">'.
'<div class="inst-template-color-block" id="solutions-container">';
		$ii = 0;
		$arDefaultTheme = array(); 
		foreach ($arThemes as $themeID => $arTheme)
		{
			if ($defaultThemeID == "")
			{
				$defaultThemeID = $themeID;
				$wizard->SetDefaultVar($themeVarName, $defaultThemeID);
			}
			if ($defaultThemeID == $themeID)
				$arDefaultTheme = $arTheme;
			$ii++;

			$this->content .= '
					<span class="inst-template-color'.($defaultThemeID == $themeID ? " inst-template-color-selected" : "").'" ondblclick="SubmitForm(\'next\');"  onclick="SelectTheme(this, \''.$themeID.'\', \''.$arTheme["SCREENSHOT"].'\');">
						<span class="inst-templ-color-img">'.CFile::ShowImage($arTheme["PREVIEW"], 70, 70, ' border="0" class="solution-image"').'</span>
						<span class="inst-templ-color-name">'.$ii.'. '.$arTheme["NAME"].'</span>
					</span>';
		}
		
		$this->content .= $this->ShowHiddenField($themeVarName, $defaultThemeID, array("id" => "selected-solution"));  
		$this->content .= 
			'</div>'.
			'<div id="solution-preview">'.
				'<b class="r3"></b><b class="r1"></b><b class="r1"></b>'.
					'<div class="solution-inner-item">'.
						CFile::ShowImage($arDefaultTheme["SCREENSHOT"], 450, 450, ' border="0" id="solution-preview-image"').
					'</div>'.
				'<b class="r1"></b><b class="r1"></b><b class="r3"></b>'.
			'</div>'.
		'</div>';
	}
}

class CSiteSettingsWizardStep extends CWizardStep
{
	function InitStep()
	{
		$this->SetStepID("site_settings");
		$this->SetTitle(GetMessage("wiz_settings"));
		$this->SetSubTitle(GetMessage("wiz_settings"));
		$this->SetNextStep("data_install");
		$this->SetPrevStep("select_theme");
		$this->SetNextCaption(GetMessage("wiz_install"));
		$this->SetPrevCaption(GetMessage("PREVIOUS_BUTTON"));
		$wizard =& $this->GetWizard();

		if(defined("WIZARD_DEFAULT_SITE_ID"))
		{
			$wizard =& $this->GetWizard(); 
			$wizard->SetVar("siteID", WIZARD_DEFAULT_SITE_ID); 
		}

		$WIZARD_SITE_ROOT_PATH = $_SERVER["DOCUMENT_ROOT"];
		if($wizard->GetVar("createSite")=="Y")
		{
			$WIZARD_SITE_DIR =  $wizard->GetVar("siteFolder");
		}
		else
		{
			$siteID = WizardServices::GetCurrentSiteID($wizard->GetVar("siteID"));
			$rsSites = CSite::GetByID($siteID);
			if ($arSite = $rsSites->Fetch())
			{
				if($arSite["DOC_ROOT"] <> '')
				{
					$WIZARD_SITE_ROOT_PATH = $arSite["DOC_ROOT"];
				}
				$WIZARD_SITE_DIR = $arSite["DIR"];
			}
			else
			{
				$WIZARD_SITE_DIR =  "/";
			}
		}

		define("WIZARD_SITE_ROOT_PATH", $WIZARD_SITE_ROOT_PATH);
		define("WIZARD_SITE_DIR", $WIZARD_SITE_DIR);
		define("WIZARD_SITE_PATH", str_replace("//", "/", WIZARD_SITE_ROOT_PATH."/".WIZARD_SITE_DIR."/"));
	}

	function GetFileContent($filename, $default_value)
	{
		if(!file_exists($filename))
			return $default_value; 
		$f = fopen($filename, "rb");
		if(!$f)
			return $default_value; 

		if(filesize($filename) > 0)
			$res = fread($f, filesize($filename));
		else 
			$res = '';

		fclose($f);

		return $res;
	}

	function GetFileContentImgSrc($filename, $default_value)
	{
		$siteLogo = $this->GetFileContent($filename, false);
		if($siteLogo!==false)
		{
			if(preg_match("/src\\s*=\\s*(\\S+)[ \t\r\n\\/>]*/i", $siteLogo, $reg))
				$siteLogo = "/".trim($reg[1], "\"' />");
			else
				$siteLogo = "";
		}
		else
			$siteLogo = $default_value;

		return $siteLogo;
	}
}

class CDataInstallWizardStep extends CWizardStep
{
	var $repeatCurrentService = false;

	function CorrectServices(&$arServices)
	{
	}

	function InitStep()
	{
		$this->SetStepID("data_install");
		$this->SetTitle(GetMessage("wiz_install_data"));
		$this->SetSubTitle(GetMessage("wiz_install_data"));
	}

	function ShowStep()
	{
		$wizard =& $this->GetWizard();

		$arServices = WizardServices::GetServices($_SERVER["DOCUMENT_ROOT"].$wizard->GetPath(), "/site/services/");

		list($firstService, $stage, $status) = $this->GetFirstStep($arServices);

		$this->content .= '
			<div class="instal-load-block" id="result">
				<div class="instal-load-label" id="status"></div>
				<div class="instal-progress-bar-outer" style="width: 670px;">
					<div class="instal-progress-bar-alignment">
						<div class="instal-progress-bar-inner" id="indicator">
							<div class="instal-progress-bar-inner-text" style="width: 670px;" id="percent"></div>
						</div>
						<span id="percent2">0%</span>
					</div>
				</div>
			</div>

			<div id="error_container" style="display:none">
				<div id="error_notice">
					<div class="inst-note-block inst-note-block-red">
						<div class="inst-note-block-icon"></div>
						<div class="inst-note-block-label">'.GetMessage("INST_ERROR_OCCURED").'</div><br />
						<div class="inst-note-block-text">'.GetMessage("INST_ERROR_NOTICE").'<div id="error_text"></div></div>
					</div>
				</div>

				<div id="error_buttons" align="center">
				<br /><input type="button" value="'.GetMessage("INST_RETRY_BUTTON").'" id="error_retry_button" onclick="" class="instal-btn instal-btn-inp" />&nbsp;<input type="button" id="error_skip_button" value="'.GetMessage("INST_SKIP_BUTTON").'" onclick="" class="instal-btn instal-btn-inp" />&nbsp;</div>
			</div>
		'.$this->ShowHiddenField("nextStep", $firstService).'
		'.$this->ShowHiddenField("nextStepStage", $stage).'
		<iframe style="display:none;" id="iframe-post-form" name="iframe-post-form" src="javascript:\'\'"></iframe>';

		$wizard =& $this->GetWizard();

		$formName = $wizard->GetFormName();
		$NextStepVarName = $wizard->GetRealName("nextStep");

		$this->content .= '
		<script type="text/javascript">
			var ajaxForm = new CAjaxForm("'.$formName.'", "iframe-post-form", "'.$NextStepVarName.'");
			ajaxForm.Post("'.$firstService.'", "'.$stage.'", "'.$status.'");
		</script>';
	}

	function OnPostForm()
	{
		$wizard =& $this->GetWizard();
		$serviceID = $wizard->GetVar("nextStep");
		$serviceStage = $wizard->GetVar("nextStepStage");

		if ($serviceID == "finish")
		{
			$wizard->SetCurrentStep("finish");
			return;
		}

		$defSiteName = GetMessage("wiz_site_default_name");
		if(GetMessage("wiz_site_name")!="")
			$defSiteName = GetMessage("wiz_site_name");
		elseif($wizard->wizardName!="")
			$defSiteName = $wizard->wizardName;
		
		$res = false;
		$site_id = $wizard->GetVar("siteID"); 
		if($site_id!="")
		{
			$db_res = CSite::GetList($by="sort", $order="desc", array("LID" => $site_id));
			if($db_res)
				$res = $db_res->Fetch();
		}

		if($wizard->GetVar("siteCreate")=="Y")
		{
			if(!$res)
			{
				$culture = CultureTable::getRow(array('filter'=>array(
					"=FORMAT_DATE" => (LANGUAGE_ID=="en"? "MM/DD/YYYY":"DD.MM.YYYY"),
					"=FORMAT_DATETIME" => (LANGUAGE_ID=="en"? "MM/DD/YYYY H:MI:SS T":"DD.MM.YYYY HH:MI:SS"),
					"=FORMAT_NAME" => CSite::GetDefaultNameFormat(),
					"=CHARSET" => (defined("BX_UTF")? "UTF-8" : (LANGUAGE_ID=="ru"? "windows-1251":"ISO-8859-1")),
				)));

				if($culture)
				{
					$cultureId = $culture["ID"];
				}
				else
				{
					$addResult = CultureTable::add(array(
						"NAME" => $site_id,
						"CODE" => $site_id,
						"FORMAT_DATE" => (LANGUAGE_ID=="en"? "MM/DD/YYYY":"DD.MM.YYYY"),
						"FORMAT_DATETIME" => (LANGUAGE_ID=="en"? "MM/DD/YYYY H:MI:SS T":"DD.MM.YYYY HH:MI:SS"),
						"FORMAT_NAME" => CSite::GetDefaultNameFormat(),
						"CHARSET" => (defined("BX_UTF")? "UTF-8" : (LANGUAGE_ID=="ru"? "windows-1251":"ISO-8859-1")),
					));
					$cultureId = $addResult->getId();
				}

				$arFields = array(
					"LID" => $site_id,
					"ACTIVE" => "Y",
					"SORT" => 100,
					"DEF" => "N",
					"NAME" => $defSiteName,
					"DIR" => $wizard->GetVar("siteFolder"),
					"SITE_NAME" => $defSiteName,
					"SERVER_NAME" => $_SERVER["SERVER_NAME"],
					"EMAIL" => COption::GetOptionString("main", "email_from"),
					"LANGUAGE_ID" => LANGUAGE_ID,
					"DOC_ROOT" => "",
					"CULTURE_ID" => $cultureId,
				);
				$obSite = new CSite;

				$result = $obSite->Add($arFields);
				if (!$result)
				{
					echo $obSite->LAST_ERROR;
					die(); 
				}
			}
			$wizard->SetVar("siteCreate", "N");
		}

		$pattern = '/^(.*):(.*)\((.*)\)/';
		preg_match($pattern, $res["NAME"], $matches);

		if($res && (count($matches) > 0 || $res["NAME"] == $site_id) && $site_id != "s1")
		{
			$templateID = $wizard->GetVar("templateID");
			$themeVarName = $templateID."_themeID";
			$themeID = $wizard->GetVar($themeVarName);
			
			$templatesPath = WizardServices::GetTemplatesPath($wizard->GetPath()."/site");
			$arTemplates = WizardServices::GetTemplates($templatesPath);

			$templatesPath = WizardServices::GetTemplatesPath($wizard->GetPath()."/site");
			$arThemes = WizardServices::GetThemes($templatesPath."/".$templateID."/themes", $templatesPath."/".$templateID);

			$siteNemNew = $defSiteName . ": " . $arTemplates[$templateID]["NAME"]  . ' (' . $arThemes[$themeID]["NAME"] . ')';
			
			$obSite = new CSite;
			$obSite->Update($site_id, Array("NAME"=>$siteNemNew, "SITE_NAME"=>$siteNemNew));
		}
		elseif($res["NAME"] == GetMessage("MAIN_DEFAULT_SITE_NAME"))
		{
			$SiteNAME = $defSiteName . " (" . GetMessage("MAIN_DEFAULT_SITE_NAME") . ")";

			$obSite = new CSite;
			$obSite->Update($site_id, Array("NAME"=>$SiteNAME, "SITE_NAME"=>$defSiteName));
		}

		CModule::IncludeModule('fileman');
		COption::SetOptionString("fileman", "different_set", "Y");

		$arMenuTypes = GetMenuTypes($site_id);

		if(count($arMenuTypes) == 0){
			$arMenuTypes = Array(
				'left' => GetMessage("WIZ_MENU_LEFT"),
				'top' => GetMessage("WIZ_MENU_TOP"),
				'bottom' => GetMessage("WIZ_MENU_BOTTOM")
			);
		}else{

			if(!$arMenuTypes['left'] || $arMenuTypes['left'] == GetMessage("WIZ_MENU_LEFT_DEFAULT"))
				$arMenuTypes['left']   = GetMessage("WIZ_MENU_LEFT");

			if(!$arMenuTypes['top'] || $arMenuTypes['top'] == GetMessage("WIZ_MENU_TOP_DEFAULT"))
				$arMenuTypes['top'] = GetMessage("WIZ_MENU_TOP");

			if(!$arMenuTypes['bottom'])
				$arMenuTypes['bottom'] = GetMessage("WIZ_MENU_BOTTOM");		
		}

		SetMenuTypes($arMenuTypes, $site_id);

		$arServices = WizardServices::GetServices($_SERVER["DOCUMENT_ROOT"].$wizard->GetPath(), "/site/services/");

		$this->CorrectServices($arServices);

		if ($serviceStage == "skip")
			$success = true;
		else
			$success = $this->InstallService($serviceID, $serviceStage);

		if (!$this->repeatCurrentService) 
		{
			list($nextService, $nextServiceStage, $stepsComplete, $status) = $this->GetNextStep($arServices, $serviceID, $serviceStage);
		}

		if ($nextService == "finish")
		{
			$response = "window.ajaxForm.StopAjax(); window.ajaxForm.SetStatus('100'); window.ajaxForm.Post('".$nextService."', '".$nextServiceStage."','".$status."');";
			COption::SetOptionString("main", "wizard_first" . substr($wizard->GetID(), 7)  . "_" . $wizard->GetVar("siteID"), "Y", false);
		}
		else
		{
			$arServiceID = array_keys($arServices);
			$lastService = array_pop($arServiceID);
			$stepsCount = $arServices[$lastService]["POSITION"];
			if (array_key_exists("STAGES", $arServices[$lastService]) && is_array($arServices[$lastService]))
				$stepsCount += count($arServices[$lastService]["STAGES"])-1;
			$percent = round($stepsComplete/$stepsCount * 100);
			$response = ($percent ? "window.ajaxForm.SetStatus('".$percent."');" : "")." window.ajaxForm.Post('".$nextService."', '".$nextServiceStage."','".$status."');";
		}
		die("[response]".$response."[/response]");
	}

	function InstallService($serviceID, $serviceStage)
	{
		$wizard =& $this->GetWizard();

		$siteID = WizardServices::GetCurrentSiteID($wizard->GetVar("siteID"));
	
		define("WIZARD_SITE_ID", $siteID);

		$WIZARD_SITE_ROOT_PATH = $_SERVER["DOCUMENT_ROOT"];

		$rsSites = CSite::GetByID($siteID);
		if ($arSite = $rsSites->Fetch())
		{
			if($arSite["DOC_ROOT"] <> '')
			{
				$WIZARD_SITE_ROOT_PATH = $arSite["DOC_ROOT"];
			}
			define("WIZARD_SITE_DIR", $arSite["DIR"]);
		}
		else
		{
			define("WIZARD_SITE_DIR", "/");
		}

		define("WIZARD_SITE_ROOT_PATH", $WIZARD_SITE_ROOT_PATH);
		define("WIZARD_SITE_PATH", str_replace("//", "/", WIZARD_SITE_ROOT_PATH."/".WIZARD_SITE_DIR."/"));

		$wizardPath = $wizard->GetPath();
		define("WIZARD_RELATIVE_PATH", $wizardPath);
		define("WIZARD_ABSOLUTE_PATH", $_SERVER["DOCUMENT_ROOT"].$wizardPath);

		$templatesPath = WizardServices::GetTemplatesPath(WIZARD_RELATIVE_PATH."/site");
		$templateID = $wizard->GetVar("templateID");

		define("WIZARD_TEMPLATE_ID", $templateID);
		define("WIZARD_TEMPLATE_RELATIVE_PATH", $templatesPath."/".WIZARD_TEMPLATE_ID);
		define("WIZARD_TEMPLATE_ABSOLUTE_PATH", $_SERVER["DOCUMENT_ROOT"].WIZARD_TEMPLATE_RELATIVE_PATH);

		$themeID = $wizard->GetVar($templateID."_themeID");
		define("WIZARD_THEME_ID", $themeID);
		define("WIZARD_THEME_RELATIVE_PATH", WIZARD_TEMPLATE_RELATIVE_PATH."/themes/".WIZARD_THEME_ID);
		define("WIZARD_THEME_ABSOLUTE_PATH", $_SERVER["DOCUMENT_ROOT"].WIZARD_THEME_RELATIVE_PATH);

		$servicePath = WIZARD_RELATIVE_PATH."/site/services/".$serviceID;
		define("WIZARD_SERVICE_RELATIVE_PATH", $servicePath);
		define("WIZARD_SERVICE_ABSOLUTE_PATH", $_SERVER["DOCUMENT_ROOT"].$servicePath);
		define("WIZARD_IS_RERUN", $_SERVER["PHP_SELF"] != "/index.php");

		define("WIZARD_SITE_LOGO", intval($wizard->GetVar("siteLogo")));
		define("WIZARD_INSTALL_DEMO_DATA", $wizard->GetVar("installDemoData") == "Y");
		define("WIZARD_REINSTALL_DATA", false);
		define("WIZARD_FIRST_INSTAL", COption::GetOptionString("main", "wizard_first" . substr($wizard->GetID(), 7)  . "_" . $wizard->GetVar("siteID"), false, $wizard->GetVar("siteID")));

		$dbUsers = CGroup::GetList($by="id", $order="asc", Array("ACTIVE" => "Y"));
		while($arUser = $dbUsers->Fetch())
			define("WIZARD_".$arUser["STRING_ID"]."_GROUP", $arUser["ID"]);

		if (!file_exists(WIZARD_SERVICE_ABSOLUTE_PATH."/".$serviceStage))
			return false;

		$langSubst = LangSubst(LANGUAGE_ID);
		if ($langSubst <> LANGUAGE_ID)
		{
			if (file_exists(($fname = WIZARD_SERVICE_ABSOLUTE_PATH."/lang/".$langSubst."/".$serviceStage)))
				__IncludeLang($fname, false, true);
		}

		if (file_exists(($fname = WIZARD_SERVICE_ABSOLUTE_PATH."/lang/".LANGUAGE_ID."/".$serviceStage)))
			__IncludeLang($fname, false, true);

		@set_time_limit(3600);
		/** @noinspection PhpUnusedLocalVariableInspection */
		global $DB, $DBType, $APPLICATION, $USER, $CACHE_MANAGER;
		include(WIZARD_SERVICE_ABSOLUTE_PATH."/".$serviceStage);

		return true;
	}

	function GetNextStep(&$arServices, &$currentService, &$currentStage)
	{
		$nextService = "finish";
		$nextServiceStage = "finish";
		$status = GetMessage("INSTALL_SERVICE_FINISH_STATUS");

		if (!array_key_exists($currentService, $arServices))
			return Array($nextService, $nextServiceStage, 0, $status); //Finish

		if ($currentStage != "skip" && array_key_exists("STAGES", $arServices[$currentService]) && is_array($arServices[$currentService]["STAGES"]))
		{
			$stageIndex = array_search($currentStage, $arServices[$currentService]["STAGES"]);
			if ($stageIndex !== false && isset($arServices[$currentService]["STAGES"][$stageIndex+1]))
				return Array(
					$currentService,
					$arServices[$currentService]["STAGES"][$stageIndex+1],
					$arServices[$currentService]["POSITION"]+ $stageIndex,
					$arServices[$currentService]["NAME"]
				); //Current step, next stage
		}

		$arServiceID = array_keys($arServices);
		$serviceIndex = array_search($currentService, $arServiceID);

		if (!isset($arServiceID[$serviceIndex+1]))
			return Array($nextService, $nextServiceStage, 0, $status); //Finish

		$nextServiceID = $arServiceID[$serviceIndex+1];
		$nextServiceStage = "index.php";
		if (array_key_exists("STAGES", $arServices[$nextServiceID]) && is_array($arServices[$nextServiceID]["STAGES"]) && isset($arServices[$nextServiceID]["STAGES"][0]))
			$nextServiceStage = $arServices[$nextServiceID]["STAGES"][0];

		return Array($nextServiceID, $nextServiceStage, $arServices[$nextServiceID]["POSITION"]-1, $arServices[$nextServiceID]["NAME"]); //Next service
	}

	function GetFirstStep(&$arServices)
	{
		foreach ($arServices as $serviceID => $arService)
		{
			$stage = "index.php";
			if (array_key_exists("STAGES", $arService) && is_array($arService["STAGES"]) && isset($arService["STAGES"][0]))
				$stage = $arService["STAGES"][0];
			return Array($serviceID, $stage, $arService["NAME"]);
		}

		return Array("service_not_found", "finish", GetMessage("INSTALL_SERVICE_FINISH_STATUS"));
	}
}

class CFinishWizardStep extends CWizardStep
{
	function InitStep()
	{
		$this->SetStepID("finish");
		$this->SetNextStep("finish");
		$this->SetTitle(GetMessage("FINISH_STEP_TITLE"));
		$this->SetNextCaption(GetMessage("wiz_go"));
	}

	function ShowStep()
	{
		$wizard =& $this->GetWizard();
		
		$siteID = WizardServices::GetCurrentSiteID($wizard->GetVar("siteID"));
		$rsSites = CSite::GetByID($siteID);
		$siteDir = "/"; 
		if ($arSite = $rsSites->Fetch())
			$siteDir = $arSite["DIR"]; 

		$wizard->SetFormActionScript(str_replace("//", "/", $siteDir."/?finish"));

		$this->CreateNewIndex();
		
		COption::SetOptionString("main", "wizard_solution", $wizard->solutionName, false, $siteID); 
		
		$this->content .= GetMessage("FINISH_STEP_CONTENT");
		
		if ($wizard->GetVar("installDemoData") == "Y")
			$this->content .= GetMessage("FINISH_STEP_REINDEX");		
		
	}

	function CreateNewIndex()
	{
		$wizard =& $this->GetWizard();
		$siteID = WizardServices::GetCurrentSiteID($wizard->GetVar("siteID"));

		define("WIZARD_SITE_ID", $siteID);

		$WIZARD_SITE_ROOT_PATH = $_SERVER["DOCUMENT_ROOT"];

		$rsSites = CSite::GetByID($siteID);
		if ($arSite = $rsSites->Fetch())
		{
			if($arSite["DOC_ROOT"] <> '')
			{
				$WIZARD_SITE_ROOT_PATH = $arSite["DOC_ROOT"];
			}
			define("WIZARD_SITE_DIR", $arSite["DIR"]);
		}
		else
		{
			define("WIZARD_SITE_DIR", "/");
		}

		define("WIZARD_SITE_ROOT_PATH", $WIZARD_SITE_ROOT_PATH);
		define("WIZARD_SITE_PATH", str_replace("//", "/", WIZARD_SITE_ROOT_PATH."/".WIZARD_SITE_DIR."/"));

		//Copy index page
		CopyDirFiles(
			WIZARD_SITE_PATH."/_index.php",
			WIZARD_SITE_PATH."/index.php",
			$rewrite = true,
			$recursive = true,
			$delete_after_copy = true
		);

		bx_accelerator_reset();
	}
}
