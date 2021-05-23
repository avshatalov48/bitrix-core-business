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
 * Class B24ConnectorStep
 * Step of check agents
 *
 * @package Bitrix\Sale\CrmSiteMaster\Steps
 */
class B24ConnectorStep  extends \CWizardStep
{
	private $currentStepName = __CLASS__;

	/** @var \SaleCrmSiteMaster */
	private $component = null;

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
		$this->SetTitle(Loc::getMessage("SALE_CSM_WIZARD_B24CONNECTORSTEP_TITLE"));

		$this->prepareButtons();
	}

	/**
	 * Show step content
	 *
	 * @return bool
	 */
	public function showStep()
	{
		ob_start();
		if ($this->GetWizard()->GetVar("b24connector_error"))
		{
			?>
			<div class="ui-alert ui-alert-danger ui-alert-inline ui-alert-icon-danger">
				<span class="ui-alert-message"><?= Loc::getMessage("SALE_CSM_WIZARD_B24CONNECTORSTEP_ERROR")?></span>
			</div>
			<div class="adm-crm-site-master-paragraph">
				<p><?=Loc::getMessage("SALE_CSM_WIZARD_B24CONNECTORSTEP_UNINSTALL_LINK", [
					"#LANGUAGE_ID#" => LANGUAGE_ID
				])?></p>
			</div>
			<?php
		}
		else
		{
			?>
			<div class="adm-crm-site-master-paragraph">
				<p><?=Loc::getMessage("SALE_CSM_WIZARD_B24CONNECTORSTEP_CONTENT1")?></p>
				<p><?=Loc::getMessage("SALE_CSM_WIZARD_B24CONNECTORSTEP_CONTENT2", [
					"#LANGUAGE_ID#" => LANGUAGE_ID
				])?></p>
			</div>
			<div class="ui-alert ui-alert-danger ui-alert-inline ui-alert-icon-danger">
				<span class="ui-alert-message"><?= Loc::getMessage("SALE_CSM_WIZARD_B24CONNECTORSTEP_WARNING")?></span>
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
			<?php
		}
		if ($this->GetNextStepID() !== null)
		{
			?>
			<input type="hidden" name="<?=$this->GetWizard()->nextStepHiddenID?>" value="<?=$this->GetNextStepID()?>">
			<button type="submit" class="ui-btn ui-btn-primary" name="<?=$this->GetWizard()->nextButtonID?>">
				<?=$this->GetNextCaption()?>
			</button>
			<?php
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
