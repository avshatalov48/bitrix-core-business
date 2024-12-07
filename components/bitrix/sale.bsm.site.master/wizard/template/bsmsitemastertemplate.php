<?php

namespace Bitrix\Sale\BsmSiteMaster\Templates;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\UI,
	Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class BsmSiteMasterTemplate
 * Template for master
 *
 * @package Bitrix\Sale\BsmSiteMaster\Templates
 */
class BsmSiteMasterTemplate extends \CWizardTemplate
{
	/**
	 * @return string
	 */
	public function getLayout()
	{
		\CUtil::InitJSCore(array("ajax"));
		UI\Extension::load(array("ui.buttons", "ui.forms", "ui.progressbar", "ui.fonts.opensans", "ui.alerts"));

		$wizard = $this->GetWizard();

		$formName = htmlspecialcharsbx($wizard->GetFormName());

		$nextButtonId = htmlspecialcharsbx($wizard->GetNextButtonID());
		$prevButtonId = htmlspecialcharsbx($wizard->GetPrevButtonID());
		$cancelButtonId = htmlspecialcharsbx($wizard->GetCancelButtonID());
		$finishButtonId = htmlspecialcharsbx($wizard->GetFinishButtonID());

		$obStep = $wizard->GetCurrentStep();

		$isShowExtendedErrors = false;
		$arErrors = $obStep->GetErrors();
		$strError = $strJsError = "";
		if (count($arErrors) > 0)
		{
			foreach ($arErrors as $arError)
			{
				$strError .= $arError[0]."<br />";

				if ($arError[1] !== false)
				{
					$strJsError .= ($strJsError <> ""? ", ":"")."{'name':'".\CUtil::addslashes($wizard->GetRealName($arError[1]))."', 'title':'".\CUtil::addslashes(htmlspecialcharsback($arError[0]))."'}";
				}
			}

			if ($strError <> '')
			{
				if (method_exists($obStep, "showExtendedErrors"))
				{
					$strError = $obStep->showExtendedErrors($strError);
					$isShowExtendedErrors = true;
				}
				else
				{
					$strError = '
						<div class="ui-alert ui-alert-danger ui-alert-inline ui-alert-icon-danger">
							<span class="ui-alert-message">'.$strError.'</span>
						</div>';
				}
			}

			$strJsError = '
			<script>
				ShowWarnings(['.$strJsError.']);
			</script>';
		}

		$buttons = '';
		if (method_exists($obStep, "showButtons") && !$isShowExtendedErrors)
		{
			$buttonsResult = $obStep->showButtons();
			if (isset($buttonsResult["NEED_WRAPPER"]) && $buttonsResult["NEED_WRAPPER"] === true)
			{
				$buttons = '<div class="adm-bsm-site-master-buttons">';
				if (isset($buttonsResult["CENTER"]) && $buttonsResult["CENTER"] === true)
				{
					$buttons .= '<div class="ui-btn-container ui-btn-container-center">';
				}
				$buttons .= $buttonsResult["CONTENT"];
				if ($buttonsResult["CENTER"] === true)
				{
					$buttons .= '</div>';
				}
				$buttons .= '</div>';
			}
			else
			{
				$buttons = $buttonsResult["CONTENT"];
			}
		}

		$stepTitle = $obStep->GetTitle();

		$autoSubmit = "";
		if ($obStep->IsAutoSubmit())
			$autoSubmit = 'setTimeout("AutoSubmit();", 500);';

		$alertText = GetMessageJS("SALE_BSM_WIZARD_TEMPLATE_WANT_TO_CANCEL");
		$loadingText = GetMessageJS("SALE_BSM_WIZARD_TEMPLATE_WAIT_WINDOW_TEXT");

		$componentPath = $wizard->GetVar("component")->getPath()."/";
		$jsCode = file_get_contents($_SERVER["DOCUMENT_ROOT"].$componentPath."/wizard/template/script.js");

		$sessidPost = bitrix_sessid_post();

		return <<<HTML
<script>
	function OnLoad()
	{
		var form = document.forms["{$formName}"];

		var cancelButton = document.forms["{$formName}"].elements["{$cancelButtonId}"];
		var nextButton = document.forms["{$formName}"].elements["{$nextButtonId}"];
		var prevButton = document.forms["{$formName}"].elements["{$prevButtonId}"];
		var finishButton = document.forms["{$formName}"].elements["{$finishButtonId}"];

		if (cancelButton && !nextButton && !prevButton && !finishButton)
		{
			top.WizardWindow.isClosed = true;
			cancelButton.onclick = CloseWindow;
		}
		else if(cancelButton)
		{
			cancelButton.onclick = ConfirmCancel;
		}

		{$autoSubmit}
	}

	function AutoSubmit()
	{
		var nextButton = document.forms["{$formName}"].elements["{$nextButtonId}"];
		if (nextButton)
		{
			var wizard = top.WizardWindow;
			if (wizard)
			{
				wizard.messLoading = "{$loadingText}";
				wizard.ShowWaitWindow();
			}

			nextButton.click();
			nextButton.disabled = true;
		}
	}

	/**
	 * @return {boolean}
	 */
	function ConfirmCancel()
	{
		return (confirm("{$alertText}"));
	}

	function ShowWarnings(warnings)
	{
		var form = document.forms["{$formName}"];
		if(!form)
			return;

		for(var i in warnings)
		{
			var e = form.elements[warnings[i]["name"]];
			if(!e)
				continue;

			var type = (e.type? e.type.toLowerCase():"");
			var bBefore = false;
			if(e.length > 1 && type !== "select-one" && type !== "select-multiple")
			{
				e = e[0];
				bBefore = true;
			}
			if(type === "textarea" || type === "select-multiple")
				bBefore = true;

			var td = e.parentNode;
			var img;
			if(bBefore)
			{
				img = td.insertBefore(new Image(), e);
				td.insertBefore(document.createElement("BR"), e);
			}
			else
			{
				img = td.insertBefore(new Image(), e.nextSibling);
				img.hspace = 2;
				img.vspace = 2;
				img.style.verticalAlign = "bottom";
			}
			img.src = "/bitrix/themes/"+phpVars.ADMIN_THEME_ID+"/images/icon_warn.gif";
			img.title = warnings[i]["title"];
		}
	}

	function CloseWindow()
	{
		if (self.parent.window.WizardWindow)
			self.parent.window.WizardWindow.Close();
	}
	
	{$jsCode}
</script>

<body onload="OnLoad();">
	{#FORM_START#}
	{$sessidPost}
	<div class="adm-bsm-site-master-wrapper">
		<div class="adm-bsm-site-master-title">{$stepTitle}</div>
		<div class="adm-bsm-site-master-content">
			{$strError}
			{#CONTENT#}
		</div>

		{$buttons}
	</div>
	{#FORM_END#}
	{$strJsError}
</body>
HTML;
	}
}