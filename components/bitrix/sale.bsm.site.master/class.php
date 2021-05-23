<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main,
	Bitrix\Main\Loader,
	Bitrix\Main\Config\Option,
	Bitrix\Main\Localization\Loc,
	Bitrix\Sale\BsmSiteMaster\Tools,
	Bitrix\Sale\BsmSiteMaster\Templates;

Loc::loadMessages(__FILE__);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/wizard.php"); //Wizard API
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/install/wizard/utils.php"); //Wizard utils
require_once("tools/bsmpackage.php");
require_once("tools/modulechecker.php");
require_once("tools/bitrixvmchecker.php");
require_once("tools/agentchecker.php");
require_once("tools/pushchecker.php");
require_once("tools/persontypepreparer.php");
require_once("tools/sitepatcher.php");

/**
 * Class SaleBsmSiteMaster
 */
class SaleBsmSiteMaster extends \CBitrixComponent
{
	/** @var string Full path to wizard directory */
	const WIZARD_DIR = __DIR__."/wizard/";

	/** @var string */
	const IS_SALE_BSM_SITE_MASTER_FINISH = "~IS_SALE_BSM_SITE_MASTER_FINISH";

	const ERROR_TYPE_COMPONENT = "COMPONENT";
	const ERROR_TYPE_WIZARD = "WIZARD";

	const BSM_WIZARD_SITE_ID = "~BSM_WIZARD_SITE_ID";

	/** @var string */
	const IS_SALE_BSM_SITE_MASTER_STUB = "~IS_SALE_BSM_SITE_MASTER_STUB";

	/** @var CWizardBase wizard */
	private $wizard;

	/** @var Tools\ModuleChecker $moduleChecker */
	private $moduleChecker;

	/** @var array variable for wizard */
	private $wizardVar = [];

	/** @var array default steps */
	private $defaultStep = [];

	/** @var array required steps */
	private $requiredStep = [];

	/** @var array error for wizard's step */
	private $wizardStepErrors = [];

	/**
	 * @param $arParams
	 * @return array
	 */
	public function onPrepareComponentParams($arParams)
	{
		$this->defaultStep = $this->getDefaultSteps();
		$this->requiredStep = $this->getRequiredSteps();

		$this->moduleChecker = new Tools\ModuleChecker();
		$this->moduleChecker->setRequiredModules($this->getRequiredModules());

		$this->arResult = [
			"CONTENT" => "",
			"WIZARD_STEPS" => [],
			"ERROR" => [
				self::ERROR_TYPE_COMPONENT => [],
				self::ERROR_TYPE_WIZARD => [],
			],
		];

		return $arParams;
	}

	/**
	 * @return array
	 */
	private function getDefaultSteps()
	{
		return [
			"Bitrix\Sale\BsmSiteMaster\Steps\WelcomeStep" => [
				"SORT" => 100
			],
			"Bitrix\Sale\BsmSiteMaster\Steps\BackupStep" => [
				"SORT" => 200
			],
			"Bitrix\Sale\BsmSiteMaster\Steps\SiteInstructionStep" => [
				"SORT" => 400
			],
			"Bitrix\Sale\BsmSiteMaster\Steps\SiteStep" => [
				"SORT" => 500
			],
			"Bitrix\Sale\BsmSiteMaster\Steps\FinishStep" => [
				"SORT" => 600
			],
		];
	}

	/**
	 * @return array
	 */
	private function getRequiredSteps()
	{
		return [
			"Bitrix\Sale\BsmSiteMaster\Steps\UpdateSystemStep" => [
				"SORT" => 350
			],
			"Bitrix\Sale\BsmSiteMaster\Steps\ModuleStep" => [
				"SORT" => 360
			],
			"Bitrix\Sale\BsmSiteMaster\Steps\ModuleInstallStep" => [
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
				"version" => "20.0.0",
				"name" => Loc::getMessage("SALE_BSM_MODULE_MAIN_NAME"),
			],
			"intranet" => [
				"version" => "19.0.900",
				"name" => Loc::getMessage("SALE_BSM_MODULE_INTRANET_NAME"),
			],
		];
	}

	/**
	 * @return mixed|void
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\LoaderException
	 */
	public function executeComponent()
	{
		/** @noinspection PhpVariableNamingConventionInspection */
		global $APPLICATION;
		$APPLICATION->SetTitle(Loc::getMessage('SALE_BSM_TITLE'));

		$this->checkPermission();
		$this->checkModules();
		$this->checkSession();

		if ($errors = $this->getErrors(self::ERROR_TYPE_COMPONENT))
		{
			ShowError(implode("<br>", $errors));
			return;
		}

		$this->initSteps();

		$this->addStepsToResult();
		$this->includeWizardTemplate();
		$this->includeWizardSteps();

		$this->createWizard();
		$this->controlRequiredSteps();

		$content = $this->wizard->Display();
		$this->arResult['CONTENT'] = $content;

		$this->includeComponentTemplate();
	}

	/**
	 * Create wizard and add steps
	 */
	private function createWizard()
	{
		$bsmPackage = new Tools\BsmPackage();
		$bsmPackage->setId("bitrix:eshop");

		$this->wizard = new CWizardBase(Loc::getMessage("SALE_BSM_TITLE"), $bsmPackage);

		// before AddSteps
		$this->setWizardVariables();

		$this->wizard->AddSteps($this->arResult["WIZARD_STEPS"]); //Add steps
		$this->wizard->DisableAdminTemplate();
		$this->wizard->SetTemplate(new Templates\BsmSiteMasterTemplate());
		$this->wizard->SetReturnOutput();
		$this->wizard->SetFormName("sale_bsm_site_master");
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
	 * @return CWizardBase
	 */
	public function getWizard()
	{
		return $this->wizard;
	}

	/**
	 * @return array
	 */
	private function getWizardVars()
	{
		return $this->wizardVar;
	}

	/**
	 * @return Tools\ModuleChecker
	 */
	public function getModuleChecker()
	{
		return $this->moduleChecker;
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
				$this->addError(Loc::getMessage("SALE_BSM_WIZARD_STEP_NOT_FOUND", [
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
		if (Main\IO\File::isFileExists(self::WIZARD_DIR."template/bsmsitemastertemplate.php"))
		{
			require_once(self::WIZARD_DIR."template/bsmsitemastertemplate.php");
		}
		else
		{
			$this->addError(Loc::getMessage("SALE_BSM_WIZARD_TEMPLATE_NOT_FOUND"), self::ERROR_TYPE_WIZARD);
		}
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
	 * Check additional required step
	 *
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 */
	private function initSteps()
	{
		$notExistModules = $this->moduleChecker->getNotExistModules();
		if ($notExistModules)
		{
			$this->addWizardStep("Bitrix\Sale\BsmSiteMaster\Steps\UpdateSystemStep", 350);
			$this->addWizardVar("not_exist_modules", $notExistModules);
		}
		else
		{
			$installedModules = $this->moduleChecker->checkInstalledModules();
			if ($installedModules["MIN_VERSION"])
			{
				$this->addWizardStep("Bitrix\Sale\BsmSiteMaster\Steps\ModuleStep", 360);
				$this->addWizardVar("min_version_modules", $installedModules["MIN_VERSION"]);
			}
			else
			{
				if ($installedModules["NOT_INSTALL"] || $this->moduleChecker->isModuleInstall())
				{
					$this->addWizardStep("Bitrix\Sale\BsmSiteMaster\Steps\ModuleStep", 360);
					$this->addWizardStep("Bitrix\Sale\BsmSiteMaster\Steps\ModuleInstallStep", 370);

					$this->addWizardVar("not_installed_modules", $installedModules["NOT_INSTALL"]);
				}
			}
		}

		$this->checkBitrixVm();
		$this->checkAgents();
		$this->checkPushServer();

		$this->sortSteps();
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

	private function checkBitrixVm()
	{
		$bitrixVmChecker = new Tools\BitrixVmChecker();
		if (!$bitrixVmChecker->isVm())
		{
			$this->addWizardStep("Bitrix\Sale\BsmSiteMaster\Steps\BitrixVmStep", 300);
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
			$this->addWizardStep("Bitrix\Sale\BsmSiteMaster\Steps\AgentStep", 310);

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

					// after ModuleStep
					$this->addWizardStep("Bitrix\Sale\BsmSiteMaster\Steps\PushAndPullStep", 340);
				}
			}
		}
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
	 * @param $name
	 * @param $value
	 */
	private function addWizardVar($name, $value)
	{
		$this->wizardVar[$name] = $value;
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
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public function setSaleBsmSiteMasterFinish()
	{
		Option::set("sale", self::IS_SALE_BSM_SITE_MASTER_FINISH, "Y");
	}

	/**
	 * @return bool
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public function isSaleBsmSiteMasterFinish()
	{
		return (Option::get("sale", self::IS_SALE_BSM_SITE_MASTER_FINISH, "N") === "Y");
	}

	/**
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public function setSaleBsmSiteMasterStub()
	{
		Option::set("sale", self::IS_SALE_BSM_SITE_MASTER_STUB, "Y");
	}

	/**
	 * @param $siteId
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public static function setBsmSiteId($siteId)
	{
		Option::set("sale", self::BSM_WIZARD_SITE_ID, $siteId);
	}

	/**
	 * @return string
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 */
	public static function getBsmSiteId()
	{
		return Option::get("sale", self::BSM_WIZARD_SITE_ID);
	}

	private function checkPermission()
	{
		/** @noinspection PhpVariableNamingConventionInspection */
		global $USER;
		if (!$USER->IsAdmin())
		{
			$this->addError(Loc::getMessage("SALE_BSM_MODULE_PERMISSION_DENIED"), self::ERROR_TYPE_COMPONENT);
		}
	}

	/**
	 * @throws Main\LoaderException
	 */
	private function checkModules()
	{
		if (!Loader::includeModule("sale"))
		{
			$this->addError(Loc::getMessage("SALE_BSM_MODULE_NOT_INSTALL"), self::ERROR_TYPE_COMPONENT);
		}
	}

	private function checkSession()
	{
		if ($this->request->isPost() && !check_bitrix_sessid())
		{
			$this->addError(Loc::getMessage("SALE_BSM_WIZARD_ERROR_SESSION_EXPIRED"), self::ERROR_TYPE_COMPONENT);
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