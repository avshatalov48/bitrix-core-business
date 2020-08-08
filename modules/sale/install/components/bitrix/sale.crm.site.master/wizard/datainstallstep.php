<?php
namespace Bitrix\Sale\CrmSiteMaster\Steps;

use Bitrix\Main,
	Bitrix\Main\Config\Option,
	Bitrix\Main\Localization\Loc,
	Bitrix\Sale\CrmSiteMaster\Tools\SitePatcher;

Loc::loadMessages(__FILE__);

if (Main\IO\File::isFileExists($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/install/wizards/bitrix/portal/wizard.php"))
{
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/install/wizards/bitrix/portal/wizard.php");
}
elseif (Main\IO\File::isFileExists($_SERVER["DOCUMENT_ROOT"]."/bitrix/wizards/bitrix/portal/wizard.php"))
{
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/wizards/bitrix/portal/wizard.php");
}

/** @noinspection PhpUndefinedClassInspection */
/**
 * Class DataInstallStep
 * Install portal and services
 *
 * @package Bitrix\Sale\CrmSiteMaster\Steps
 */
class DataInstallStep extends \DataInstallStep
{
	private $currentStepName = __CLASS__;

	/** @var \SaleCrmSiteMaster */
	private $component = null;

	/**
	 * Initialization step id and title
	 */
	public function initStep()
	{
		define("ADDITIONAL_INSTALL", "Y");

		$this->component = $this->GetWizard()->GetVar("component");

		$this->SetStepID($this->currentStepName);
		$this->SetTitle(Loc::getMessage("SALE_CSM_WIZARD_DATAINSTALLSTEP_TITLE"));
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
		$this->prepareWizardVars();

		if ($this->GetErrors())
		{
			return false;
		}

		ob_start();
		?>
		<div class="adm-crm-site-master-timer">
			<img class="adm-crm-site-master-timer-image" src="<?=$this->component->getPath()?>/wizard/images/timer.svg" alt="">
		</div>

		<div class="adm-crm-site-master-timer-description">
			<?=Loc::getMessage("SALE_CSM_WIZARD_DATAINSTALLSTEP_DESCR")?>
		</div>

		<div id="error_container" style="display: none; margin-top: 25px">
			<div class="ui-alert ui-alert-danger ui-alert-inline ui-alert-icon-danger">
				<span class="ui-alert-message" id="error_text"></span>
			</div>

			<div class="adm-crm-slider-buttons" id="error_buttons">
				<div class="ui-btn-container ui-btn-container-center">
					<button type="button" id="error_retry_button" class="ui-btn ui-btn-primary" onclick="">
						<?=Loc::getMessage("SALE_CSM_WIZARD_DATAINSTALLSTEP_RETRY_BUTTON")?>
					</button>
					<button type="button" id="error_skip_button" class="ui-btn ui-btn-primary" onclick="">
						<?=Loc::getMessage("SALE_CSM_WIZARD_DATAINSTALLSTEP_SKIP_BUTTON")?>
					</button>
				</div>
			</div>
		</div>
		<?
		echo $this->ShowHiddenField("nextStep", "main");
		echo $this->ShowHiddenField("nextStepStage", "database");
		?><iframe style="display:none;" id="iframe-post-form" name="iframe-post-form" src="javascript:''"></iframe><?
		$wizard =& $this->GetWizard();
		$arServices = \WizardServices::GetServices($_SERVER["DOCUMENT_ROOT"].$wizard->GetPath(), "/site/services/");
		list($firstService, $stage, $status) = $this->GetFirstStep($arServices);
		$formName = $wizard->GetFormName();
		$nextStepVarName = $wizard->GetRealName("nextStep");
		$messages = Loc::loadLanguageFile(__FILE__);
		?>
		<script type="text/javascript">
			BX.message(<?=\CUtil::PhpToJSObject($messages)?>);
			var ajaxForm = new CAjaxForm("<?=$formName?>", "iframe-post-form", "<?=$nextStepVarName?>");
			ajaxForm.Post("<?=$firstService?>", "<?=$stage?>", "<?=$status?>");
			ajaxForm.SetEventBeforeUnloadWindow();
		</script>
		<?
		$content = ob_get_contents();
		ob_end_clean();

		$this->content = $content;

		return true;
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
	public function onPostForm()
	{
		$wizard =& $this->GetWizard();
		$serviceID = $wizard->GetVar("nextStep");
		if ($serviceID !== "finish")
		{
			/** @noinspection PhpVariableNamingConventionInspection */
			global $APPLICATION;
			$APPLICATION->RestartBuffer();
		}
		parent::OnPostForm();

		$this->prepareSite();

		$steps = $this->component->getSteps($this->currentStepName);
		if (isset($steps["NEXT_STEP"]))
		{
			$this->GetWizard()->SetCurrentStep($steps["NEXT_STEP"]);
		}

		return true;
	}

	/**
	 * @param $serviceID
	 * @param $serviceStage
	 * @return bool
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\LoaderException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public function installService($serviceID, $serviceStage)
	{
		$wizard =& $this->GetWizard();

		$siteId = SitePatcher::getInstance()->getCrmSiteId();
		$wizardSiteRootPath = SitePatcher::getInstance()->getCrmSitePath();

		define("WIZARD_SITE_ID", $siteId);
		define("WIZARD_SITE_ROOT_PATH", $wizardSiteRootPath);

		$siteFolder = $wizard->GetVar("siteFolder");
		if ($siteFolder)
		{
			define("WIZARD_SITE_DIR", $siteFolder);
		}
		else
		{
			define("WIZARD_SITE_DIR", "/");
		}

		define("WIZARD_SITE_PATH", str_replace("//", "/", WIZARD_SITE_ROOT_PATH."/".WIZARD_SITE_DIR));

		$wizardPath = $wizard->GetPath();
		define("WIZARD_RELATIVE_PATH", $wizardPath);
		define("WIZARD_ABSOLUTE_PATH", $wizardSiteRootPath.$wizardPath);

		$templatesPath = \WizardServices::GetTemplatesPath("/bitrix/modules/intranet/install");
		$templateID = $wizard->GetVar("templateID");
		define("WIZARD_TEMPLATE_ID", "bitrix24");
		define("WIZARD_TEMPLATE_RELATIVE_PATH", $templatesPath."/".WIZARD_TEMPLATE_ID);
		define("WIZARD_TEMPLATE_ABSOLUTE_PATH", $wizardSiteRootPath.WIZARD_TEMPLATE_RELATIVE_PATH);

		$themeID = $wizard->GetVar($templateID."_themeID");
		define("WIZARD_THEME_ID", $themeID);
		define("WIZARD_THEME_RELATIVE_PATH", WIZARD_TEMPLATE_RELATIVE_PATH."/themes/".WIZARD_THEME_ID);
		define("WIZARD_THEME_ABSOLUTE_PATH", $wizardSiteRootPath.WIZARD_THEME_RELATIVE_PATH);

		$servicePath = WIZARD_RELATIVE_PATH."/site/services/".$serviceID;
		define("WIZARD_SERVICE_RELATIVE_PATH", $servicePath);
		define("WIZARD_SERVICE_ABSOLUTE_PATH", $wizardSiteRootPath.$servicePath);
		define("WIZARD_IS_RERUN", false);
		define("WIZARD_B24_TO_CP", false);

		$firstStep = Option::get(
			"main",
			"wizard_first".mb_substr($wizard->GetID(), 7)."_".$siteId,
			false,
			$siteId
		);
		define("WIZARD_FIRST_INSTAL", $firstStep);

		define("WIZARD_SITE_NAME", $wizard->GetVar("siteName"));

		define("WIZARD_INSTALL_DEMO_DATA", $wizard->GetVar("installDemoData") == "Y");
		define("WIZARD_INSTALL_MOBILE", $wizard->GetVar("installMobile") == "Y");

		if($firstStep == "N" || $wizard->GetVar("installDemoData") == "Y")
		{
			Option::set("main", "wizard_clear_exec", "N", $siteId);
		}

		define("WIZARD_NEW_2011", false);

		$dbGroupUsers = \CGroup::GetList($by="id", $order="asc", Array("ACTIVE" => "Y"));
		$arGroupsId = Array("ADMIN_SECTION", "SUPPORT", "CREATE_GROUPS", "PERSONNEL_DEPARTMENT", "DIRECTION", "MARKETING_AND_SALES", "RATING_VOTE", "RATING_VOTE_AUTHORITY");

		while ($arGroupUser = $dbGroupUsers->Fetch())
		{
			if(in_array($arGroupUser["STRING_ID"], $arGroupsId))
			{
				define("WIZARD_".$arGroupUser["STRING_ID"]."_GROUP", $arGroupUser["ID"]);
			}
			else
			{
				if(mb_substr($arGroupUser["STRING_ID"], -2) == $siteId)
				{
					define("WIZARD_".mb_substr($arGroupUser["STRING_ID"], 0, -3)."_GROUP", $arGroupUser["ID"]);
				}
			}
		}

		if (!Main\IO\File::isFileExists(WIZARD_SERVICE_ABSOLUTE_PATH."/".$serviceStage))
			return false;

		if (LANGUAGE_ID != "en" && LANGUAGE_ID != "ru")
		{
			if (Main\IO\File::isFileExists(WIZARD_SERVICE_ABSOLUTE_PATH."/lang/en/".$serviceStage))
			{
				Loc::loadMessages(WIZARD_SERVICE_ABSOLUTE_PATH."/lang/en/".$serviceStage);
			}
		}

		if (Main\IO\File::isFileExists(WIZARD_SERVICE_ABSOLUTE_PATH."/lang/".LANGUAGE_ID."/".$serviceStage))
		{
			Loc::loadMessages(WIZARD_SERVICE_ABSOLUTE_PATH."/lang/".LANGUAGE_ID."/".$serviceStage);
		}

		@set_time_limit(3600);

		/** @noinspection PhpUnusedLocalVariableInspection */
		/** @noinspection PhpVariableNamingConventionInspection */
		global $DB, $DBType, $APPLICATION, $USER, $CACHE_MANAGER;
		include(WIZARD_SERVICE_ABSOLUTE_PATH."/".$serviceStage);

		return true;
	}

	/**
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\LoaderException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	private function prepareWizardVars()
	{
		$wizard = $this->GetWizard();
		$sitePatcher = SitePatcher::getInstance();

		$siteId = $sitePatcher->getCrmSiteId();
		$siteName = $sitePatcher->getCrmSiteName();
		$siteDir = $sitePatcher->getCrmSiteDir();

		Option::set("main", "site_name", $siteName, $siteId);

		$wizard->SetVar("siteID", $siteId);
		$wizard->SetVar("templateID", "bitrix24");
		$wizard->SetVar("installDemoData", "Y");
		$wizard->SetVar("installStructureData", "N");
		$wizard->SetVar("siteName", $siteName);
		$wizard->SetVar("siteFolder", $siteDir);
		$wizard->SetVar("allowGuests","N");
		$wizard->SetVar("allowGroup", "N");
	}

	/**
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	private function prepareSite()
	{
		$sitePatcher = SitePatcher::getInstance();

		SitePatcher::updateSiteTemplateConditions();
		$sitePatcher->addUrlRewrite();
		$sitePatcher->deleteFiles();

		// patch modules
		$sitePatcher->patchDisk();
		$sitePatcher->patchDav();
		$sitePatcher->patchTimeman();
		$sitePatcher->patchMeeting();
		$sitePatcher->patchImconnector();
		$sitePatcher->patchImopenlines();
		$sitePatcher->patchVoximplant();
		$sitePatcher->patchMobile();
		$sitePatcher->patchIm();

		Option::set("crm", "crm_shop_enabled", "Y");

		$sitePatcher->createDepartment(Loc::getMessage("SALE_CSM_WIZARD_DATAINSTALLSTEP_DEPARTMENT_NAME"));
		$sitePatcher->prepareCrmCatalog();
		$sitePatcher->addSiteToCatalog();
	}
}