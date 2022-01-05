<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Engine\Response\AjaxJson;
use Bitrix\Ui\EntityForm\Scope;
use Bitrix\Ui\EntityForm\ScopeAccess;
use Bitrix\UI\Form\EntityEditorConfigScope;

class UiFormConfigAjaxController extends \Bitrix\Main\Engine\Controller
{
	/**
	 * @param string $moduleId
	 * @param string $entityTypeId
	 * @param array $config
	 * @param string $name
	 * @param array $accessCodes
	 * @param array $params
	 * @return int|AjaxJson
	 */
	public function saveAction(
		string $moduleId,
		string $entityTypeId,
		array $config,
		string $name = '',
		array $accessCodes = [],
		array $params = []
	)
	{
		if (
			($scopeAccess = ScopeAccess::getInstance($moduleId))
			&& $scopeAccess->canAdd()
		)
		{
			$result = Scope::getInstance()
				->setScopeConfig($moduleId, $entityTypeId, $name, $accessCodes, $config, $params);

			return (is_int($result) ? $result : AjaxJson::createError(null, $result));
		}

		return $this->getAccessDenied();
	}

	/**
	 * @param string $moduleId
	 * @param string $guid
	 * @param string $scope
	 * @param int $userScopeId
	 * @return void|AjaxJson
	 */
	public function setScopeAction(string $moduleId, string $categoryName, string $guid, string $scope, int $userScopeId = 0)
	{
		if (
			$scope !== EntityEditorConfigScope::CUSTOM
			|| (
				($scopeAccess = ScopeAccess::getInstance($moduleId))
				&& $scopeAccess->canRead($userScopeId)
			)
		)
		{
			Scope::getInstance()->setScope($categoryName, $guid, $scope, $userScopeId);
			return;
		}

		return $this->getAccessDenied();
	}

	/**
	 * @param string $moduleId
	 * @param int $scopeId
	 * @param array $accessCodes
	 * @return array|AjaxJson
	 */
	public function updateScopeAccessCodesAction(string $moduleId, int $scopeId, array $accessCodes = [])
	{
		if (
			($scopeAccess = ScopeAccess::getInstance($moduleId))
			&& $scopeAccess->canUpdate($scopeId)
		)
		{
			return Scope::getInstance()->updateScopeAccessCodes($scopeId, $accessCodes);
		}

		return $this->getAccessDenied();
	}

	/**
	 * @return AjaxJson
	 */
	private function getAccessDenied(): AjaxJson
	{
		$result = [new \Bitrix\Main\Error('Access denied')];
		return AjaxJson::createError(null, $result);
	}
}
