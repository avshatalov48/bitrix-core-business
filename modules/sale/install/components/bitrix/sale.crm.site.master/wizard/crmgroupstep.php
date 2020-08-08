<?php
namespace Bitrix\Sale\CrmSiteMaster\Steps;

use Bitrix\Main,
	Bitrix\Main\Application,
	Bitrix\Main\Localization\Loc,
	Bitrix\Sale\CrmSiteMaster\Tools\SitePatcher;

Loc::loadMessages(__FILE__);

/**
 * Class CrmGroupStep
 * @package Bitrix\Sale\CrmSiteMaster\Steps
 */
class CrmGroupStep extends \CWizardStep
{
	const ADMIN_USER_GROUP_ID = 1;

	const SELECTED_USER_GROUPS = "~SELECTED_USER_GROUPS";
	const EMPLOYEE_USER_GROUP_ID = "~EMPLOYEE_USER_GROUP_ID";

	private $currentStepName = __CLASS__;

	/** @var \SaleCrmSiteMaster */
	private $component = null;

	/** @var Main\Request */
	private $request;

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
	}

	/**
	 * Initialization step id, title and next step
	 *
	 * @throws Main\SystemException
	 * @throws \ReflectionException
	 */
	public function initStep()
	{
		$this->component = $this->GetWizard()->GetVar("component");

		$this->SetStepID($this->currentStepName);
		$this->SetTitle(Loc::getMessage("SALE_CSM_WIZARD_CRMGROUPSTEP_TITLE"));

		$this->request = Application::getInstance()->getContext()->getRequest();

		$this->prepareButtons();

		$this->setStepErrors();
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
		ob_start();
		?>
		<div class="adm-crm-site-master-paragraph"><?=Loc::getMessage("SALE_CSM_WIZARD_CRMGROUPSTEP_DESCR1")?></div>
		<div class="adm-crm-site-master-paragraph"><?=Loc::getMessage("SALE_CSM_WIZARD_CRMGROUPSTEP_DESCR2")?></div>
		<div class="adm-crm-site-master-paragraph"><?=Loc::getMessage("SALE_CSM_WIZARD_CRMGROUPSTEP_DESCR3")?></div>

		<div class="ui-alert ui-alert-danger ui-alert-inline ui-alert-icon-danger">
			<span class="ui-alert-message"><?=Loc::getMessage("SALE_CSM_WIZARD_CRMGROUPSTEP_NOTE")?></span>
		</div>

		<div class="adm-crm-group-master-form">
			<div class="adm-crm-group-master-form-select-manager">
				{#USER_GROUPS_SELECT_MANAGER#}
			</div>
			<div class="adm-crm-group-master-form-select-admin">
				{#USER_GROUPS_SELECT_ADMIN#}
			</div>
		</div>
		<?
		$content = ob_get_contents();
		ob_end_clean();

		$this->content = $content;

		$this->showSelectUserGroupManagerHtml();
		$this->showSelectUserGroupAdminHtml();

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
		Main\Loader::includeModule("crm");

		$this->saveSelectedGroups();

		$employeeGroupId = $this->getEmployeesGroupId();
		if ($employeeGroupId)
		{
			Main\Config\Option::set("crm", self::EMPLOYEE_USER_GROUP_ID, $employeeGroupId);
		}

		$crmRoleId = $this->getCrmRoleId(
				Loc::getMessage("SALE_CSM_WIZARD_CRMGROUPSTEP_CRM_ROLE_ADM")
		);
		if ($crmRoleId === null)
		{
			$crmRoleId = $this->getCrmRoleId(
				Loc::getMessage("SALE_CSM_WIZARD_CRMGROUPSTEP_CRM_ROLE_MAN")
			);
		}

		if ($crmRoleId && $employeeGroupId)
		{
			$this->setCrmRole($crmRoleId, [$employeeGroupId]);
		}

		$this->setIntranetUserGroups();

		$sitePatcher = SitePatcher::getInstance();
		$sitePatcher->addEmployeesToCompanyStructure();

		return true;
	}

	/**
	 * @throws Main\ArgumentOutOfRangeException
	 */
	private function saveSelectedGroups()
	{
		$selectedGroups = [];

		$groupManagerList = $this->request->get("USER_GROUPS_MANAGER");
		if ($groupManagerList)
		{
			$selectedGroups["MANAGER"] = $groupManagerList;
		}

		$groupAdminList = $this->request->get("USER_GROUPS_ADMIN");
		if ($groupAdminList)
		{
			$selectedGroups["ADMIN"] = $groupAdminList;
		}

		if ($selectedGroups)
		{
			Main\Config\Option::set("sale", self::SELECTED_USER_GROUPS, serialize($selectedGroups));
		}

		unset($groupManagerList, $groupAdminList, $selectedGroups);
	}

	/**
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\LoaderException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	private function setIntranetUserGroups()
	{
		$groups = [self::ADMIN_USER_GROUP_ID];

		$groupManagerList = $this->request->get("USER_GROUPS_MANAGER");
		if ($groupManagerList)
		{
			$groups = array_merge($groupManagerList, $groups);
		}

		$groupAdminList = $this->request->get("USER_GROUPS_ADMIN");
		if ($groupAdminList)
		{
			$groups = array_merge($groupAdminList, $groups);
		}

		$employeesGroupId = $this->getEmployeesGroupId();

		if ($employeesGroupId)
		{
			$sitePatcher = SitePatcher::getInstance();
			$userList = $sitePatcher->getUserIdList($groups);
			$sitePatcher->addNewGroup($userList, [$employeesGroupId]);
		}
	}

	/**
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	private function showSelectUserGroupManagerHtml()
	{
		$groupList = $this->getUserGroupList();

		ob_start();
		?>
		<div class="adm-crm-site-master-form-row">
			<label for="USER_GROUPS_MANAGER" class="adm-crm-site-master-form-label">
				<?=Loc::getMessage("SALE_CSM_WIZARD_CRMGROUPSTEP_SELECT_TITLE_MAN")?>
			</label>
			<div class="adm-crm-site-master-form-control">
				<div class="ui-ctl-multiple-select ui-ctl-w75">
					<select class="ui-ctl-element ui-ctl-element-auto"
						name="USER_GROUPS_MANAGER[]" id="USER_GROUPS_MANAGER" title="" multiple size="5"
					>
					<?
					foreach ($groupList as $group)
					{
						?><option value="<?=$group["ID"]?>"><?=$group["NAME"]?></option><?
					}
					?>
					</select>
				</div>
			</div>
		</div>
		<?
		$content = ob_get_contents();
		ob_end_clean();

		$this->content = str_replace("{#USER_GROUPS_SELECT_MANAGER#}", $content, $this->content);
	}

	/**
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	private function showSelectUserGroupAdminHtml()
	{
		$groupList = $this->getUserGroupList();

		ob_start();
		?>
		<div class="adm-crm-site-master-form-row">
			<label for="USER_GROUPS_ADMIN" class="adm-crm-site-master-form-label">
				<?=Loc::getMessage("SALE_CSM_WIZARD_CRMGROUPSTEP_SELECT_TITLE_ADM")?>
			</label>
			<div class="adm-crm-site-master-form-control">
				<div class="ui-ctl-multiple-select ui-ctl-w75">
					<select class="ui-ctl-element ui-ctl-element-auto"
							name="USER_GROUPS_ADMIN[]" id="USER_GROUPS_ADMIN" title="" multiple size="5">
						<?
						foreach ($groupList as $group)
						{
							?><option value="<?=$group["ID"]?>"><?=$group["NAME"]?></option><?
						}
						?>
					</select>
				</div>
			</div>
		</div>
		<?
		$content = ob_get_contents();
		ob_end_clean();

		$this->content = str_replace("{#USER_GROUPS_SELECT_ADMIN#}", $content, $this->content);
	}

	/**
	 * @return array
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	private function getUserGroupList()
	{
		$groupList = [];

		$groupIterator = Main\GroupTable::getList([
			"select" => ["ID", "NAME"],
			"filter" => [
				'LOGIC' => 'AND',
				["!%STRING_ID" => "CRM_SHOP_"],
				["!STRING_ID" => "ADMIN_SECTION"],
				["!STRING_ID" => "SUPPORT"],
				["!STRING_ID" => "CREATE_GROUPS"],
				["!STRING_ID" => "PERSONNEL_DEPARTMENT"],
				["!STRING_ID" => "DIRECTION"],
				["!STRING_ID" => "MARKETING_AND_SALES"],
				["!%STRING_ID" => "EMPLOYEES_"],
				["!%STRING_ID" => "PORTAL_ADMINISTRATION_"],
			],
		]);
		while ($group = $groupIterator->fetch())
		{
			$groupList[] = $group;
		}

		return $groupList;
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
	 * @return int|null
	 * @throws Main\ArgumentException
	 * @throws Main\ArgumentNullException
	 * @throws Main\ArgumentOutOfRangeException
	 * @throws Main\LoaderException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	private function getEmployeesGroupId()
	{
		$employeeGroupId = Main\GroupTable::getList([
			"select" => ["ID"],
			"filter" => ["=STRING_ID" => "EMPLOYEES_".SitePatcher::getInstance()->getCrmSiteId()],
		])->fetch();

		return $employeeGroupId ? (int)$employeeGroupId["ID"] : null;
	}

	/**
	 * @param $crmRoleId
	 * @param array $groups
	 */
	private function setCrmRole($crmRoleId, array $groups)
	{
		$arPerms = [];
		foreach ($groups as $group)
		{
			$arPerms["G".$group][] = $crmRoleId;
		}

		$crmRole = new \CcrmRole();
		$crmRole->SetRelation($arPerms);

		try
		{
			\CCrmSaleHelper::addUserToShopGroup();
			$cache = new \CPHPCache;
			$cache->CleanDir("/crm/list_crm_roles/");
		}
		catch (\Exception $ex)
		{
			$this->SetError($ex->getMessage());
		}
	}

	/**
	 * @param $name
	 * @return int|null
	 */
	private function getCrmRoleId($name)
	{
		$crmRoleId = null;

		$crmRoleIterator = \CCrmRole::GetList(
			["ID" => "DESC"],
			["NAME" => $name]
		);
		if ($crmRole = $crmRoleIterator->Fetch())
		{
			$crmRoleId = (int)$crmRole["ID"];
		}

		return $crmRoleId;
	}
}