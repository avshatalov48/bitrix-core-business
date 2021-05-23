<?php
namespace Bitrix\Sale\CrmSiteMaster\Steps;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main,
	Bitrix\Main\Application,
	Bitrix\Main\Localization\Loc,
	Bitrix\Sale\CrmSiteMaster\Tools\PushChecker;

Loc::loadMessages(__FILE__);

/**
 * Class PushAndPullStep
 * Step of check agents
 *
 * @package Bitrix\Sale\CrmSiteMaster\Steps
 */
class PushAndPullStep  extends \CWizardStep
{
	private $currentStepName = __CLASS__;

	/** @var \SaleCrmSiteMaster */
	private $component = null;

	/** @var Main\Request */
	private $request;

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
	 * @throws Main\SystemException
	 * @throws \ReflectionException
	 */
	public function initStep()
	{
		$this->component = $this->GetWizard()->GetVar("component");

		$this->SetStepID($this->currentStepName);
		$this->SetTitle(Loc::getMessage("SALE_CSM_WIZARD_PUSHANDPULLSTEP_TITLE"));

		$this->request = Application::getInstance()->getContext()->getRequest();

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
		$errors = $this->GetWizard()->GetVar("push_error");
		if ($errors)
		{
			?>
			<div class="ui-alert ui-alert-warning ui-alert-icon-warning">
				<span class="ui-alert-message">
					<?=Loc::getMessage("SALE_CSM_WIZARD_PUSHANDPULLSTEP_SETUP", [
						"#LANGUAGE_ID#" => LANGUAGE_ID
					]);?>
				</span>
			</div>
			<div class="ui-alert ui-alert-danger ui-alert-icon-danger adm-crm-push-step-error-block">
				<span class="ui-alert-message"><?=Loc::getMessage("SALE_CSM_WIZARD_PUSHANDPULLSTEP_ERROR_DESC")?></span>
				<?php
				foreach ($errors as $error)
				{
					?>
					<div class="adm-crm-push-step-error-item"><?=$error?></div>
					<?php
				}
				?>
			</div>
			<?php
		}
		else
		{
			?>
			<div class="ui-alert ui-alert-success ui-alert-icon-info">
				<span class="ui-alert-message">
					<?=Loc::getMessage("SALE_CSM_WIZARD_PUSHANDPULLSTEP_INFO", [
						"#LANGUAGE_ID#" => LANGUAGE_ID
					]);?>
				</span>
			</div>
			<?php
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

		// from ModuleStep to ModuleInstallStem
		$wizard->SetVar("modules", $wizard->GetVar("modules"));
		$wizard->SetVar("modulesCount", $wizard->GetVar("modulesCount"));

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
