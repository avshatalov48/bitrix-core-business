<?php

namespace Bitrix\Sale\BsmSiteMaster\Steps;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main,
	Bitrix\Main\Localization\Loc,
	Bitrix\Sale\BsmSiteMaster\Tools\SitePatcher;

Loc::loadMessages(__FILE__);

/**
 * Class FinishStep
 * Step with finish information
 *
 * @package Bitrix\Sale\BsmSiteMaster\Steps
 */
class FinishStep extends \CWizardStep
{
	private $currentStepName = __CLASS__;

	/** @var \SaleBsmSiteMaster */
	private $component = null;

	/**
	 * Initialization step id and title
	 */
	public function initStep()
	{
		$this->component = $this->GetWizard()->GetVar("component");

		$this->SetStepID($this->currentStepName);
		$this->SetTitle(Loc::getMessage("SALE_BSM_WIZARD_FINISHSTEP_TITLE"));
	}

	/**
	 * @return bool
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public function showStep()
	{
		if (!$this->component->isSaleBsmSiteMasterFinish())
		{
			$this->component->setSaleBsmSiteMasterFinish();
			$this->component->setSaleBsmSiteMasterStub();

			SitePatcher::unsetG2GroupFromHidePanel();

			// enable composite
			SitePatcher::enableComposite();
		}

		ob_start();
		?>
		<div class="adm-bsm-site-master-finish">
			<img class="adm-bsm-site-master-finish-image" src="<?=$this->component->getPath()?>/wizard/images/smile.svg" alt="">
		</div>

		<div class="adm-bsm-site-master-finish-description">
			<?=Loc::getMessage("SALE_BSM_WIZARD_FINISHSTEP_DESCR1")?>
		</div>
		<div class="adm-bsm-site-master-finish-description">
			<?=Loc::getMessage("SALE_BSM_WIZARD_FINISHSTEP_DESCR2")?>
		</div>
		<?
		$content = ob_get_contents();
		ob_end_clean();

		$this->content = $content;

		return true;
	}

	/**
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public function showButtons()
	{
		ob_start();
		?>
		<a href="<?=$this->getSiteUrl()?>" target="_parent" class="ui-btn ui-btn-primary">
			<?=Loc::getMessage("SALE_BSM_WIZARD_FINISHSTEP_SITE_LINK")?>
		</a>
		<?
		$content = ob_get_contents();
		ob_end_clean();

		return [
			"CONTENT" => $content,
			"NEED_WRAPPER" => true,
			"CENTER" => true,
		];
	}

	/**
	 * @return string
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	private function getSiteUrl()
	{
		$site = Main\SiteTable::getList([
			"select" => ["SERVER_NAME"],
			"filter" => ["=LID" => $this->component->getBsmSiteId()]
		])->fetch();

		$request = Main\Application::getInstance()->getContext()->getRequest();
		return ($request->isHttps() ? "https://" : "http://").$site["SERVER_NAME"];
	}
}