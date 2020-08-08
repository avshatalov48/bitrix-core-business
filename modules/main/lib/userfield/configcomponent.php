<?php

namespace Bitrix\Main\UserField;

use Bitrix\Main\Application;
use Bitrix\Main\Error;
use Bitrix\Main\ErrorCollection;
use Bitrix\Main\Localization\Loc;

abstract class ConfigComponent extends \CBitrixComponent
{
	public const ERROR_CODE_NO_MODULE_ERROR = 'UF_CONFIG_NO_MODULE';

	/** @var \Bitrix\Main\UserField\UserFieldAccess */
	protected $access;
	protected $entityId = '';
	protected $moduleId;
	/** @var ErrorCollection */
	protected $errorCollection;
	protected $userTypes;

	protected function init(): void
	{
		$this->errorCollection = new ErrorCollection();

		$request = Application::getInstance()->getContext()->getRequest();
		$this->entityId = $this->arParams['entityId'] ?: $request->get('entityId');
		$this->moduleId = $this->arParams['moduleId'] ?: $request->get('moduleId');

		if(!$this->moduleId)
		{
			$this->errorCollection[] = $this->getNoModuleError();
			return;
		}
		$this->access = UserFieldAccess::getInstance($this->moduleId);
		if(!$this->access)
		{
			$this->errorCollection[] = $this->getAccessDeniedError();
			return;
		}
	}

	protected function getNoModuleError(): Error
	{
		return new Error(Loc::getMessage('MAIN_USER_FIELD_CONFIG_COMPONENT_NO_MODULE_ERROR'), static::ERROR_CODE_NO_MODULE_ERROR);
	}

	protected function getAccessDeniedError(): Error
	{
		return new Error(Loc::getMessage('MAIN_USER_FIELD_CONFIG_COMPONENT_ACCESS_DENIED_ERROR'), static::ERROR_CODE_NO_MODULE_ERROR);
	}

	protected function getUserTypes(): array
	{
		if(!$this->userTypes)
		{
			global $USER_FIELD_MANAGER;
			$this->userTypes = $USER_FIELD_MANAGER->GetUserType();
			$restrictedTypes = $this->access->getRestrictedTypes();
			foreach($restrictedTypes as $typeId)
			{
				unset($this->userTypes[$typeId]);
			}
		}

		return $this->userTypes;
	}

	protected function setTitle(string $title): void
	{
		global $APPLICATION;

		$APPLICATION->SetTitle($title);
	}
}