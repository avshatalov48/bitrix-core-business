<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die;

use Bitrix\Main\Engine\Response\AjaxJson;
use Bitrix\Main\Event;
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
		array $params = [],
	) {
		if (
			($scopeAccess = ScopeAccess::getInstance($moduleId))
			&& $scopeAccess->canAddByEntityTypeId($entityTypeId)
		)
		{
			$preparedParams = [
				'forceSetToUsers' => $params['forceSetToUsers'] === 'true' ? 'Y' : 'N',
				'availableOnAdd' => $params['availableOnAdd'] === 'true' ? 'Y' : 'N',
				'availableOnUpdate' => $params['availableOnUpdate'] === 'true' ? 'Y' : 'N',
				'common' => ($params['common'] ?? 'Y') === 'Y' ? 'Y' : 'N',
				'categoryName' => (string)$params['categoryName'],
			];

			$result = Scope::getInstance()
				->setScopeConfig($moduleId, $entityTypeId, $name, $accessCodes, $config, $preparedParams)
			;

			return is_int($result) ? $result : AjaxJson::createError(null, $result);
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
	protected function emitOnUIFormSetScope(string $guid, string $scope, string $categoryName = '')
	{
		$event = new Event(
			'ui',
			'onUIFormSetScope',
			[
				'GUID' => $guid,
				'SCOPE' => $scope,
				'CATEGORY_NAME' => $categoryName,
			],
		);
		$event->send();
	}

	public function setScopeAction(
		string $moduleId,
		string $categoryName,
		string $guid,
		string $scope,
		int $userScopeId = 0,
		int $entityId = 0,
	): ?AjaxJson
	{
		if (
			$scope !== EntityEditorConfigScope::CUSTOM
			|| (
				($scopeAccess = ScopeAccess::getInstance($moduleId))
				&& $scopeAccess->canRead($userScopeId)
			)
		)
		{
			if ($entityId > 0)
			{
				Scope::getInstance()->setEditScope($moduleId, $categoryName, $guid, $scope, $userScopeId);
			}
			else
			{
				Scope::getInstance()->setCreateScope($moduleId, $categoryName, $guid, $scope, $userScopeId);
			}

			$this->emitOnUIFormSetScope($guid, $scope, $categoryName);

			return null;
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
		if (empty($accessCodes))
		{
			return AjaxJson::createError(null, [new \Bitrix\Main\Error('Invalid data')]);
		}

		if (
			($scopeAccess = ScopeAccess::getInstance($moduleId))
			&& $scopeAccess->canUpdate($scopeId)
		)
		{
			return Scope::getInstance()->updateScopeAccessCodes($scopeId, $accessCodes);
		}

		return $this->getAccessDenied();
	}

	public function copyScopeAction(string $moduleId, string $entityTypeId, int $scopeId): int|AjaxJson
	{
		if (
			($scopeAccess = ScopeAccess::getInstance($moduleId))
			&& $scopeAccess->canAddByEntityTypeId($entityTypeId)
		) {
			$result = Scope::getInstance()->copyScope($scopeId, $entityTypeId);

			return is_int($result) ? $result : AjaxJson::createError(null, $result);
		}

		return $this->getAccessDenied();
	}

	public function removeScopeAction(string $moduleId, int $scopeId): bool|AjaxJson
	{
		if (
			($scopeAccess = ScopeAccess::getInstance($moduleId))
			&& $scopeAccess->canDelete($scopeId)
		) {
			Scope::getInstance()->removeByIds([$scopeId]);

			return true;
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
