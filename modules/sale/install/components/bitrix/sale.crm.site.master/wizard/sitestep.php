<?php

namespace Bitrix\Sale\CrmSiteMaster\Steps;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main,
	Bitrix\Main\Application,
	Bitrix\Main\Config\Option,
	Bitrix\Main\Localization\Loc,
	Bitrix\Sale\CrmSiteMaster\Tools;

Loc::loadMessages(__FILE__);

/**
 * Class SiteStep
 * Select or create site
 *
 * @package Bitrix\Sale\CrmSiteMaster\Steps
 */
class SiteStep extends \CWizardStep
{
	const SITE_TEMPLATE_LOGIN = "login";

	const WIZARD_PATH = "intranet/install/wizards/bitrix/";
	const WIZARD_NAME = "portal";

	private $currentStepName = __CLASS__;

	/** @var \SaleCrmSiteMaster */
	private $component = null;

	/** @var Main\Request */
	private $request;

	private $formFieldList = [
		"CRM_SITE",
		"LID",
		"NAME",
		"SERVER_NAME",
		"DOC_ROOT",
		"WIZARD_REWRITE",
	];

	/** @var array site's fields */
	private $fields = [];

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
		$this->SetTitle(Loc::getMessage("SALE_CSM_WIZARD_SITESTEP_TITLE"));

		$this->request = Application::getInstance()->getContext()->getRequest();

		$this->prepareButtons();
	}

	/**
	 * Show step content
	 *
	 * @return bool
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public function showStep()
	{
		$this->initFormFields();
		$this->deleteFormFields();

		if ($this->GetErrors())
		{
			return false;
		}

		ob_start();
		?>
		<div class="adm-crm-site-master-form">
			<div class="adm-crm-site-sitestep-fields">
				<div id="select_site">
					{#SITE_SELECT#}
				</div>
				<div id="create_site" style="display: none">
					{#SITE_FORM#}
				</div>
			</div>
			<div class="adm-crm-site-master-form-row">
				<span class="adm-crm-site-master-checkbox">
					<label class="adm-crm-site-master-checkbox-label">
						<?$wizardRewrite = (isset($this->formFieldList["WIZARD_REWRITE"])
							? htmlspecialcharsbx($this->formFieldList["WIZARD_REWRITE"]) : '');?>
						<input
							type="checkbox"
							name="WIZARD_REWRITE"
							value="Y"
							id="WIZARD_REWRITE"
							<?=($wizardRewrite ? "checked" : "")?>
						>
						<?=Loc::getMessage("SALE_CSM_WIZARD_SITESTEP_MAIN_SITE_WIZARD_REWRITE")?>
					</label>
				</span>
			</div>
		</div>
		<?
		$content = ob_get_contents();
		ob_end_clean();

		$this->content = $content;

		$this->showSelectSiteHtml();
		$this->createNewSiteHtml();

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
			<?
		}
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
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public function onPostForm()
	{
		$wizard =& $this->GetWizard();
		if ($wizard->IsPrevButtonClick())
		{
			$this->saveFormFields();
			return true;
		}

		$crmSite = $this->request->get("CRM_SITE");

		try
		{
			$this->checkSite();

			if ($crmSite === "new")
			{
				$this->setFields();
				$result = $this->createNewSite();
				if (empty($result["ERROR"]))
				{
					$crmSite = $result["LID"];
				}
				else
				{
					$errorMessage = implode("<br>", $result["ERROR"]);
					$this->SetError($errorMessage);
				}
			}
		}
		catch (\Exception $ex)
		{
			$this->SetError($ex->getMessage());
		}

		$this->prepareSite($crmSite);

		$this->component->setCrmSiteId($crmSite);

		if ($this->GetErrors())
		{
			return false;
		}

		return true;
	}

	/**
	 * @throws Main\SystemException
	 */
	private function checkSite()
	{
		if ($this->request->get("CRM_SITE") !== "new")
		{
			$site = Main\SiteTable::getList([
				"select" => ["NAME", "DIR", "DOC_ROOT"],
				"filter" => ["=LID" => $this->request->get("CRM_SITE"), "ACTIVE" => "Y"]
			])->fetch();

			if (!empty($site["DIR"]) && $site["DIR"] !== "/")
			{
				$error = Loc::getMessage("SALE_CSM_WIZARD_SITESTEP_SITE_DIR_ERROR");
				throw new Main\SystemException($error);
			}

			$documentRoot = trim($site["DOC_ROOT"].$site["DIR"]);
		}
		else
		{
			$documentRoot = trim($this->request->get("DOC_ROOT"));
		}

		$documentRoot = Rel2Abs($_SERVER["DOCUMENT_ROOT"], $documentRoot);
		if (rtrim($documentRoot, "/") === $_SERVER["DOCUMENT_ROOT"])
		{
			$error = Loc::getMessage("SALE_CSM_WIZARD_SITESTEP_DOC_ROOT_ERROR");
			throw new Main\SystemException($error);
		}

		$documentRoot = $documentRoot."/";
		if (!$this->isDocumentRootExists($documentRoot))
		{
			$error = Loc::getMessage("SALE_CSM_WIZARD_SITESTEP_DOC_ROOT_NOT_EXISTS");
			throw new Main\SystemException($error);
		}

		if ($missingFiles = $this->getMissingRequiredFileList($documentRoot))
		{
			foreach ($missingFiles as $key => $missingFile)
			{
				$pathTo = $documentRoot.$missingFile;
				if ($missingFile === ".htaccess")
				{
					$missingFile = "htaccess";
				}

				if (file_exists($_SERVER["DOCUMENT_ROOT"].$this->component->getPath()."/wizard/files/".$missingFile))
				{
					$isCopied = CopyDirFiles(
						$_SERVER["DOCUMENT_ROOT"].$this->component->getPath()."/wizard/files/".$missingFile,
						$pathTo,
						true,
						true,
						false
					);
					if ($isCopied)
					{
						unset($missingFiles[$key]);
					}
				}
			}

			if ($missingFiles)
			{
				$error = Loc::getMessage("SALE_CSM_WIZARD_SITESTEP_REQUIRED_FILE_ERROR", [
					"#REQUIRED_FILES#" => implode(", ", $missingFiles)
				]);
				throw new Main\SystemException($error);
			}
		}

		if ($this->isIndexFileExists($documentRoot))
		{
			$error = Loc::getMessage("SALE_CSM_WIZARD_SITESTEP_REWRITE_ERROR");
			throw new Main\SystemException($error);
		}
	}

	/**
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	private function setFields()
	{
		$defaultParam = $this->getDefaultParam();
		$serverName = $this->prepareServerName(trim($this->request->get("SERVER_NAME")));

		/** @var array $arFields */
		$this->fields = array(
			"LID" => trim($this->request->get("LID")),
			"ACTIVE" => "Y",
			"DEF" => "N",
			"SORT" => $this->getSort(),
			"NAME" => trim($this->request->get("NAME")),
			"DIR" => "/",
			"SITE_NAME" => trim($this->request->get("NAME")),
			"SERVER_NAME" => $serverName,
			"EMAIL" => $defaultParam["EMAIL"],
			"LANGUAGE_ID" => $defaultParam["LANGUAGE_ID"],
			"DOC_ROOT" => trim($this->request->get("DOC_ROOT")),
			"DOMAINS" => $serverName,
			"CULTURE_ID" => $defaultParam["CULTURE_ID"],
			"WIZARD_REWRITE" => $this->request->get("WIZARD_REWRITE"),
		);
		unset($defaultParam, $serverName);
	}

	/**
	 * @return array
	 */
	private function getFields()
	{
		return $this->fields;
	}

	/**
	 * Check site's fields
	 *
	 * @throws Main\SystemException
	 */
	private function checkFields()
	{
		/** @noinspection PhpVariableNamingConventionInspection */
		global $APPLICATION;

		/** @noinspection PhpUndefinedClassInspection */
		$obSite = new \CSite();

		if (!$obSite->CheckFields($this->getFields()))
		{
			if ($ex = $APPLICATION->GetException())
			{
				$errors = '';
				foreach ($ex->GetMessages() as $message)
				{
					$errors .= $message["text"]."<br>";
				}

				if ($errors)
				{
					throw new Main\SystemException($errors);
				}
			}
		}
	}

	/**
	 * @return array
	 * @throws Main\SystemException
	 */
	private function createNewSite()
	{
		/** @noinspection PhpVariableNamingConventionInspection */
		global $APPLICATION;

		$this->checkFields();

		/** @var array $result */
		$result = [
			"ERROR" => [],
			"LID" => null,
		];

		if (!$this->copyTemplate())
		{
			$result["ERROR"][] = Loc::getMessage("SALE_CSM_WIZARD_SITESTEP_TEMPLATE_COPY_ERROR", [
				"#SITE_TEMPLATE_NAME#" => self::SITE_TEMPLATE_LOGIN
			]);
			return $result;
		}

		$arFields = $this->getFields();
		$this->addTemplate($arFields);

		/** @noinspection PhpUndefinedClassInspection */
		$obSite = new \CSite();
		$lid = $obSite->Add($arFields);
		if ($lid)
		{
			$result["LID"] = $lid;
		}
		else
		{
			if ($ex = $APPLICATION->GetException())
			{
				foreach ($ex->GetMessages() as $message)
				{
					$result["ERROR"][] = $message["text"];
				}
			}
		}

		return $result;
	}

	/**
	 * @param $serverName
	 * @return mixed
	 */
	private function prepareServerName($serverName)
	{
		$serverName = filter_var($serverName, FILTER_SANITIZE_URL);
		$serverName = trim($serverName, " \t\n\r\0\x0B/\\");
		$components = parse_url($serverName);
		if (isset($components["host"]) && !empty($components["host"]))
		{
			return $components["host"];
		}
		elseif (isset($components["path"]) && !empty($components["path"]))
		{
			return $components["path"];
		}
		else
		{
			return $serverName;
		}
	}

	/**
	 * @param $documentRoot
	 * @return bool
	 */
	private function isIndexFileExists($documentRoot)
	{
		if ($this->request->get("WIZARD_REWRITE"))
		{
			return false;
		}

		return (Main\IO\File::isFileExists($documentRoot."index.php"));
	}

	/**
	 * @param $documentRoot
	 * @return array
	 */
	private function getMissingRequiredFileList($documentRoot)
	{
		$requiredFileList = [
			"bitrix",
			"upload",
			"404.php",
			".htaccess"
		];

		$missingFileList = [];
		foreach ($requiredFileList as $requiredFile)
		{
			$fullPath = $documentRoot.$requiredFile;

			if (!file_exists($fullPath))
			{
				$missingFileList[] = $requiredFile;
			}
		}

		return $missingFileList;
	}

	/**
	 * @param $documentRoot
	 * @return bool
	 */
	private function isDocumentRootExists($documentRoot)
	{
		return Main\IO\Directory::isDirectoryExists($documentRoot);
	}

	/**
	 * @param $lid
	 * @throws Main\ArgumentException
	 * @throws Main\SystemException
	 */
	private function prepareSite($lid)
	{
		$this->setAuthComponentsTemplate($lid);
		$this->setLandingOption($lid);
	}

	/**
	 * @param $arFields
	 */
	private function addTemplate(&$arFields)
	{
		$arFields["TEMPLATE"][] = [
			"TEMPLATE" => self::SITE_TEMPLATE_LOGIN,
			"SORT" => 1,
			"CONDITION" => ""
		];
	}

	/**
	 * Copy template files
	 *
	 * @return bool
	 */
	private function copyTemplate()
	{
		return CopyDirFiles(
			$_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/install/templates/".self::SITE_TEMPLATE_LOGIN,
			$_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/templates/".self::SITE_TEMPLATE_LOGIN,
			true,
			true,
			false
		);
	}

	/**
	 * @param $siteId
	 * @throws Main\ArgumentOutOfRangeException
	 */
	private function setAuthComponentsTemplate($siteId)
	{
		Option::set("main", "auth_components_template", "", $siteId);
	}

	/**
	 * @param $siteId
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	private function setLandingOption($siteId)
	{
		$crmSite = \Bitrix\Main\SiteTable::getList([
			"select" => ["SERVER_NAME"],
			"filter" => ["=LID" => $siteId],
		])->fetch();
		if ($crmSite && !empty($crmSite["SERVER_NAME"]))
		{
			Option::set("landing", "portal_url", $crmSite["SERVER_NAME"]);
		}
	}

	/**
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	private function getSiteList()
	{
		return Main\SiteTable::getList([
			"select" => ["*"],
			"filter" => [
				"ACTIVE" => "Y",
				"!DEF" => "Y",
				"!DOC_ROOT" => $_SERVER["DOCUMENT_ROOT"],
			]
		])->fetchAll();
	}

	/**
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	private function showSelectSiteHtml()
	{
		$siteList = $this->getSiteList();
		$siteId = (isset($this->formFieldList["CRM_SITE"]) ? htmlspecialcharsbx($this->formFieldList["CRM_SITE"]) : 'new');

		$option = '';
		$option .= '<div class="ui-ctl ui-ctl-w75 ui-ctl-after-icon ui-ctl-dropdown"><div class="ui-ctl-after ui-ctl-icon-angle"></div><select class="ui-ctl-element" name="CRM_SITE" id="CRM_SITE" title="">';
		$option .= '<option value="new">'.Loc::getMessage("SALE_CSM_WIZARD_SITESTEP_SITE_SELECT_NEW").'</option>';
		foreach ($siteList as $site)
		{
			$lid = htmlspecialcharsbx($site["LID"]);
			$name = htmlspecialcharsbx($site["NAME"]);
			$option .= '<option value="'.$lid.'" '.(($siteId == $lid) ? " selected" : '').'>'.$name.'</option>';
		}
		$option .= '</select></div>';

		$content = '
			<div class="adm-crm-site-master-form-row">
				<label for="LID" class="adm-crm-site-master-form-label">'.Loc::getMessage("SALE_CSM_WIZARD_SITESTEP_SITE_SELECT_TITLE").'</label>
				<div class="adm-crm-site-master-form-control">'.$option.'</div>
			</div>
		';

		$this->content = str_replace("{#SITE_SELECT#}", $content, $this->content);
	}

	/**
	 * Add html for creating new site
	 */
	private function createNewSiteHtml()
	{
		ob_start();
		?>
		<div class="adm-crm-site-master-form-row">
			<label for="LID" class="adm-crm-site-master-form-label">
				<?=Loc::getMessage("SALE_CSM_WIZARD_SITESTEP_ID")?>
			</label>
			<div class="adm-crm-site-master-form-control">
				<div class="ui-ctl ui-ctl-textbox ui-ctl-w75">
					<?
					$lid = (isset($this->formFieldList["LID"]) ? htmlspecialcharsbx($this->formFieldList["LID"]) : '');
					if (!$lid)
					{
						try
						{
							$lid = $this->getRandSiteId();
						}
						catch (\Exception $ex)
						{
							$lid = '';
						}
					}
					?>
					<input type="text" name="LID" id="LID" class="ui-ctl-element" size="2" maxlength="2" value="<?=($lid)?>">
				</div>
			</div>
		</div>

		<div class="adm-crm-site-master-form-row">
			<label for="NAME" class="adm-crm-site-master-form-label">
				<?=Loc::getMessage("SALE_CSM_WIZARD_SITESTEP_NAME")?>
			</label>
			<div class="adm-crm-site-master-form-control">
				<div class="ui-ctl ui-ctl-textbox ui-ctl-w75">
					<?$name = (isset($this->formFieldList["NAME"]) ? htmlspecialcharsbx($this->formFieldList["NAME"]) : '');?>
					<input type="text" name="NAME" id="NAME" class="ui-ctl-element" value="<?=($name)?>">
				</div>
			</div>
		</div>

		<div class="adm-crm-site-master-form-row">
			<label for="SERVER_NAME" class="adm-crm-site-master-form-label">
				<?=Loc::getMessage("SALE_CSM_WIZARD_SITESTEP_MAIN_SERVER_URL")?>
			</label>
			<div class="adm-crm-site-master-form-control">
				<div class="ui-ctl ui-ctl-textbox ui-ctl-w75">
					<?$serverName = (isset($this->formFieldList["SERVER_NAME"]) ? htmlspecialcharsbx($this->formFieldList["SERVER_NAME"]) : '');?>
					<input type="text" name="SERVER_NAME" id="SERVER_NAME" class="ui-ctl-element" value="<?=($serverName)?>">
				</div>
			</div>
		</div>

		<div class="adm-crm-site-master-form-row">
			<label for="DOC_ROOT" class="adm-crm-site-master-form-label">
				<?=Loc::getMessage("SALE_CSM_WIZARD_SITESTEP_MAIN_DOC_ROOT")?>
			</label>
			<div class="adm-crm-site-master-form-control">
				<div class="ui-ctl ui-ctl-textbox ui-ctl-w75">
					<?$docRoot = (isset($this->formFieldList["DOC_ROOT"]) ? htmlspecialcharsbx($this->formFieldList["DOC_ROOT"]) : '');?>
					<input type="text" name="DOC_ROOT" id="DOC_ROOT" class="ui-ctl-element" value="<?=($docRoot)?>">
				</div>
			</div>
		</div>

		<div class="adm-crm-site-master-form-row">
			<a href="javascript:void(0)"
			   class="adm-crm-site-master-form-action-fill"
			   id="DOC_ROOT_LINK">
				<?=Loc::getMessage("SALE_CSM_WIZARD_SITESTEP_MAIN_DOC_ROOT_SET")?>
			</a>
		</div>
		<?
		$content = ob_get_contents();
		ob_end_clean();

		$this->content = str_replace("{#SITE_FORM#}", $content, $this->content);
	}

	/**
	 * Get site id for form
	 *
	 * @return string
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	private function getRandSiteId()
	{
		$id = '';
		$idList = [];

		$siteIterator = Main\SiteTable::getList([
			"select" => ["LID"]
		]);
		while ($site = $siteIterator->fetch())
		{
			$idList[] = $site["LID"];
		}

		if ($idList)
		{
			do
			{
				$id = Main\Security\Random::getString(2);
			}
			while(in_array($id, $idList));
		}

		return $id;
	}

	/**
	 * @return int
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	private function getSort()
	{
		$sortList = [];

		$siteList = Main\SiteTable::getList([
			"select" => ["SORT"],
			"filter" => [
				"ACTIVE" => "Y",
			]
		])->fetchAll();
		foreach ($siteList as $site)
		{
			$sortList[] = $site["SORT"];
		}

		$sort = null;
		if ($sortList)
		{
			$sort = max($sortList);
		}

		return $sort ? (int)$sort + 100 : 100;
	}

	/**
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	private function getDefaultParam()
	{
		$param = [
			"EMAIL" => "",
			"LANGUAGE_ID" => "",
			"CULTURE_ID" => "",
		];

		$site = Main\SiteTable::getList([
			"select" => ["*"],
			"filter" => [
				"ACTIVE" => "Y",
				"DEF" => "Y",
			]
		])->fetch();
		if ($site["DEF"] === "Y")
		{
			$param = [
				"EMAIL" => $site["EMAIL"],
				"LANGUAGE_ID" => $site["LANGUAGE_ID"],
				"CULTURE_ID" => $site["CULTURE_ID"],
			];
		}

		if (!$param["EMAIL"])
		{
			$param["EMAIL"] = Option::get('main', "email_from");
		}

		return $param;
	}

	/**
	 * Show extended errors
	 *
	 * @param $strError
	 * @return string
	 */
	public function showExtendedErrors($strError)
	{
		$this->SetTitle(Loc::getMessage("SALE_CSM_WIZARD_SITESTEP_SITE_CREATE_ERROR"));

		$this->SetPrevStep("Bitrix\Sale\CrmSiteMaster\Steps\SiteInstructionStep");
		$this->SetPrevCaption(Loc::getMessage("SALE_CSM_WIZARD_SITESTEP_DANGER_BACK"));

		$error = [
			Loc::getMessage("SALE_CSM_WIZARD_SITESTEP_DANGER_DESCR"),
			$strError
		];

		ob_start();

		echo $this->saveFormHiddenFields();
		?>
		<div class="adm-crm-site-master-content">
			<div class="adm-crm-site-master-warning">
				<img class="adm-crm-site-master-warning-image" src="<?=$this->component->getPath()?>/wizard/images/warning.svg" alt="">
			</div>
			<div class="ui-alert ui-alert-danger ui-alert-text-center ui-alert-inline ui-alert-icon-danger">
				<span class="ui-alert-message"><?=implode("<br>", $error)?></span>
			</div>
		</div>

		<div class="adm-crm-site-master-buttons">
			<div class="ui-btn-container ui-btn-container-center">
				<?
				if ($this->GetPrevStepID() !== null)
				{
					?>
					<input type="hidden" name="<?=$this->GetWizard()->prevStepHiddenID?>" value="<?=$this->GetPrevStepID()?>">
					<button type="submit" class="ui-btn ui-btn-primary" name="<?=$this->GetWizard()->prevButtonID?>">
						<?=$this->GetPrevCaption()?>
					</button>
					<?
				}
				?>
			</div>
		</div>
		<?
		$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}

	/**
	 * Init form fields from wizard's var
	 */
	private function initFormFields()
	{
		foreach ($this->formFieldList as $field)
		{
			$this->formFieldList[$field] = $this->GetWizard()->GetVar($field);
		}
	}

	/**
	 * Save form fields to hidden fields
	 *
	 * @return string
	 */
	private function saveFormHiddenFields()
	{
		$formFieldList = '';

		foreach ($this->formFieldList as $field)
		{
			$formFieldList .= $this->ShowHiddenField($field, $this->request->get($field));
		}

		return $formFieldList;
	}

	/**
	 * Save form fields to wizard's var
	 */
	private function saveFormFields()
	{
		$prefix = $this->GetWizard()->GetVarPrefix();

		foreach ($this->formFieldList as $field)
		{
			$value = $this->request->get($field) ? $this->request->get($field) : $this->request->get($prefix.$field);
			$this->GetWizard()->SetVar($field, $value);
		}
	}

	/**
	 * Delete form fields
	 */
	private function deleteFormFields()
	{
		foreach ($this->formFieldList as $field)
		{
			$this->GetWizard()->UnSetVar($field);
		}
	}
}