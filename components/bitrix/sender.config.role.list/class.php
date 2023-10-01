<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sender\Access\ActionDictionary;
use Bitrix\Sender\Access\Service\RolePermissionService;
use Bitrix\Sender\Access\Service\RolePermissionServiceInterface;
use Bitrix\Sender\Access\Service\RoleRelationService;
use Bitrix\Sender\Access\Service\RoleRelationServiceInterface;
use Bitrix\Sender\Security;

if (!Bitrix\Main\Loader::includeModule('sender'))
{
	ShowError('Module `sender` not installed');
	die();
}

class ConfigRoleListSenderComponent extends Bitrix\Sender\Internals\CommonSenderComponent implements Controllerable
{
	/**
	 * @var RolePermissionServiceInterface;
	 */
	private $permissionService;
	/**
	 * @var RoleRelationServiceInterface;
	 */
	private $roleRelationService;

	protected function initParams()
	{
		parent::initParams();
		$this->permissionService = new RolePermissionService();
		$this->roleRelationService = new RoleRelationService();
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

		$roleList = $this->permissionService->getRoleList();
		foreach ($roleList as $row)
		{
			$this->arResult['ROLES'][$row['ID']] = [
				'ID' => $row['ID'],
				'NAME' => $row['NAME'],
				'EDIT_URL' => str_replace('#id#', $row['ID'], $this->arParams['PATH_TO_EDIT']),
			];
		}

		$this->configureAccessCodes();

		return true;
	}

	/**
	 * configure access code list from database configuration
	 */
	private function configureAccessCodes()
	{
		$accessCodes = [];
		$accessCodesToResolve = array();
		$accessList = $this->roleRelationService->getRelationList(
			[
				'select' => ['ID', 'ROLE_ID', 'RELATION']
			]
		);

		foreach ($accessList as $row)
		{
			$accessCodes[$row['ID']] = [
				'ID' => $row['ID'],
				'ROLE_ID' => $row['ROLE_ID'],
				'RELATION' => $row['RELATION']
			];
			$accessCodesToResolve[] = $row['RELATION'];
		}

		$accessManager = new \CAccess();
		$resolvedAccessCodes = $accessManager->GetNames($accessCodesToResolve);

		foreach ($accessCodes as $id => $roleAccessCode)
		{
			if (isset($resolvedAccessCodes[$roleAccessCode['RELATION']]))
			{
				$codeDescription = $resolvedAccessCodes[$roleAccessCode['RELATION']];
				$accessCodes[$id]['ACCESS_PROVIDER'] = $codeDescription['provider'];
				$accessCodes[$id]['ACCESS_NAME'] = $codeDescription['name'];
			}
			else
			{
				$accessCodes[$id]['ACCESS_NAME'] = Loc::getMessage('SENDER_CONFIG_ROLE_LIST_COMP_UNKNOWN_ACCESS_CODE');
			}
		}
		$this->arResult['ROLE_ACCESS_CODES'] = $accessCodes;
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
		$this->errors = new ErrorCollection();
		$this->userId = Security\User::current()->getId();

		if (!$this->checkRequiredParams())
		{
			$this->printErrors();
			return;
		}

		try
		{
			static::initParams();
		}
		catch (ArgumentException $e)
		{
			$this->errors->setError(new Error('Failed to initialize module `sender`'));
			$this->printErrors();
			return $this->arParams;
		}

		/* Set title */
		if ($this->arParams['SET_TITLE'])
		{
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
			$this->permissionService->deleteRole($roleId);
		}
	}

	public function getEditAction()
	{
		return ActionDictionary::ACTION_SETTINGS_EDIT;
	}

	public function getViewAction()
	{
		return ActionDictionary::ACTION_SETTINGS_EDIT;
	}
}