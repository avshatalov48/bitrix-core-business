<?php
namespace Bitrix\Sale\CrmSiteMaster\Steps;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc,
	Bitrix\Sale\CrmSiteMaster\Tools\AgentChecker;

Loc::loadMessages(__FILE__);

/**
 * Class AgentStep
 * Step of check agents
 *
 * @package Bitrix\Sale\CrmSiteMaster\Steps
 */
class AgentStep  extends \CWizardStep
{
	private $currentStepName = __CLASS__;

	/** @var \SaleCrmSiteMaster */
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
	 *
	 * @throws \ReflectionException
	 */
	private function prepareButtons()
	{
		$steps = $this->component->getSteps($this->currentStepName);

		$shortClassName = (new \ReflectionClass($this))->getShortName();

		if (isset($steps["NEXT_STEP"]))
		{
			$this->SetNextStep($steps["NEXT_STEP"]);
			$this->SetNextCaption(Loc::getMessage("SALE_CSM_WIZARD_".mb_strtoupper($shortClassName)."_NEXT"));
		}
		if (isset($steps["PREV_STEP"]))
		{
			$this->SetPrevStep($steps["PREV_STEP"]);
			$this->SetPrevCaption(Loc::getMessage("SALE_CSM_WIZARD_".mb_strtoupper($shortClassName)."_PREV"));
		}
	}

	/**
	 * Initialization step id and title
	 *
	 * @throws \ReflectionException
	 */
	public function initStep()
	{
		$this->component = $this->GetWizard()->GetVar("component");

		$this->SetStepID($this->currentStepName);
		$this->SetTitle(Loc::getMessage("SALE_CSM_WIZARD_AGENTSTEP_TITLE"));

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
		$errorType = $this->GetWizard()->GetVar("errorType");
		ob_start();

		if ($errorType === AgentChecker::ERROR_CODE_FAIL)
		{
			$error = $this->GetWizard()->GetVar("error");
			?>
			<div class="ui-alert ui-alert-danger ui-alert-icon-danger">
				<span class="ui-alert-message"><?=$error?></span>
			</div>
			<div class="adm-crm-site-master-paragraph">
				<?=Loc::getMessage("SALE_CSM_WIZARD_AGENTSTEP_CHECKER_LINK", [
					"#LANG#" => LANGUAGE_ID
				])?>
			</div>
			<?
		}
		elseif ($errorType === AgentChecker::ERROR_CODE_WARNING)
		{
			$warning = $this->GetWizard()->GetVar("warning");
			?>
			<div class="ui-alert ui-alert-warning ui-alert-icon-warning">
				<span class="ui-alert-message"><?=$warning?></span>
			</div>
			<?
		}
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

		$errorType = $this->GetWizard()->GetVar("errorType");
		if (in_array($this->currentStepName, $this->component->arResult["WIZARD_STEPS"]) && $errorType === AgentChecker::ERROR_CODE_FAIL)
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
