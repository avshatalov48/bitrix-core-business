<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

class BizprocDebuggerStartComponent extends CBitrixComponent
{
	public function onPrepareComponentParams($arParams)
	{
		if (isset($arParams['DOCUMENT_SIGNED']))
		{
			$arParams['DOCUMENT_SIGNED'] = htmlspecialcharsback($arParams['DOCUMENT_SIGNED']);
			$arParams['DOCUMENT_UNSIGNED'] = CBPDocument::unSignParameters($arParams['DOCUMENT_SIGNED']);
			$arParams['PARAMETER_DOCUMENT_TYPE'] = $arParams['DOCUMENT_UNSIGNED'] ? $arParams['DOCUMENT_UNSIGNED'][0] : null;
			$arParams['DOCUMENT_CATEGORY_ID'] = $arParams['DOCUMENT_UNSIGNED'] ? $arParams['DOCUMENT_UNSIGNED'][1] : null;
		}

		$arParams['SET_TITLE'] = ($arParams['SET_TITLE'] === 'N' ? 'N' : 'Y');

		return $arParams;
	}

	private function showError(string $message)
	{
		$this->arResult['errorMessage'] = $message;
		$this->includeComponentTemplate('error');

		return null;
	}

	public function executeComponent()
	{
		if (!$this->getParameterDocumentType())
		{
			return $this->showError(
				\Bitrix\Main\Localization\Loc::getMessage('BIZPROC_DEBUGGER_START_ERROR_DOCUMENT_TYPE')
			);
		}

		$this->includeModules();

		if (!$this->checkRights())
		{
			return $this->showError(
				\Bitrix\Main\Localization\Loc::getMessage('BIZPROC_DEBUGGER_START_ERROR_RIGHTS')
			);
		}

		$this->arResult = [
			'activeSession' => \Bitrix\Bizproc\Debugger\Session\Manager::getActiveSession(),
			'documentSigned' => $this->arParams['~DOCUMENT_SIGNED'],
			'currentUserId' => (int)(\Bitrix\Main\Engine\CurrentUser::get()->getId()),
		];

		return $this->includeComponentTemplate();
	}

	private function includeModules(): ?bool
	{
		if (!\Bitrix\Main\Loader::includeModule('bizproc'))
		{
			return static::showError(\Bitrix\Main\Localization\Loc::getMessage('BIZPROC_MODULE_NOT_INSTALLED'));
		}

		$module = $this->getModuleId();
		if (!\Bitrix\Main\Loader::includeModule($module))
		{
			$codeMessage = mb_strtoupper($module) . '_MODULE_NOT_INSTALLED';

			return static::showError(\Bitrix\Main\Localization\Loc::getMessage($codeMessage));
		}

		return true;
	}

	private function checkRights(): bool
	{
		return \Bitrix\Bizproc\Debugger\Session\Manager::canUserDebugAutomation(
			(int)(\Bitrix\Main\Engine\CurrentUser::get()->getId()),
			$this->getParameterDocumentType()
		);
	}

	private function getParameterDocumentType()
	{
		return $this->arParams['PARAMETER_DOCUMENT_TYPE'];
	}

	private function getModuleId()
	{
		[$moduleId, $entity, $documentId] = CBPHelper::ParseDocumentId($this->getParameterDocumentType());

		return $moduleId;
	}
}