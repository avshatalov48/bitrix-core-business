<?php
namespace Bitrix\Sale\CrmSiteMaster\Steps;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class BitrixVmStep
 * Step of check Bitrix env
 *
 * @package Bitrix\Sale\CrmSiteMaster\Steps
 */
class BitrixVmStep  extends \CWizardStep
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
		$this->SetTitle(Loc::getMessage("SALE_CSM_WIZARD_BITRIXVMSTEP_TITLE"));

		$this->prepareButtons();
	}

	/**
	 * Show step content
	 *
	 * @return bool
	 */
	public function showStep()
	{
		$vmLink = "https://www.1c-bitrix.ru/download/vmbitrix.php";
		$vmLinkInstruction = "https://dev.1c-bitrix.ru/learning/course/index.php?COURSE_ID=37&LESSON_ID=8811";

		ob_start();
		?>
		<div class="ui-alert ui-alert-warning ui-alert-icon-warning">
			<span class="ui-alert-message"><?=Loc::getMessage("SALE_CSM_WIZARD_BITRIXVMSTEP_INFO")?></span>
		</div>
		<div class="ui-alert ui-alert-warning ui-alert-icon-warning">
			<span class="ui-alert-message"><?=Loc::getMessage("SALE_CSM_WIZARD_BITRIXVMSTEP_INFO2")?></span>
		</div>
		<div class="adm-crm-site-master-paragraph"><?=Loc::getMessage("SALE_CSM_WIZARD_BITRIXVMSTEP_VM_LINK_DOWNLOAD", [
			"#VM_LINK#" => $vmLink
		])?></div>
		<div class="adm-crm-site-master-paragraph"><?=Loc::getMessage("SALE_CSM_WIZARD_BITRIXVMSTEP_VM_LINK_DOC", [
			"#VM_LINK_INSTRUCTION#" => $vmLinkInstruction,
		])?></div>
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
