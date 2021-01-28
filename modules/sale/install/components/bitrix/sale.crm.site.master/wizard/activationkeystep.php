<?php
namespace Bitrix\Sale\CrmSiteMaster\Steps;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main,
	Bitrix\Main\Application,
	Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Class ActivationKeyStep
 * @package Bitrix\Sale\CrmSiteMaster\Steps
 */
class ActivationKeyStep extends \CWizardStep
{
	/** @var Main\Request */
	private $request;

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
	 * Initialization step id, title and next/prev step
	 *
	 * @throws Main\SystemException
	 * @throws \ReflectionException
	 */
	public function initStep()
	{
		$this->component = $this->GetWizard()->GetVar("component");

		$this->SetStepID($this->currentStepName);
		$this->SetTitle(Loc::getMessage("SALE_CSM_WIZARD_ACTIVATIONKEYSTEP_TITLE"));

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
		?>
		<div class="adm-crm-site-master-paragraph" style="text-align: center;">
			<?=Loc::getMessage("SALE_CSM_WIZARD_ACTIVATIONKEYSTEP_CONTENT")?>
		</div>
		<div class="adm-crm-site-master-separator" style="height: 50px;"></div>

		<div class="adm-crm-site-master-check-key">
			<div class="adm-crm-site-master-check-key-column" id="check_key">
				<div class="ui-ctl ui-ctl-textbox ui-ctl-active">
					<input type="text" class="ui-ctl-element" style="text-transform: uppercase" id="id_key" name="KEY">
				</div>
			</div>
			<div class="adm-crm-site-master-check-key-column">
				<button class="ui-btn ui-btn-primary" id="id_key_btn">
					<?=Loc::getMessage("SALE_CSM_WIZARD_ACTIVATIONKEYSTEP_CHECK_BUTTON")?>
				</button>
			</div>
		</div>

		<div class="adm-crm-site-master-separator" style="height: 50px;"></div>

		<div class="adm-crm-site-master-separator" style="height: 120px;"></div>
		<div class="adm-crm-site-master-buy-key-container">
			<div class="adm-crm-site-master-buy-key-block">
				<div class="adm-crm-site-master-buy-key-icon">
					<svg width="22px" height="21px" viewBox="0 0 22 21" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
						<g id="Page-1" stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
							<g id="02" transform="translate(0, -57)" fill="#2FC6F6">
								<path d="M10.4437866,77.8875732 C4.67584254,77.8875732 0,73.2117307 0,67.4437866 C0,61.6758425 4.67584254,57 10.4437866,57 C16.2117307,57 20.8875732,61.6758425 20.8875732,67.4437866 C20.8875732,73.2117307 16.2117307,77.8875732 10.4437866,77.8875732 Z M9.88552794,66.6580286 L5.01511171,71.5031935 L6.31782462,72.7992378 L6.99843744,72.1227772 L8.30151898,73.4188215 L9.79382688,71.9350616 L8.49025385,70.6390173 L11.1896785,67.9541707 C12.4755576,68.6683944 14.1304896,68.4850173 15.2233947,67.3980143 C16.5428185,66.0851049 16.5428185,63.956928 15.2233947,62.6451185 C13.904831,61.333309 11.7646072,61.3328202 10.4451833,62.6451185 C9.35227822,63.7321826 9.16747534,65.3786049 9.88552794,66.6580286 Z M14.3550792,66.5339114 C13.5165852,67.367425 12.1529513,67.367425 11.3144327,66.5339114 C10.4759142,65.6999457 10.4759142,64.3431871 11.3144327,63.509197 C12.1524352,62.6756834 13.5165607,62.6756834 14.3550792,63.509197 C15.1935977,64.3431627 15.1935977,65.7003979 14.3550792,66.5339114 Z" id="Combined-Shape"></path>
							</g>
						</g>
					</svg>
				</div>
				<?php
				$priceLink = "https://www.bitrix24.ru/prices/self-hosted.php";
				if ($this->component->getLanguageId() === "ua")
				{
					$priceLink = "https://www.bitrix24.ua/prices/self-hosted.php";
				}
				?>
				<div class="adm-crm-site-master-buy-key-link">
					<a href="<?=$priceLink?>" target="_blank"><?=Loc::getMessage("SALE_CSM_WIZARD_ACTIVATIONKEYSTEP_BUY_LINK")?></a>
				</div>
			</div>
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
			<input type="hidden" name="<?=$this->GetWizard()->nextStepHiddenID?>" value="<?=$this->GetNextStepID()?>">
			<input type="hidden" name="<?=$this->GetWizard()->nextButtonID?>" value="<?=$this->GetNextCaption()?>">
			<?php
		}
		$content = ob_get_contents();
		ob_end_clean();

		return [
			"CONTENT" => $content,
			"NEED_WRAPPER" => false,
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

		return true;
	}
}