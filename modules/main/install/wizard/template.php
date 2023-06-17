<?php

class WizardTemplate extends CWizardTemplate
{
	function GetLayout()
	{
		global $arWizardConfig;
		$wizard = &$this->wizard;

		$formName = CUtil::JSEscape($wizard->GetFormName());
		$nextButtonID = CUtil::JSEscape($wizard->GetNextButtonID());
		$prevButtonID = CUtil::JSEscape($wizard->GetPrevButtonID());

		$wizardPath = $wizard->GetPath();

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
		if($stepSubTitle <> '')
			$stepSubTitle = '<div class="inst-cont-title-review">'.$stepSubTitle.'</div>';

		$alertText = GetMessage("MAIN_WIZARD_WANT_TO_CANCEL");
		$loadingText = GetMessage("MAIN_WIZARD_WAIT_WINDOW_TEXT");

		$BX_ROOT = BX_ROOT;
		$productVersion = SM_VERSION;

		//wizard customization file
		$bxProductConfig = array();
		if(file_exists($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/.config.php"))
		{
			include($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/.config.php");
			if(defined("INSTALL_UTF_PAGE") && is_array($bxProductConfig["product_wizard"]))
			{
				foreach($bxProductConfig["product_wizard"] as $key=>$val)
					$bxProductConfig["product_wizard"][$key] = mb_convert_encoding($val, INSTALL_CHARSET, "utf-8");
			}
		}

		$title = $bxProductConfig["product_wizard"]["product_name"] ?? $arWizardConfig["productName"] ?? InstallGetMessage("INS_TITLE3");

		$titleSub = "";
		if($title == InstallGetMessage("INS_TITLE3"))
			$titleSub = '<div class="inst-title-label">'.InstallGetMessage("INS_TITLE2").'</div>';

		$title = str_replace("#VERS#", $productVersion , $title);
		$browserTitle = strip_tags(str_replace(Array("<br>", "<br />"), " ",$title));

		if(isset($bxProductConfig["product_wizard"]["copyright"]))
			$copyright = $bxProductConfig["product_wizard"]["copyright"];
		else
		{
			$copyright = InstallGetMessage("COPYRIGHT");
			if (isset($arWizardConfig["copyrightText"]))
				$copyright .= $arWizardConfig["copyrightText"];
		}
		$copyright = str_replace("#CURRENT_YEAR#", date("Y") , $copyright);

		$support = $bxProductConfig["product_wizard"]["links"] ?? $arWizardConfig["supportText"] ?? InstallGetMessage("SUPPORT");

		if(file_exists($_SERVER["DOCUMENT_ROOT"]."/readme.php") || file_exists($_SERVER["DOCUMENT_ROOT"]."/readme.html"))
			$support = InstallGetMessage("SUPPORT_README").$support;
		
		//Images
		$logoImage = "";
		$boxImage = "";

		if(isset($bxProductConfig["product_wizard"]["logo"]))
		{
			$logoImage = $bxProductConfig["product_wizard"]["logo"];
		}
		else
		{
			if (isset($arWizardConfig["imageLogoSrc"]) && file_exists($_SERVER["DOCUMENT_ROOT"].$arWizardConfig["imageLogoSrc"]))
				$logoImage = '<img src="'.$arWizardConfig["imageLogoSrc"].'" alt="" />';
			elseif (file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/images/install/".LANGUAGE_ID."/logo.png"))
				$logoImage = '<img src="/bitrix/images/install/'.LANGUAGE_ID.'/logo.png" alt="" />';
			elseif (file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/images/install/".LANGUAGE_ID."/logo.gif"))
				$logoImage = '<img src="/bitrix/images/install/'.LANGUAGE_ID.'/logo.gif" alt="" />';
			elseif (file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/images/install/en/logo.gif"))
				$logoImage = '<img src="/bitrix/images/install/en/logo.gif" alt="" />';
		}

		if(isset($bxProductConfig["product_wizard"]["product_image"]))
		{
			$boxImage = $bxProductConfig["product_wizard"]["product_image"];
		}
		else
		{
			if (isset($arWizardConfig["imageBoxSrc"]) && file_exists($_SERVER["DOCUMENT_ROOT"].$arWizardConfig["imageBoxSrc"]))
				$boxImage = '<img src="'.$arWizardConfig["imageBoxSrc"].'" alt="" />';
			elseif (file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/images/install/".LANGUAGE_ID."/box.jpg"))
				$boxImage = '<img src="/bitrix/images/install/'.LANGUAGE_ID.'/box.jpg" alt="" />';
			elseif (file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/images/install/en/box.jpg"))
				$boxImage = '<img src="/bitrix/images/install/en/box.jpg" alt="" />';
		}

		$strNavigation = "";

		$arSteps = $wizard->GetWizardSteps();
		$currentStep = $wizard->GetCurrentStepID();

		$currentSuccess = false;
		$stepNumber = 1;

		foreach ($arSteps as $stepID => $stepObject)
		{
			if ($stepID == $currentStep)
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

		$currentStep = $wizard->GetCurrentStepID();
		$jsBeforeOnload = "";
		if ($currentStep == "create_modules")
		{
			$jsBeforeOnload .= "var warningBeforeOnload = '".InstallGetMessage("INS_BEFORE_USER_EXIT")."';\n";
			$jsBeforeOnload .= "window.onbeforeunload = OnBeforeUserExit;";
		}

		$jsCode = "";
		$jsCode = file_get_contents($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/install/wizard/script.js");

		$instructionText = InstallGetMessage("GOTO_README");
		$noscriptInfo = InstallGetMessage("INST_JAVASCRIPT_DISABLED");
		$charset = (defined("INSTALL_UTF_PAGE") ? "UTF-8" : INSTALL_CHARSET);


		return <<<HTML
<!DOCTYPE html>
<html>
	<head>
		<title>{$browserTitle}</title>
		<meta http-equiv="Content-Type" content="text/html; charset={$charset}">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<noscript>
			<style type="text/css">
				div {display: none;}
				#noscript {padding: 3em; font-size: 130%; background:white;}
			</style>
			<p id="noscript">{$noscriptInfo}</p>
		</noscript>
		<link rel="stylesheet" href="/bitrix/images/install/installer_style.css">
		<script type="text/javascript">
		<!--
			document.onkeydown = EnterKeyPress;

			function EnterKeyPress(event)
			{
				if (!document.getElementById)
					return;

				if (window.event)
					event = window.event;

				var sourceElement = (event.target? event.target : (event.srcElement? event.srcElement : null));

				if (!sourceElement || sourceElement.tagName.toUpperCase() == "TEXTAREA")
					return;

				var key = (event.keyCode ? event.keyCode : (event.which ? event.which : null) );
				if (!key)
					return;

				if (key == 13)
				{
					CancelBubble(event);
				}
				else if (key == 39 && event.ctrlKey)
				{
					var nextButton = document.forms["{$formName}"].elements["{$nextButtonID}"];
					if (nextButton)
					{
						nextButton.click();
						CancelBubble(event);
					}
				}
				else if (key == 37 && event.ctrlKey)
				{
					var prevButton = document.forms["{$formName}"].elements["{$prevButtonID}"];
					if (prevButton)
					{
						prevButton.click();
						CancelBubble(event);
					}
				}
			}

			{$jsCode}
			{$jsBeforeOnload}
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
									{$titleSub}
									<div class="inst-title">{$title}</div>
								</div>
								<div class="inst-cont-title-wrap">
									<div class="inst-cont-title">{$stepTitle}</div>
									{$stepSubTitle}
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
<script type="text/javascript">PreloadImages();</script>
<div class="instal-bg"><div class="instal-bg-inner"></div></div>
</body>
</html>

HTML;
	}
}
