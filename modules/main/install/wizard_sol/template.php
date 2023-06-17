<?php
class WizardTemplate extends CWizardTemplate
{
	function GetLayout()
	{
		global $arWizardConfig;
		$wizard =& $this->GetWizard();

		$formName = htmlspecialcharsbx($wizard->GetFormName());

		$nextButtonID = htmlspecialcharsbx($wizard->GetNextButtonID());
		$prevButtonID = htmlspecialcharsbx($wizard->GetPrevButtonID());
		$cancelButtonID = htmlspecialcharsbx($wizard->GetCancelButtonID());
		$finishButtonID = htmlspecialcharsbx($wizard->GetFinishButtonID());

		$wizardPathCustom = $wizard->GetPath();
		$wizardPath = "/bitrix/images/main/wizard_sol";

		$obStep =& $wizard->GetCurrentStep();
		$arErrors = $obStep->GetErrors();
		$strError = "";
		if (!empty($arErrors))
		{
			foreach ($arErrors as $arError)
				$strError .= $arError[0]."<br />";

			if ($strError <> '')
				$strError = '<div class="inst-note-block inst-note-block-red"><div class="inst-note-block-icon"></div><div class="inst-note-block-text">'.$strError."</div></div>";
		}

		$stepTitle = $obStep->GetTitle();
		$stepSubTitle = $obStep->GetSubTitle();

		$BX_ROOT = BX_ROOT;
		$productVersion = "";

		//wizard customization file
		$bxProductConfig = array();
		if(file_exists($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/.config.php"))
			include($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/.config.php");

		if(isset($bxProductConfig["intranet_wizard"]["product_name"]))
			$title = $bxProductConfig["intranet_wizard"]["product_name"];
		elseif(GetMessage("WIZARD_TITLE_SOL")!="")
			$title = GetMessage("WIZARD_TITLE_SOL");
		else
			$title = GetMessage("WIZARD_TITLE");
		$title = str_replace("#VERS#", $productVersion , $title);

		$copyright = $bxProductConfig["intranet_wizard"]["copyright"] ?? GetMessage("COPYRIGHT");
		$copyright = str_replace("#CURRENT_YEAR#", date("Y") , $copyright);

		$support = $bxProductConfig["intranet_wizard"]["links"] ?? GetMessage("SUPPORT");

		$wizardName = $bxProductConfig["intranet_wizard"]["title"] ?? $wizard->GetWizardName();

		//Images
		$logoImage = "";
		$boxImage = "";

		if(isset($bxProductConfig["intranet_wizard"]["logo"]))
		{
			$logoImage = $bxProductConfig["intranet_wizard"]["logo"];
		}
		else
		{
			if (file_exists($_SERVER["DOCUMENT_ROOT"].$wizardPathCustom."/images/".LANGUAGE_ID."/logo.png"))
				$logoImage = '<img src="'.$wizardPathCustom.'/images/'.LANGUAGE_ID.'/logo.png" alt="" />';
			elseif (file_exists($_SERVER["DOCUMENT_ROOT"].$wizardPathCustom."/images/".LANGUAGE_ID."/logo.gif"))
				$logoImage = '<img src="'.$wizardPathCustom.'/images/'.LANGUAGE_ID.'/logo.gif" alt="" />';
			elseif (file_exists($_SERVER["DOCUMENT_ROOT"].$wizardPathCustom."/images/en/logo.gif"))
				$logoImage = '<img src="'.$wizardPathCustom.'/images/en/logo.gif" alt="" />';
		}

		if(isset($bxProductConfig["intranet_wizard"]["product_image"]))
		{
			$boxImage = $bxProductConfig["intranet_wizard"]["product_image"];
		}
		else
		{
			if (file_exists($_SERVER["DOCUMENT_ROOT"].$wizardPathCustom."/images/".LANGUAGE_ID."/box.jpg"))
				$boxImage = '<img src="'.$wizardPathCustom.'/images/'.LANGUAGE_ID.'/box.jpg" alt="" />';
			elseif (file_exists($_SERVER["DOCUMENT_ROOT"].$wizardPathCustom."/images/en/box.jpg"))
				$boxImage = '<img src="'.$wizardPathCustom.'/images/en/box.jpg" alt="" />';
		}

		$strErrorMessage = "";
		$strWarningMessage = "";
		$strNavigation = "";

		$arSteps = $wizard->GetWizardSteps();
		$currentStepID = $wizard->GetCurrentStepID();
		if ($currentStepID == "ldap_settings" || $currentStepID == "ldap_groups")
				$currentStepID = "site_settings";

		$currentSuccess = false;
		$stepNumber = 1;

		foreach ($arSteps as $stepID => $stepObject)
		{
			if ($stepID == "ldap_settings" || $stepID == "ldap_groups")
				continue;

			if ($stepID == $currentStepID)
			{
				$class = ' inst-active-step';
				$currentSuccess = true;
			}
			elseif ($currentSuccess)
				$class = '';
			else
				$class = ' inst-past-stage';

			$strNavigation .= '
			<div class="inst-sequence-step-item'.$class.'"><span class="inst-sequence-step-num">'.$stepNumber.'</span><span class="inst-sequence-step-text">'.$stepObject->GetTitle().'</span></div>';

			$stepNumber++;
		}

		if ($strNavigation <> '')
			$strNavigation = '<div class="inst-sequence-steps">'.$strNavigation.'</div>';

		$jsCode = "";
		$jsCode = file_get_contents($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/install/wizard_sol/script.js");

		$noscriptInfo = GetMessage("INST_JAVASCRIPT_DISABLED");
		$charset = LANG_CHARSET;

		$currentStep =& $wizard->GetCurrentStep();

		$buttons = "";

		if ($currentStep->GetNextStepID() != null)
			$buttons .= '<a onclick="this.blur(); return SubmitForm(\'next\');" href="" class="button-next"><span id="next-button-caption">'.$currentStep->GetNextCaption().'</span></a>';

		if ($currentStep->GetPrevStepID() != null)
			$buttons .= '<a onclick="this.blur(); return SubmitForm(\'prev\');" href="" class="button-prev"><span id="prev-button-caption">'.$currentStep->GetPrevCaption().'</span></a>';

		return <<<HTML
<!DOCTYPE html>
<html>
	<head>
		<title>{$wizardName}</title>
		<meta http-equiv="Content-Type" content="text/html; charset={$charset}">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<link rel="stylesheet" href="/bitrix/images/install/installer_style.css">
		<style type="text/css">
			
			
			#solution-preview
			{
				margin-top: 10px;
			}
			
			#solution-preview div.solution-inner-item, 
			#solution-preview b 
			{
				background-color:#F7F7F7;
			}
			
			#solution-preview div.solution-inner-item
			{
				padding: 10px;
				text-align: center;
			}
			
			#solution-preview-image
			{
				border: 1px solid #CFCFCF;
				width: 450px;
			}
			
			/* Round Corners */
			.r0, .r1, .r2, .r3, .r4 { overflow: hidden; font-size:1px; display: block; height: 1px;}
			.r4 { margin: 0 4px; }
			.r3 { margin: 0 3px; }
			.r2 { margin: 0 2px; }
			.r1 { margin: 0 1px; }
			div.wizard-input-form-block
			{
				margin-bottom:30px;
			}
			
			div.wizard-input-form-block h4
			{
				font-size:14px;
				margin-bottom:12px;
				color: #5E7CAD;
			}
			
			div.wizard-input-form-block-content
			{
				margin-left: 30px;
				margin-bottom: 25px;
				zoom:1;
			}
			
			div.wizard-input-form-block-content img
			{
				border: solid 1px #D6D6D6;
				margin-bottom: 5px;
			}
			
			div.wizard-input-form-block-content img.no-border
			{
				border: none;
			}		
			
			div.wizard-input-form-field-text input,
			div.wizard-input-form-field-textarea textarea,
			div.wizard-input-form-field-file input
			{
				width: 90% !important;
				border: solid 1px #CECECE;
				background-color: #F5F5F5;
				padding: 3px;

				font: 100%/100% Arial, sans-serif;
				float: left;
			}
			
			div.wizard-input-form-field-desc
			{
				color: rgb(119, 119, 119);
				zoom:1;
			}

			div.wizard-input-form-field
			{
				overflow: hidden;
				margin-bottom: 5px;
			}
		</style>

		<noscript>
			<style type="text/css">
				div {display: none;}
				#noscript {padding: 3em; font-size: 130%; background:white; display:block;}
			</style>
			<p id="noscript">{$noscriptInfo}</p>
		</noscript>

		<script type="text/javascript">
		<!--

			function SubmitForm(button)
			{
				var buttons = {
					"next" : "{$nextButtonID}",
					"prev" : "{$prevButtonID}",
					"cancel" : "{$cancelButtonID}",
					"finish" : "{$finishButtonID}"
				};

				var form = document.forms["{$formName}"];
				if (form)
				{
					hiddenField = document.createElement("INPUT");
					hiddenField.type = "hidden";
					hiddenField.name = buttons[button];
					hiddenField.value = button;
					form.appendChild(hiddenField);
					form.submit();
				}

				return false;

			}
			{$jsCode}
		//-->
		</script>


	</head>

<body id="bitrix_install_template">
<table class="installer-main-table" id="container">
	<tr>
		<td class="installer-main-table-cell">
			<div class="installer-block-wrap">
				<div class="installer-block">
					{#FORM_START#}
					<table class="installer-block-table">
						<tr>
							<td class="installer-block-cell-left">
								<table class="inst-left-side-img-table">
									<tr>
										<td class="inst-left-side-img-cell">{$boxImage}</td>
									</tr>
								</table>
								{$strNavigation}
							</td>
							<td class="installer-block-cell-right">
								<div class="inst-title-block">
									<div class="inst-title">{$title}</div>
								</div>
								<div class="inst-cont-title-wrap">
									<div class="inst-cont-title">{$stepTitle}</div>
								</div>
								<div id="step-content">
									{$strError}
									{#CONTENT#}
								</div>
								<div class="instal-btn-wrap">
									{#BUTTONS#}
								</div>
							</td>
						</tr>
						<tr>
							<td class="installer-block-cell-left installer-block-cell-bottom">{$logoImage}</td>
							<td class="installer-block-cell-right installer-block-cell-bottom"></td>
						</tr>
					</table>
					{#FORM_END#}
				</div>
				<div class="installer-footer">
					<div class="instal-footer-left-side">{$copyright}</div>
					<div class="instal-footer-right-side">{$support}</div>
				</div>
			</div>
		</td>
	</tr>
</table>
<script type="text/javascript">PreloadImages("{$wizardPath}/");</script>
<div class="instal-bg"><div class="instal-bg-inner"></div></div>
</body>
</html>

HTML;
	}
}
?>