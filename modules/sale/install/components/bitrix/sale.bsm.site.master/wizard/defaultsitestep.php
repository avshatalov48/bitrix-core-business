<?php
namespace Bitrix\Sale\BsmSiteMaster\Steps;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class DefaultSiteStep
 * Step of check default site
 *
 * @package Bitrix\Sale\BsmSiteMaster\Steps
 */
class DefaultSiteStep extends \CWizardStep
{
	private $currentStepName = __CLASS__;

	/** @var \SaleBsmSiteMaster */
	private $component = null;

	/**
	 * Check step errors
	 */
	private function setStepErrors()
	{
		$errors = $this->component->getWizardStepErrors($this->currentStepName);
		if ($errors)
		{
			foreach ($errors as $error)
			{
				$this->SetError($error);
			}
		}
	}

	/**
	 * Prepare next/prev buttons
	 */
	private function prepareButtons()
	{
		$steps = $this->component->getSteps($this->currentStepName);

		$shortClassName = (new \ReflectionClass($this))->getShortName();

		if (isset($steps["NEXT_STEP"]))
		{
			$this->SetNextStep($steps["NEXT_STEP"]);
			$this->SetNextCaption(Loc::getMessage("SALE_BSM_WIZARD_".mb_strtoupper($shortClassName)."_NEXT"));
		}
		if (isset($steps["PREV_STEP"]))
		{
			$this->SetPrevStep($steps["PREV_STEP"]);
			$this->SetPrevCaption(Loc::getMessage("SALE_BSM_WIZARD_".mb_strtoupper($shortClassName)."_PREV"));
		}
	}

	/**
	 * Initialization step id and title
	 */
	public function initStep()
	{
		$this->component = $this->GetWizard()->GetVar("component");

		$this->SetStepID($this->currentStepName);
		$this->SetTitle(Loc::getMessage("SALE_BSM_WIZARD_DEFAULTSITESTEP_TITLE"));

		$this->prepareButtons();
		$this->setStepErrors();
	}

	/**
	 * Show step content
	 *
	 * @return bool
	 */
	public function showStep()
	{
		ob_start();

		$error = $this->GetWizard()->GetVar("default_site_error");
		?>
		<div class="ui-alert ui-alert-danger ui-alert-icon-danger">
			<span class="ui-alert-message"><?= $error ?></span>
		</div>
		<div class="ui-alert ui-alert-warning ui-alert-icon-warning">
			<span class="ui-alert-message"><?= Loc::getMessage("SALE_BSM_WIZARD_DEFAULTSITESTEP_INFO") ?></span>
		</div>
		<div class="adm-crm-site-master-paragraph">
			<?=Loc::getMessage("SALE_BSM_WIZARD_DEFAULTSITESTEP_SITE_LINK", [
				"#LANG#" => LANGUAGE_ID
			])?>
		</div>
		<?php

		$content = ob_get_contents();
		ob_end_clean();

		$this->content = $content;

		return true;
	}

	/**
	 * @return bool
	 */
	public function onPostForm()
	{
		$wizard =& $this->GetWizard();
		if ($wizard->IsPrevButtonClick())
		{
			return false;
		}

		if (in_array($this->currentStepName, $this->component->arResult["WIZARD_STEPS"]))
		{
			$this->GetWizard()->SetCurrentStep($this->currentStepName);
		}

		return true;
	}

	/**
	 * @return array
	 */
	public function showButtons()
	{
		ob_start();
		if ($this->GetPrevStepID() !== null)
		{
			?>
			<input type="hidden" name="<?=$this->GetWizard()->prevStepHiddenID?>" value="<?=$this->GetPrevStepID()?>">
			<button type="submit" class="ui-btn ui-btn-primary" name="<?=$this->GetWizard()->prevButtonID?>">
				<?=$this->GetPrevCaption()?>
			</button>
			<?
		}
		if ($this->GetNextStepID() !== null)
		{
			?>
			<input type="hidden" name="<?=$this->GetWizard()->nextStepHiddenID?>" value="<?=$this->GetNextStepID()?>">
			<button type="submit" class="ui-btn ui-btn-primary" name="<?=$this->GetWizard()->nextButtonID?>">
				<?=$this->GetNextCaption()?>
			</button>
			<?
		}
		$content = ob_get_contents();
		ob_end_clean();

		return [
			"CONTENT" => $content,
			"NEED_WRAPPER" => true,
			"CENTER" => true,
		];
	}
}