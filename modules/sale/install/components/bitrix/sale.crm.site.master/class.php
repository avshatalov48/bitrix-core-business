<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main,
	Bitrix\Main\Loader,
	Bitrix\Main\Config\Option,
	Bitrix\Main\Localization\Loc,
	Bitrix\Sale\Update\CrmEntityCreatorStepper,
	Bitrix\Sale\CrmSiteMaster\Tools,
	Bitrix\Sale\CrmSiteMaster\Templates;

Loc::loadMessages(__FILE__);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/wizard.php"); //Wizard API
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/install/wizard/utils.php"); //Wizard utils
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/update_client.php");
require_once("tools/modulechecker.php");
require_once("tools/persontypepreparer.php");
require_once("tools/crmpackage.php");
require_once("tools/sitepatcher.php");
require_once("tools/agentchecker.php");
require_once("tools/b24connectoruninstaller.php");
require_once("tools/pushchecker.php");
require_once("tools/bitrixvmchecker.php");

/**
 * Class SaleCrmSiteMaster
 */
class SaleCrmSiteMaster extends \CBitrixComponent
{
	/** @var string Full path to wizard directory */
	const WIZARD_DIR = __DIR__."/wizard/";

	/** @var string */
	const CRM_WIZARD_SITE_ID = "~CRM_WIZARD_SITE_ID";

	/** @var string Has the last step been reached? */
	const IS_SALE_CRM_SITE_MASTER_FINISH = "~IS_SALE_CRM_SITE_MASTER_FINISH";

	/** @var string */
	const IS_SALE_CRM_SITE_MASTER_STUB = "~IS_SALE_CRM_SITE_MASTER_STUB";

	const IS_CRM_SITE_MASTER_OPENED = "~IS_CRM_SITE_MASTER_OPENED";

	const ERROR_TYPE_COMPONENT = "COMPONENT";
	const ERROR_TYPE_ORDER = "ORDER";
	const ERROR_TYPE_WIZARD = "WIZARD";

	/** @var CWizardBase wizard */
	private $wizard;

	/** @var Tools\ModuleChecker $moduleChecker */
	private $moduleChecker;

	/** @var array default steps */
	private $defaultStep = [];

	/** @var array required steps */
	private $requiredStep = [];

	/** @var array variable for wizard */
	private $wizardVar = [];

	/** @var array error for wizard's step */
	private $wizardStepErrors = [];

	/**
	 * @param $arParams
	 * @return array
	 */
	public function onPrepareComponentParams($arParams)
	{
		$this->arResult = [
			"CONTENT" => "",
			"WIZARD_STEPS" => [],
			"ERROR" => [
				self::ERROR_TYPE_COMPONENT => [],
				self::ERROR_TYPE_WIZARD => [],
				self::ERROR_TYPE_ORDER => [],
			],
		];

		$this->defaultStep = $this->getDefaultSteps();
		$this->requiredStep = $this->getRequiredSteps();

		$this->moduleChecker = new Tools\ModuleChecker();
		$this->moduleChecker->setRequiredModules($this->getRequiredModules());

		return $arParams;
	}

	/**
	 * @return array
	 */
	private function getDefaultSteps(): array
	{
		$defaultStep = [
			"Bitrix\Sale\CrmSiteMaster\Steps\WelcomeStep" => [
				"SORT" => 100
			],
			"Bitrix\Sale\CrmSiteMaster\Steps\BackupStep" => [
				"SORT" => 200
			],
			"Bitrix\Sale\CrmSiteMaster\Steps\SiteInstructionStep" => [
				"SORT" => 400
			],
			"Bitrix\Sale\CrmSiteMaster\Steps\SiteStep" => [
				"SORT" => 500
			],
		];

		if (Bitrix\Main\ModuleManager::isModuleInstalled("intranet"))
		{
			if ($this->isIntranetWizard())
			{
				$defaultStep["Bitrix\Sale\CrmSiteMaster\Steps\DataInstallStep"]["SORT"] = 600;
			}
			else
			{
				$this->addError(Loc::getMessage("SALE_CSM_INTRANET_PORTAL_WIZARD_ERROR"), self::ERROR_TYPE_COMPONENT);
			}

			$defaultStep["Bitrix\Sale\CrmSiteMaster\Steps\CrmGroupStep"]["SORT"] = 700;
			$defaultStep["Bitrix\Sale\CrmSiteMaster\Steps\FinishStep"]["SORT"] = 800;
		}

		return $defaultStep;
	}

	/**
	 * @return bool
	 */
	private function isIntranetWizard(): bool
	{
		return (
			Main\IO\File::isFileExists($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/install/wizards/bitrix/portal/wizard.php")
			|| Main\IO\File::isFileExists($_SERVER["DOCUMENT_ROOT"]."/bitrix/wizards/bitrix/portal/wizard.php")
		);
	}

	/**
	 * @return array
	 */
	private function getRequiredSteps()
	{
		return [
			"Bitrix\Sale\CrmSiteMaster\Steps\B24ConnectorStep" => [
				"SORT" => 320
			],
			"Bitrix\Sale\CrmSiteMaster\Steps\PersonTypeStep" => [
				"SORT" => 330
			],
			"Bitrix\Sale\CrmSiteMaster\Steps\ActivationKeyStep" => [
				"SORT" => 340
			],
			"Bitrix\Sale\CrmSiteMaster\Steps\UpdateSystemStep" => [
				"SORT" => 350
			],
			"Bitrix\Sale\CrmSiteMaster\Steps\ModuleStep" => [
				"SORT" => 360
			],
			"Bitrix\Sale\CrmSiteMaster\Steps\PushAndPullStep" => [
				"SORT" => 365
			],
			"Bitrix\Sale\CrmSiteMaster\Steps\ModuleInstallStep" => [
				"SORT" => 370
			]
		];
	}

	/**
	 * @return array
	 */
	private function getRequiredModules()
	{
		return [
			"main" => [
				"name" => Loc::getMessage("SALE_CSM_MODULE_MAIN_NAME"),
				"version" => "20.0.650"
			],
			"iblock" => [
				"name" => Loc::getMessage("SALE_CSM_MODULE_IBLOCK_NAME"),
				"version" => ""
			],
			"pull" => [
				"name" => Loc::getMessage("SALE_CSM_MODULE_PULL_NAME"),
				"version" => "19.0.0"
			],
			"socialnetwork" => [
				"name" => Loc::getMessage("SALE_CSM_MODULE_SOCIALNETWORK_NAME"),
				"version" => ""
			],
			"report" => [
				"name" => Loc::getMessage("SALE_CSM_MODULE_REPORT_NAME"),
				"version" => ""
			],
			"lists" => [
				"name" => Loc::getMessage("SALE_CSM_MODULE_LISTS_NAME"),
				"version" => ""
			],
			"webservice" => [
				"name" => Loc::getMessage("SALE_CSM_MODULE_WEBSERVICES_NAME"),
				"version" => ""
			],
			"mail" => [
				"name" => Loc::getMessage("SALE_CSM_MODULE_MAIL_NAME"),
				"version" => ""
			],
			"support" => [
				"name" => Loc::getMessage("SALE_CSM_MODULE_SUPPORT_NAME"),
				"version" => ""
			],
			"workflow" => [
				"name" => Loc::getMessage("SALE_CSM_MODULE_WORKFLOW_NAME"),
				"version" => ""
			],
			"wiki" => [
				"name" => Loc::getMessage("SALE_CSM_MODULE_WIKI_NAME"),
				"version" => ""
			],
			"advertising" => [
				"name" => Loc::getMessage("SALE_CSM_MODULE_ADVERTISING_NAME"),
				"version" => ""
			],
			"intranet" => [
				"name" => Loc::getMessage("SALE_CSM_MODULE_INTRANET_NAME"),
				"version" => "20.0.650"
			],
			"calendar" => [
				"name" => Loc::getMessage("SALE_CSM_MODULE_CALENDAR_NAME"),
				"version" => ""
			],
			"crm" => [
				"name" => Loc::getMessage("SALE_CSM_MODULE_CRM_NAME"),
				"version" => "20.0.487"
			],
			"currency" => [
				"name" => Loc::getMessage("SALE_CSM_MODULE_CURRENCY_NAME"),
				"version" => ""
			],
			"catalog" => [
				"name" => Loc::getMessage("SALE_CSM_MODULE_CATALOG_NAME"),
				"version" => ""
			],
			"tasks" => [
				"name" => Loc::getMessage("SALE_CSM_MODULE_TASKS_NAME"),
				"version" => ""
			],
			"disk" => [
				"name" => Loc::getMessage("SALE_CSM_MODULE_DISK_NAME"),
				"version" => ""
			],
			"im" => [
				"name" => Loc::getMessage("SALE_CSM_MODULE_IM_NAME"),
				"version" => "19.0.850"
			],
			"dav" => [
				"name" => Loc::getMessage("SALE_CSM_MODULE_DAV_NAME"),
				"version" => ""
			],
			"timeman" => [
				"name" => Loc::getMessage("SALE_CSM_MODULE_TIMEMAN_NAME"),
				"version" => "20.0.150"
			],
			"meeting" => [
				"name" => Loc::getMessage("SALE_CSM_MODULE_MEETING_NAME"),
				"version" => ""
			],
			"imconnector" => [
				"name" => Loc::getMessage("SALE_CSM_MODULE_IMCONNECTOR_NAME"),
				"version" => ""
			],
			"mobile" => [
				"name" => Loc::getMessage("SALE_CSM_MODULE_MOBILE_NAME"),
				"version" => ""
			],
			"mobileapp" => [
				"name" => Loc::getMessage("SALE_CSM_MODULE_MOBILEAPP_NAME"),
				"version" => ""
			],
			"voximplant" => [
				"name" => Loc::getMessage("SALE_CSM_MODULE_VOXIMPLANT_NAME"),
				"version" => ""
			],
			"imopenlines" => [
				"name" => Loc::getMessage("SALE_CSM_MODULE_IMOPENLINES_NAME"),
				"version" => ""
			],
			"landing" => [
				"name" => Loc::getMessage("SALE_CSM_MODULE_LANDING_NAME"),
				"version" => "20.0.400"
			],
			"bizproc" => [
				"name" => Loc::getMessage("SALE_CSM_MODULE_BIZPROC_NAME"),
				"version" => ""
			],
			"bizprocdesigner" => [
				"name" => Loc::getMessage("SALE_CSM_MODULE_BIZPROCDESIGNER_NAME"),
				"version" => ""
			],
			"blog" => [
				"name" => Loc::getMessage("SALE_CSM_MODULE_BLOG_NAME"),
				"version" => ""
			],
			"rest" => [
				"name" => Loc::getMessage("SALE_CSM_MODULE_REST_NAME"),
				"version" => ""
			],
		];
	}

	/**
	 * @return Tools\ModuleChecker
	 */
	public function getModuleChecker()
	{
		return $this->moduleChecker;
	}

	/**
	 * @param $name
	 * @param $value
	 */
	private function addWizardVar($name, $value)
	{
		$this->wizardVar[$name] = $value;
	}

	/**
	 * @return array
	 */
	private function getWizardVars()
	{
		return $this->wizardVar;
	}

	/**
	 * @param $stepName
	 * @param $value
	 */
	private function addWizardStepError($stepName, $value)
	{
		$this->wizardStepErrors[$stepName]["ERRORS"][] = $value;
	}

	/**
	 * @param $stepName
	 * @return array
	 */
	public function getWizardStepErrors($stepName)
	{
		return $this->wizardStepErrors[$stepName]["ERRORS"];
	}

	/**
	 * @param $errors
	 * @param $type
	 */
	private function addErrors($errors, $type)
	{
		if (!is_array($errors))
		{
			$errors = [$errors];
		}

		foreach ($errors as $error)
		{
			$this->addError($error, $type);
		}
	}

	/**
	 * @param array|string $error
	 * @param $type
	 */
	private function addError($error, $type)
	{
		$this->arResult["ERROR"][$type] = array_merge($this->arResult["ERROR"][$type], [$error]);
	}

	/**
	 * @param $type
	 * @return array
	 */
	private function getErrors($type)
	{
		return isset($this->arResult["ERROR"][$type]) ? $this->arResult["ERROR"][$type] : [];
	}

	/**
	 * @param $stepName
	 * @param $sort
	 * @param bool $replace
	 */
	private function addWizardStep($stepName, $sort, $replace = false)
	{
		if ($replace)
		{
			$this->defaultStep = [];
		}

		$this->defaultStep[$stepName] = [
			"SORT" => $sort
		];
	}

	/**
	 * Include wizard's step
	 */
	private function includeWizardSteps()
	{
		$steps = $this->arResult["WIZARD_STEPS"];
		foreach ($steps as $step)
		{
			$class = array_pop(explode("\\", $step));
			$stepFile = mb_strtolower($class).".php";
			if (Main\IO\File::isFileExists(self::WIZARD_DIR.$stepFile))
			{
				require_once(self::WIZARD_DIR.$stepFile);
			}
			else
			{
				$this->addError(Loc::getMessage("SALE_CSM_WIZARD_STEP_NOT_FOUND", [
					"#STEP_NAME#" => $step
				]), self::ERROR_TYPE_WIZARD);
			}
		}
	}

	/**
	 * Include wizard's template
	 */
	private function includeWizardTemplate()
	{
		if (Main\IO\File::isFileExists(self::WIZARD_DIR."template/crmsitemastertemplate.php"))
		{
			require_once(self::WIZARD_DIR."template/crmsitemastertemplate.php");
		}
		else
		{
			$this->addError(Loc::getMessage("SALE_CSM_WIZARD_TEMPLATE_NOT_FOUND"), self::ERROR_TYPE_WIZARD);
		}
	}

	/**
	 * Create wizard and add steps
	 *
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 */
	private function createWizard()
	{
		$crmPackage = new Tools\CrmPackage();
		$crmPackage->setId("bitrix:portal");
		if ($crmSiteId = self::getCrmSiteId())
		{
			$crmPackage->setSiteId($crmSiteId);
		}

		$this->wizard = new CWizardBase(Loc::getMessage("SALE_CSM_TITLE"), $crmPackage);

		// before AddSteps
		$this->setWizardVariables();

		$this->wizard->AddSteps($this->arResult["WIZARD_STEPS"]); //Add steps
		$this->wizard->DisableAdminTemplate();
		$this->wizard->SetTemplate(new Templates\CrmSiteMasterTemplate());
		$this->wizard->SetReturnOutput();
		$this->wizard->SetFormName("sale_crm_site_master");
	}

	/**
	 * @return CWizardBase
	 */
	public function getWizard()
	{
		return $this->wizard;
	}

	/**
	 * Set variables for wizard
	 */
	private function setWizardVariables()
	{
		if (!($wizard = $this->getWizard()))
		{
			return;
		}

		$wizard->SetVar("component", $this);
		$wizard->SetVar("modulesRequired", $this->moduleChecker->getRequiredModules());

		foreach ($this->getWizardVars() as $varName => $varValue)
		{
			$wizard->SetVar($varName, $varValue);
		}
	}

	/**
	 * Check additional required step
	 *
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\LoaderException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	private function initSteps()
	{
		$notExistModules = $this->moduleChecker->getNotExistModules();
		if ($notExistModules)
		{
			$notAvailableModule = $this->moduleChecker->checkAvailableModules($notExistModules);
			if ($notAvailableModule["ERROR"])
			{
				$this->addWizardStepError("Bitrix\Sale\CrmSiteMaster\Steps\ActivationKeyStep", $notAvailableModule["ERROR"]);
			}

			if ($notAvailableModule["MODULES"])
			{
				// Activate coupon
				$this->addWizardStep("Bitrix\Sale\CrmSiteMaster\Steps\ActivationKeyStep", 340);
			}
			else
			{
				// Update system
				$this->addWizardStep("Bitrix\Sale\CrmSiteMaster\Steps\UpdateSystemStep", 350);
				$this->addWizardVar("not_exist_modules", $notExistModules);
			}
		}
		else
		{
			$installedModules = $this->moduleChecker->checkInstalledModules();
			if ($installedModules["MIN_VERSION"])
			{
				$this->addWizardStep("Bitrix\Sale\CrmSiteMaster\Steps\ModuleStep", 360);
				$this->addWizardVar("min_version_modules", $installedModules["MIN_VERSION"]);
			}
			else
			{
				if ($installedModules["NOT_INSTALL"] || $this->moduleChecker->isModuleInstall())
				{
					$this->addWizardStep("Bitrix\Sale\CrmSiteMaster\Steps\ModuleStep", 360);
					$this->addWizardStep("Bitrix\Sale\CrmSiteMaster\Steps\ModuleInstallStep", 370);

					$this->addWizardVar("not_installed_modules", $installedModules["NOT_INSTALL"]);
				}
			}
		}

		$this->checkBitrixVm();
		$this->checkAgents();
		$this->checkPersonType();
		$this->checkB24Connection();
		$this->checkPushServer();

		$this->sortSteps();
	}

	/**
	 * Add wizard steps to component's params
	 */
	private function addStepsToResult()
	{
		foreach ($this->defaultStep as $stepName => $step)
		{
			$this->arResult["WIZARD_STEPS"][] = $stepName;
		}
	}

	/**
	 * Sort wizard's step
	 */
	private function sortSteps()
	{
		$arSteps = [];
		foreach ($this->defaultStep as $stepName => $step)
		{
			$arSteps[$stepName] = $step["SORT"];
		}

		// sort step
		array_multisort($arSteps, SORT_ASC, $this->defaultStep);
		unset($arSteps);
	}

	/**
	 * @param string $currentStep
	 * @return array
	 */
	public function getSteps($currentStep)
	{
		$result = [];

		$stepsName = $this->arResult["WIZARD_STEPS"];

		$firstStep = $stepsName[0];
		$lastKey = $stepsName[count($stepsName) - 1];

		if ($firstStep === $currentStep)
		{
			$result["NEXT_STEP"] = $stepsName[1];
		}
		elseif ($lastKey === $currentStep)
		{
			$result["PREV_STEP"] = $stepsName[count($stepsName) - 2];
		}
		else
		{
			$key = array_search($currentStep, $stepsName);
			$result["NEXT_STEP"] = $stepsName[$key+1];
			$result["PREV_STEP"] = $stepsName[$key-1];
		}

		return $result;
	}

	/**
	 * Control required steps
	 */
	private function controlRequiredSteps()
	{
		if ($this->wizard->IsNextButtonClick())
		{
			$nextStepId = $this->wizard->GetNextStepID();
			$nextStepSort = $this->defaultStep[$nextStepId]["SORT"];
			foreach ($this->requiredStep as $stepName => $stepValues)
			{
				if (array_key_exists($nextStepId, $this->requiredStep))
				{
					continue;
				}

				if ($this->isStepExists($stepName))
				{
					if ($nextStepSort >= $stepValues["SORT"])
					{
						$this->setStepImmediately($stepName);
						break;
					}
				}
			}
		}
	}

	/**
	 * @param $stepName
	 * @return bool
	 */
	private function isStepExists($stepName)
	{
		if (in_array($stepName, $this->arResult["WIZARD_STEPS"]))
		{
			return true;
		}

		return false;
	}

	/**
	 * @param $stepName
	 */
	private function setStepImmediately($stepName)
	{
		unset($_REQUEST[$this->wizard->nextButtonID]);
		unset($_REQUEST[$this->wizard->nextStepHiddenID]);

		$this->wizard->SetCurrentStep($stepName);
	}

	/**
	 * @param $siteId
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public static function setCrmSiteId($siteId)
	{
		Option::set("sale", self::CRM_WIZARD_SITE_ID, $siteId);
	}

	/**
	 * @return string
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public static function getCrmSiteId()
	{
		return Option::get("sale", self::CRM_WIZARD_SITE_ID);
	}

	/**
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public function setSaleCrmSiteMasterFinish()
	{
		Option::set("sale", self::IS_SALE_CRM_SITE_MASTER_FINISH, "Y");
	}

	/**
	 * @return bool
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public function isSaleCrmSiteMasterFinish()
	{
		return (Option::get("sale", self::IS_SALE_CRM_SITE_MASTER_FINISH, "N") === "Y");
	}

	/**
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public function setSaleCrmSiteMasterStub()
	{
		Option::set("sale", self::IS_SALE_CRM_SITE_MASTER_STUB, "Y");
	}

	/**
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public function setLandingSmnExtended()
	{
		Option::set("landing", "smn_extended", "Y");
	}

	/**
	 * @return string
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public function getPathToOrderList()
	{
		$site = Main\SiteTable::getList([
			"select" => ["SERVER_NAME"],
			"filter" => ["=LID" => $this->getCrmSiteId()]
		])->fetch();

		$siteUrl = ($this->request->isHttps() ? "https://" : "http://").$site["SERVER_NAME"];
		$pathToOderList = Main\Config\Option::get('crm', 'path_to_order_list', '/shop/orders/');

		return $siteUrl.$pathToOderList;
	}

	/**
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	private function checkPersonType()
	{
		$personTypePreparer = new Tools\PersonTypePreparer();

		$personTypeList = $personTypePreparer->getPersonTypeList();

		if ($personTypeList["NOT_MATCH"])
		{
			$this->addWizardStep("Bitrix\Sale\CrmSiteMaster\Steps\PersonTypeStep", 330);
		}
	}

	/**
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 */
	private function checkAgents()
	{
		$agentChecker = new Tools\AgentChecker();
		$result = $agentChecker->checkAgents();
		if (!$result->isSuccess())
		{
			$this->addWizardStep("Bitrix\Sale\CrmSiteMaster\Steps\AgentStep", 310);

			$errors = $result->getErrors();
			foreach ($errors as $error)
			{
				if ($error->getCode() === Tools\AgentChecker::ERROR_CODE_FAIL)
				{
					$this->addWizardVar("error", $error->getMessage());
					$this->addWizardVar("errorType", Tools\AgentChecker::ERROR_CODE_FAIL);

					break;
				}
				elseif ($error->getCode() === Tools\AgentChecker::ERROR_CODE_WARNING)
				{
					$this->addWizardVar("warning", $error->getMessage());
					$this->addWizardVar("errorType", Tools\AgentChecker::ERROR_CODE_WARNING);

					break;
				}
			}
		}
	}

	private function checkBitrixVm()
	{
		$bitrixVmChecker = new Tools\BitrixVmChecker();
		if (!$bitrixVmChecker->isVm())
		{
			$this->addWizardStep("Bitrix\Sale\CrmSiteMaster\Steps\BitrixVmStep", 300);
		}
	}

	/**
	 * @throws Main\LoaderException
	 */
	private function checkB24Connection()
	{
		$b24connector = new Tools\B24ConnectorUnInstaller();
		if ($b24connector->isModule())
		{
			if ($b24connector->isSiteConnected())
			{
				$this->addWizardStep("Bitrix\Sale\CrmSiteMaster\Steps\B24ConnectorStep", 320);
			}
			else
			{
				$result = $b24connector->uninstallModule();
				if (!$result->isSuccess())
				{
					$this->addWizardStep("Bitrix\Sale\CrmSiteMaster\Steps\B24ConnectorStep", 320);
					$errors = [];
					foreach ($result->getErrors() as $error)
					{
						$errors[] = $error->getMessage();
					}

					$this->addWizardVar("b24connector_error", implode("<br>", $errors));
				}
			}
		}
	}

	private function checkPushServer()
	{
		$pushChecker = new Tools\PushChecker();
		if ($pushChecker->isModuleLoaded())
		{
			$version = $pushChecker->getModuleVersion();
			if ($version && (version_compare($version, "19.0.0") !== -1))
			{
				if (!$pushChecker->isPushActive() && !$pushChecker->isShared())
				{
					$registerResult = $pushChecker->registerSharedServer();
					if (!$registerResult->isSuccess())
					{
						$errorMessages = $registerResult->getErrorMessages();
						$this->addWizardVar("push_error", $errorMessages);
					}
					else
					{
						$this->addWizardVar("push_error", false);
					}

					// after ModuleStep
					$this->addWizardStep("Bitrix\Sale\CrmSiteMaster\Steps\PushAndPullStep", 365);
				}
			}
		}
	}

	private function prepareGrid()
	{
		$gridOptions = new Main\Grid\Options('order_error_list');
		$sort = $gridOptions->GetSorting(['sort' => ['ID' => 'DESC'], 'vars' => ['by' => 'by', 'order' => 'order']]);
		$navParams = $gridOptions->GetNavParams();

		$nav = new Main\UI\PageNavigation('order_error_list');
		$nav->allowAllRecords(true)->setPageSize($navParams['nPageSize'])->initFromUri();

		$errorList = CrmEntityCreatorStepper::getErrors([
			'count_total' => true,
			'offset' => $nav->getOffset(),
			'limit' => $nav->getLimit(),
			"order" => $sort["sort"]
		]);
		$nav->setRecordCount($errorList->getCount());

		$this->arResult["GRID"] = [
			"NAV_OBJECT" => $nav,
			"TOTAL_ROWS_COUNT" => $nav->getRecordCount(),
		];

		$tmpError = [];
		while ($error = $errorList->fetch())
		{
			$tmpError[]["data"] = [
				"ORDER_ID" => $error["ORDER_ID"],
				"ERROR" => $error["ERROR"]
			];
		}

		if ($tmpError)
		{
			$this->addErrors($tmpError, self::ERROR_TYPE_ORDER);
		}
	}

	/**
	 * @return mixed|void
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\LoaderException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public function executeComponent()
	{
		/** @noinspection PhpVariableNamingConventionInspection */
		global $APPLICATION;
		$APPLICATION->SetTitle(Loc::getMessage('SALE_CSM_TITLE'));

		$this->checkPermission();
		$this->checkModules();
		$this->checkSession();

		$this->setMasterOpenOption();

		if ($this->request->get("update-order"))
		{
			CrmEntityCreatorStepper::registerOrderUpdateEventHandler();
			LocalRedirect($APPLICATION->GetCurPageParam("", ["update-order"]));
		}

		if ($errors = $this->getErrors(self::ERROR_TYPE_COMPONENT))
		{
			ShowError(implode("<br>", $errors));
			return;
		}

		if ($this->isSaleCrmSiteMasterFinish()
			|| CrmEntityCreatorStepper::isAgent()
			|| CrmEntityCreatorStepper::isFinished()
		)
		{
			if (CrmEntityCreatorStepper::isFinished())
			{
				$this->prepareGrid();
			}

			if (empty($this->getErrors(self::ERROR_TYPE_ORDER)))
			{
				$this->addWizardStep("Bitrix\Sale\CrmSiteMaster\Steps\FinishStep", 100, true);
			}
		}

		if (!$this->isSaleCrmSiteMasterFinish())
		{
			$this->initSteps();
		}

		$this->addStepsToResult();

		$this->includeWizardTemplate();
		$this->includeWizardSteps();

		if (!$this->getErrors(self::ERROR_TYPE_COMPONENT) && !$this->getErrors(self::ERROR_TYPE_WIZARD))
		{
			$this->createWizard();
			$this->controlRequiredSteps();

			$content = $this->wizard->Display();
			$this->arResult['CONTENT'] = $content;
		}

		if ($wizardErrors = $this->getErrors(self::ERROR_TYPE_WIZARD))
		{
			ShowError(implode("<br>", $wizardErrors));
		}
		else
		{
			$this->includeComponentTemplate();
		}
	}

	private function checkPermission()
	{
		/** @noinspection PhpVariableNamingConventionInspection */
		global $USER;
		if (!$USER->IsAdmin())
		{
			$this->addError(Loc::getMessage("SALE_CSM_ACCESS_DENIED"), self::ERROR_TYPE_COMPONENT);
		}
	}

	private function checkModules()
	{
		if (!Loader::includeModule("sale"))
		{
			$this->addError(Loc::getMessage("SALE_CSM_MODULE_NOT_INSTALL"), self::ERROR_TYPE_COMPONENT);
		}
	}

	private function checkSession()
	{
		if ($this->request->isPost() && !check_bitrix_sessid())
		{
			$this->addError(Loc::getMessage("SALE_CSM_WIZARD_ERROR_SESSION_EXPIRED"), self::ERROR_TYPE_COMPONENT);
		}
	}

	/**
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 */
	private function setMasterOpenOption()
	{
		if (Option::get("sale", self::IS_CRM_SITE_MASTER_OPENED, "N") === "N")
		{
			Option::set("sale", self::IS_CRM_SITE_MASTER_OPENED, "Y");
		}
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		return __CLASS__;
	}
}