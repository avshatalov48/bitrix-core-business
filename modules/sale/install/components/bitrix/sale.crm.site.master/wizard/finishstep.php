<?php
namespace Bitrix\Sale\CrmSiteMaster\Steps;

use Bitrix\Main,
	Bitrix\Main\Config\Option,
	Bitrix\Main\Localization\Loc,
	Bitrix\Sale\Update\CrmEntityCreatorStepper,
	Bitrix\Sale\CrmSiteMaster\Tools\SitePatcher;

Loc::loadMessages(__FILE__);

if (Main\IO\File::isFileExists($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/install/wizards/bitrix/portal/wizard.php"))
{
	include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/install/wizards/bitrix/portal/wizard.php");
}
elseif (Main\IO\File::isFileExists($_SERVER["DOCUMENT_ROOT"]."/bitrix/wizards/bitrix/portal/wizard.php"))
{
	include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/wizards/bitrix/portal/wizard.php");
}

/** @noinspection PhpUndefinedClassInspection */
/**
 * Class FinishStep
 * Step with finish information
 *
 * @package Bitrix\Sale\CrmSiteMaster\Steps
 */
class FinishStep extends \FinishStep
{
	private $currentStepName = __CLASS__;

	/** @var \SaleCrmSiteMaster */
	private $component = null;

	private $siteId;
	private $sitePath;
	private $siteDir;

	/**
	 * Initialization step id and title
	 */
	public function initStep()
	{
		$this->component = $this->GetWizard()->GetVar("component");

		$this->SetStepID($this->currentStepName);
		$this->SetTitle(Loc::getMessage("SALE_CSM_WIZARD_FINISHSTEP_TITLE"));
	}

	/**
	 * @return bool
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\LoaderException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public function showStep()
	{
		if (!$this->component->isSaleCrmSiteMasterFinish())
		{
			$this->component->setSaleCrmSiteMasterFinish();
			$this->component->setSaleCrmSiteMasterStub();
			$this->component->setLandingSmnExtended();

			$this->siteId = SitePatcher::getCrmSiteId();
			$this->sitePath = SitePatcher::getInstance()->getCrmSitePath();
			$this->siteDir = SitePatcher::getInstance()->getCrmSiteDir();

			SitePatcher::disableRegularArchive();

			$this->createNewIndex();

			// register event for show progress bar and bind agent
			CrmEntityCreatorStepper::registerEventHandler();

			SitePatcher::saveConfig1C();

			// enable composite
			SitePatcher::enableComposite();

			// enable crm shop
			SitePatcher::crmShopEnable();
		}

		ob_start();
		?>
		<div class="adm-crm-site-master-finish">
			<img class="adm-crm-site-master-finish-image" src="<?=$this->component->getPath()?>/wizard/images/smile.svg" alt="">
		</div>

		<div class="adm-crm-site-master-finish-description">
			<?=Loc::getMessage("SALE_CSM_WIZARD_FINISHSTEP_DESCR1")?>
		</div>
		<div class="adm-crm-site-master-finish-description">
			<?=Loc::getMessage("SALE_CSM_WIZARD_FINISHSTEP_DESCR2")?>
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
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public function showButtons()
	{
		ob_start();
		?>
		<a href="<?=$this->component->getPathToOrderList()?>" class="ui-btn ui-btn-primary">
			<?=Loc::getMessage("SALE_CSM_WIZARD_FINISHSTEP_ORDER_LINK")?>
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
	 * Create index.php
	 *
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public function createNewIndex()
	{
		$wizard =& $this->GetWizard();

		define("WIZARD_SITE_PATH", str_replace("//", "/", $this->sitePath."/".$this->siteDir."/"));

		$firstStep = Option::get(
			"main",
			"wizard_first".mb_substr($wizard->GetID(), 7)."_".$this->siteId,
			false,
			$this->siteId
		);
		if (IsModuleInstalled("bitrix24"))
		{
			$firstStep = "Y";
		}

		define("WIZARD_FIRST_INSTAL", $firstStep);

		//Copy index page
		if (WIZARD_FIRST_INSTAL !== "Y" && $wizard->GetVar("templateID") === "bitrix24")
		{
			CopyDirFiles(
				WIZARD_SITE_PATH."_index.php",
				WIZARD_SITE_PATH."index_old.php",
				true,
				true,
				true
			);

			CopyDirFiles(
				WIZARD_SITE_PATH."index_b24.php",
				WIZARD_SITE_PATH."index.php",
				true,
				true,
				true
			);
		}
		else
		{
			CopyDirFiles(
				WIZARD_SITE_PATH."/_index.php",
				WIZARD_SITE_PATH."/index.php",
				true,
				true,
				true
			);
		}

		Option::set("main", "wizard_first".mb_substr($wizard->GetID(), 7)."_".$this->siteId, "Y", $this->siteId);
		Option::set("main", "~wizard_id", mb_substr($wizard->GetID(), 7), $this->siteId);
		Option::set("main", "wizard_solution", $wizard->GetID(), $this->siteId);
	}
}