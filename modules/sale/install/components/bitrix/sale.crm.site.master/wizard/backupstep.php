<?php

namespace Bitrix\Sale\CrmSiteMaster\Steps;

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class BackupStep
 * Step with check backup
 *
 * @package Bitrix\Sale\CrmSiteMaster\Steps
 */
class BackupStep extends \CWizardStep
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
			$this->SetNextCaption(Loc::getMessage("SALE_CSM_WIZARD_".mb_strtoupper($shortClassName) . "_NEXT"));
		}
	}

	/**
	 * Initialization step id, title and next step
	 *
	 * @throws \ReflectionException
	 */
	public function initStep()
	{
		$this->component = $this->GetWizard()->GetVar("component");

		$this->SetStepID($this->currentStepName);
		$this->SetTitle(Loc::getMessage("SALE_CSM_WIZARD_BACKUPSTEP_TITLE"));

		$this->prepareButtons();
	}

	/**
	 * Show step content
	 */
	public function showStep()
	{
		if ($this->GetErrors())
		{
			return false;
		}

		$instructionLink = "https://dev.1c-bitrix.ru/learning/course/index.php?COURSE_ID=35&CHAPTER_ID=04833&LESSON_PATH=3906.4833";
		if (!in_array($this->component->getLanguageId(), ["ru", "ua"]))
		{
			$instructionLink = "https://training.bitrix24.com/support/training/course/index.php?COURSE_ID=20&LESSON_ID=1188";
		}

		ob_start();
		?>
		<div class="adm-crm-site-master-paragraph">
			<p><?= Loc::getMessage("SALE_CSM_WIZARD_BACKUPSTEP_DESCR_TEXT") ?></p>
			<p><?= Loc::getMessage("SALE_CSM_WIZARD_BACKUPSTEP_DESCR_LINK", [
					"#LINK_INSTRUCTION#" => $instructionLink
				]) ?></p>
			<p><?= Loc::getMessage("SALE_CSM_WIZARD_BACKUPSTEP_DESCR_NEXT") ?></p>
		</div>
		<?php
		$content = ob_get_contents();
		ob_end_clean();

		$this->content = $content;

		return true;
	}

	/**
	 * @return array
	 */
	public function showButtons()
	{
		ob_start();
		if ($this->GetNextStepID() !== null)
		{
			?>
			<span class="adm-crm-site-master-checkbox">
				<label class="adm-crm-site-master-checkbox-label"
					   style="display: flex !important;align-items: center;flex-direction: row;">
					<input type="checkbox" id="confirmation_done" value="Y">
					<?= Loc::getMessage("SALE_CSM_WIZARD_BACKUPSTEP_ALL_DONE") ?>
				</label>
			</span>
			<input type="hidden" name="<?= $this->GetWizard()->nextStepHiddenID ?>" value="<?= $this->GetNextStepID() ?>">
			<button type="submit" class="ui-btn ui-btn-primary ui-btn-disabled" name="<?= $this->GetWizard()->nextButtonID ?>" disabled>
				<?= $this->GetNextCaption() ?>
			</button>
			<?php
		}
		$content = ob_get_contents();
		ob_end_clean();

		return [
			"CONTENT" => $content,
			"NEED_WRAPPER" => true,
			"CENTER" => false,
		];
	}
}