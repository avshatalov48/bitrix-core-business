<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Web\Uri;

use Bitrix\Sender\Security;
use Bitrix\Sender\Internals\Model;

Loc::loadMessages(__FILE__);

class SenderConfigRoleEditComponent extends CBitrixComponent
{
	/** @var  ErrorCollection $errors */
	protected $errors;

	protected function checkRequiredParams()
	{
		return true;
	}

	protected function initParams()
	{
		$this->arParams['PATH_TO_LIST'] = isset($this->arParams['PATH_TO_LIST']) ? $this->arParams['PATH_TO_LIST'] : '';
		$this->arParams['PATH_TO_USER_PROFILE'] = isset($this->arParams['PATH_TO_USER_PROFILE']) ? $this->arParams['PATH_TO_USER_PROFILE'] : '';
		$this->arParams['NAME_TEMPLATE'] = empty($this->arParams['NAME_TEMPLATE']) ? \CAllSite::GetNameFormat(false) : str_replace(array("#NOBR#","#/NOBR#"), array("",""), $this->arParams["NAME_TEMPLATE"]);

		if (!isset($this->arParams['ID']))
		{
			$this->arParams['ID'] = $this->request->get('ID');
		}
		$this->arParams['ID'] = (int) $this->arParams['ID'];

		$this->arParams['SET_TITLE'] = isset($this->arParams['SET_TITLE']) ? $this->arParams['SET_TITLE'] == 'Y' : true;
		$this->arParams['CAN_EDIT'] = isset($this->arParams['CAN_EDIT'])
			?
			$this->arParams['CAN_EDIT']
			:
			Security\Access::current()->canModifySettings();
	}

	protected function preparePost()
	{
		$roleId = (int) $this->arParams['ID'];
		$roleName = (string) $this->request->get('NAME');
		$permissions = $this->request->get('PERMISSIONS');

		$result = Security\Role\Manager::setRolePermissions(
			$roleId,
			['NAME' => $roleName],
			$permissions
		);
		$this->errors->add($result->getErrors());
		if ($this->errors->isEmpty())
		{
			if ($this->request->get('IFRAME') == 'Y')
			{
				$roleId = $roleId ?: $result->getId();
				$path = str_replace('#id#', $roleId, $this->arParams['PATH_TO_EDIT']);
				$uri = new Uri($path);

				$uri->addParams(array('IFRAME' => 'Y'));
				$uri->addParams(array('IS_SAVED' => 'Y'));

				$path = $uri->getLocator();
				LocalRedirect($path);
			}
			else
			{
				LocalRedirect($this->arParams['PATH_TO_LIST']);
			}
		}
	}

	protected function prepareResult()
	{
		/* Set title */
		if ($this->arParams['SET_TITLE'])
		{
			/**@var CAllMain*/
			$GLOBALS['APPLICATION']->SetTitle(
				$this->arParams['ID'] > 0
					?
					Loc::getMessage('SENDER_CONFIG_ROLE_EDIT_COMP_TITLE_EDIT')
					:
					Loc::getMessage('SENDER_CONFIG_ROLE_EDIT_COMP_TITLE_ADD')
			);
		}

		if (!$this->arParams['CAN_EDIT'])
		{
			$this->errors->setError(Security\AccessChecker::getError());
			return false;
		}

		if (!Security\Role\Manager::canUse())
		{
			return false;
		}

		$this->arResult['ERRORS'] = array();
		$this->arResult['ACTION_URI'] = $this->getPath() . '/ajax.php';
		$this->arResult['PERMISSIONS'] = [];
		$this->arResult['NAME'] = Loc::getMessage('SENDER_CONFIG_ROLE_EDIT_COMP_TEMPLATE_NAME');

		$row = Model\Role\RoleTable::getRowById($this->arParams['ID']);
		if ($row)
		{
			$this->arResult['NAME'] = $row['NAME'];
			$this->arResult['PERMISSIONS'] = Security\Role\Manager::getRolePermissions($this->arParams['ID']);
		}

		if ($this->request->isPost() && check_bitrix_sessid() && $this->arParams['CAN_EDIT'])
		{
			$this->preparePost();
			$this->printErrors();
		}

		$this->arResult['LIST'] = $this->getSenderPermissions();

		return true;
	}

	protected function getSenderPermissions()
	{
		$list = [];
		$perms = $this->arResult['PERMISSIONS'];
		$map = Security\Role\Permission::getMap();
		foreach ($map as $entityCode => $actionMap)
		{
			$actions = [];
			foreach ($actionMap as $actionCode => $availablePerms)
			{
				$permissions = [];
				foreach ($availablePerms as $permCode)
				{
					$permissions[] = [
						'CODE' => $permCode,
						'NAME' => Security\Role\Permission::getPermissionName($permCode),
						'SELECTED' => $permCode === $perms[$entityCode][$actionCode]
					];
				}

				$actions[] = [
					'CODE' => $actionCode,
					'NAME' => Security\Role\Permission::getActionName($actionCode),
					'PERMS' => $permissions
				];
			}

			$list[] = [
				'CODE' => $entityCode,
				'NAME' => Security\Role\Permission::getEntityName($entityCode),
				'ACTIONS' => $actions
			];
		}

		return $list;
	}

	public function executeComponent()
	{
		$this->errors = new \Bitrix\Main\ErrorCollection();
		if (!Loader::includeModule('sender'))
		{
			$this->errors->setError(new Error('Module `sender` is not installed.'));
			$this->printErrors();
			return;
		}

		$this->initParams();
		if (!$this->checkRequiredParams())
		{
			$this->printErrors();
			return;
		}

		if (!$this->prepareResult())
		{
			$this->printErrors();
			return;
		}

		$this->printErrors();
		$this->includeComponentTemplate();
	}

	protected function printErrors()
	{
		foreach ($this->errors as $error)
		{
			ShowError($error);
		}
	}
}