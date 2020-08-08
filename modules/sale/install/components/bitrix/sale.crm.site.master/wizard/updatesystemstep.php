<?php
namespace Bitrix\Sale\CrmSiteMaster\Steps;

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class UpdateSystemStep
 * @package Bitrix\Sale\CrmSiteMaster\Steps
 */
class UpdateSystemStep extends \CWizardStep
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
		$this->SetTitle(Loc::getMessage("SALE_CSM_WIZARD_UPDATESYSTEMSTEP_TITLE"));

		$this->prepareButtons();
	}

	/**
	 * @return bool
	 */
	public function showStep()
	{
		$this->installModulesHtml();

		ob_start();
		?>
		<div class="adm-crm-site-master-paragraph-wrapper">
			<div class="adm-crm-site-master-paragraph"><?=Loc::getMessage("SALE_CSM_WIZARD_UPDATESYSTEMSTEP_DESCR2", [
					"#UPDATE_SYSTEM_LINK#" => "/bitrix/admin/update_system.php?lang=".LANGUAGE_ID
				])?>
			</div>
			<div class="adm-crm-site-master-paragraph"><?=Loc::getMessage("SALE_CSM_WIZARD_UPDATESYSTEMSTEP_DESCR3")?></div>
		</div>
		<?
		$content = ob_get_contents();
		ob_end_clean();

		$this->content .= $content;

		return true;
	}

	/**
	 * Prepare html content with modules to be installed
	 */
	private function installModulesHtml()
	{
		$modules = $this->GetWizard()->GetVar("not_exist_modules");

		$rows = [];
		foreach ($modules as $module)
		{
			$rows[]["data"] = [
				"MODULE" => $module["name"],
			];
		};

		/** @noinspection PhpVariableNamingConventionInspection */
		global $APPLICATION;

		ob_start();
		$APPLICATION->IncludeComponent('bitrix:main.ui.grid', '', [
			'GRID_ID' => 'module_load_list',
			'COLUMNS' => [
				[
					'id' => 'MODULE',
					'name' => Loc::getMessage("SALE_CSM_WIZARD_UPDATESYSTEMSTEP_DESCR1"),
					'sort' => 'MODULE',
					'default' => true,
					'resizeable' => false,
				],
			],
			'ROWS' => $rows,
			'SHOW_ROW_CHECKBOXES' => false,
			'AJAX_MODE' => 'N',
			'AJAX_OPTION_JUMP'          => 'N',
			'SHOW_CHECK_ALL_CHECKBOXES' => false,
			'SHOW_ROW_ACTIONS_MENU'     => false,
			'SHOW_GRID_SETTINGS_MENU'   => false,
			'SHOW_NAVIGATION_PANEL'     => false,
			'SHOW_PAGINATION'           => false,
			'SHOW_SELECTED_COUNTER'     => false,
			'SHOW_TOTAL_COUNTER'        => false,
			'SHOW_PAGESIZE'             => false,
			'SHOW_ACTION_PANEL'         => false,
			'ACTION_PANEL'              => [],
			'ALLOW_COLUMNS_SORT'        => false,
			'ALLOW_COLUMNS_RESIZE'      => false,
			'ALLOW_HORIZONTAL_SCROLL'   => false,
			'ALLOW_SORT'                => false,
			'ALLOW_PIN_HEADER'          => false,
			'AJAX_OPTION_HISTORY'       => 'N'
		]);
		$content = ob_get_contents();
		ob_end_clean();

		$this->content .= $content;
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
}