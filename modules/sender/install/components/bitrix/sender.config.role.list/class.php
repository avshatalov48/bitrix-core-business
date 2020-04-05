<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Main\Loader;
use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Engine\Contract\Controllerable;

use Bitrix\Sender\Security;

Loc::loadMessages(__FILE__);

class SenderConfigRoleListComponent extends CBitrixComponent implements Controllerable
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

		$this->arParams['SET_TITLE'] = isset($this->arParams['SET_TITLE']) ? $this->arParams['SET_TITLE'] == 'Y' : true;
		$this->arParams['CAN_EDIT'] = isset($this->arParams['CAN_EDIT'])
			?
			$this->arParams['CAN_EDIT']
			:
			Security\Access::current()->canModifySettings();
	}

	protected function preparePost()
	{
		$accessCodes = $this->request->get('PERMS');
		if(!is_array($accessCodes))
		{
			return;
		}

		$list = [];
		foreach ($accessCodes as $accessCode => $roleId)
		{
			if (!$roleId || !$accessCode)
			{
				continue;
			}

			$list[] = [
				'ROLE_ID' => $roleId,
				'ACCESS_CODE' => $accessCode
			];
		}

		$result = Security\Role\Manager::setAccessCodes($list);
		$this->errors->add($result->getErrors());
	}

	protected function prepareResult()
	{
		$this->arResult['ERRORS'] = array();
		$this->arResult['ROLES'] = [];
		$this->arResult['CAN_EDIT'] = Security\Role\Manager::canUse();
		if(!$this->arResult['CAN_EDIT'])
		{
			$this->arResult['TRIAL_TEXT'] = Security\Role\Manager::getTrialText();
		}


		if ($this->request->isPost() && check_bitrix_sessid() && $this->arResult['CAN_EDIT'])
		{
			$this->preparePost();
		}

		foreach (Security\Role\Manager::getRoleList() as $row)
		{
			$this->arResult['ROLES'][$row['ID']] = [
				'ID' => $row['ID'],
				'NAME' => $row['NAME'],
				'EDIT_URL' => str_replace('#id#', $row['ID'], $this->arParams['PATH_TO_EDIT']),
			];
		}

		$accessCodes = [];
		$accessCodesToResolve = array();
		$accessList = Security\Role\Manager::getAccessList([
			'select' => ['ID', 'ROLE_ID', 'ROLE_NAME' => 'ROLE.NAME', 'ACCESS_CODE'],
		]);
		foreach ($accessList as $row)
		{
			$accessCodes[$row['ID']] = [
				'ID' => $row['ID'],
				'ROLE_ID' => $row['ROLE_ID'],
				'ROLE_NAME' => $row['ROLE_NAME'],
				'ACCESS_CODE' => $row['ACCESS_CODE']
			];
			$accessCodesToResolve[] = $row['ACCESS_CODE'];
		}

		$accessManager = new \CAccess();
		$resolvedAccessCodes = $accessManager->GetNames($accessCodesToResolve);

		foreach ($accessCodes as $id => $roleAccessCode)
		{
			if (isset($resolvedAccessCodes[$roleAccessCode['ACCESS_CODE']]))
			{
				$codeDescription = $resolvedAccessCodes[$roleAccessCode['ACCESS_CODE']];
				$accessCodes[$id]['ACCESS_PROVIDER'] = $codeDescription['provider'];
				$accessCodes[$id]['ACCESS_NAME'] = $codeDescription['name'];
			}
			else
			{
				$accessCodes[$id]['ACCESS_NAME'] = Loc::getMessage('SENDER_CONFIG_ROLE_LIST_COMP_UNKNOWN_ACCESS_CODE');
			}
		}

		$this->arResult['ROLE_ACCESS_CODES'] = $accessCodes;

		return true;
	}

	protected function printErrors()
	{
		foreach ($this->errors as $error)
		{
			ShowError($error);
		}
	}

	public function executeComponent()
	{
		if (!$this->errors->isEmpty())
		{
			$this->printErrors();
			return;
		}

		if (!$this->prepareResult())
		{
			$this->printErrors();
			return;
		}

		$this->includeComponentTemplate();
	}

	public function onPrepareComponentParams($arParams)
	{
		$this->arParams = $arParams;
		$this->errors = new \Bitrix\Main\ErrorCollection();
		if (!Loader::includeModule('sender'))
		{
			$this->errors->setError(new Error('Module `sender` is not installed.'));
			$this->printErrors();
			return $this->arParams;
		}

		$this->initParams();
		if (!$this->checkRequiredParams())
		{
			$this->printErrors();
		}

		/* Set title */
		if ($this->arParams['SET_TITLE'])
		{
			/**@var CAllMain*/
			$GLOBALS['APPLICATION']->SetTitle(Loc::getMessage('SENDER_CONFIG_ROLE_LIST_COMP_TITLE'));
		}

		if (!$this->arParams['CAN_EDIT'])
		{
			$this->errors->setError(Security\AccessChecker::getError());
			return $this->arParams;
		}

		return $this->arParams;
	}

	public function configureActions()
	{
		return array();
	}

	public function deleteRoleAction($roleId)
	{
		if (!$this->errors->isEmpty())
		{
			return;
		}

		$roleId = (int) $roleId;
		if($roleId > 0)
		{
			Security\Role\Manager::deleteRole($roleId);
		}
	}
}