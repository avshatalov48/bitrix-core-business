<?php
namespace Bitrix\UI\Form;

use Bitrix\Main;
use Bitrix\UI;

class EntityEditorConfiguration
{
	public static function canEditOtherSettings()
	{
		return Main\Engine\CurrentUser::get()->canDoOperation('edit_other_settings');
	}

	protected function getCategoryName()
	{
		return 'ui.form.editor';
	}

	protected function prepareName($configID, $scope)
	{
		if($scope === EntityEditorConfigScope::COMMON)
		{
			return "{$configID}_common";
		}
		return $configID;
	}

	protected function prepareScopeName($configID)
	{
		return "{$configID}_scope";
	}

	protected function prepareOptionsName($configID, $scope)
	{
		if($scope === EntityEditorConfigScope::COMMON)
		{
			return "{$configID}_common_opts";
		}
		return "{$configID}_opts";
	}

	public function getScope($configID)
	{
		return \CUserOptions::GetOption(
			$this->getCategoryName(),
			$this->prepareScopeName($configID),
			EntityEditorConfigScope::UNDEFINED
		);
	}

	public function get($configID, $scope)
	{
		if(!EntityEditorConfigScope::isDefined($scope))
		{
			return null;
		}

		return \CUserOptions::GetOption(
			$this->getCategoryName(),
			$this->prepareName($configID, $scope),
			null,
			$scope === EntityEditorConfigScope::COMMON ? 0 : false
		);
	}

	public function set($configID, array $config, array $params)
	{
		$categoryName = $this->getCategoryName();

		$scope = isset($params['scope'])? mb_strtoupper($params['scope']) : EntityEditorConfigScope::UNDEFINED;
		if(!EntityEditorConfigScope::isDefined($scope))
		{
			$scope = EntityEditorConfigScope::PERSONAL;
		}

		$forAllUsers = self::canEditOtherSettings()
			&& isset($params['forAllUsers'])
			&& $params['forAllUsers'] === 'Y';

		if($forAllUsers)
		{
			if(isset($params['delete']) && $params['delete'] === 'Y')
			{
				\CUserOptions::DeleteOptionsByName($categoryName, $configID);
			}
			\CUserOptions::SetOption($categoryName, $configID, $config, true);
		}

		if($scope === EntityEditorConfigScope::COMMON)
		{
			\CUserOptions::SetOption(
				$categoryName,
				$this->prepareName($configID, $scope),
				$config,
				true
			);
		}
		else
		{
			\CUserOptions::SetOption($categoryName, $configID, $config);
		}

		$options = isset($params['options']) && is_array($params['options']) ? $params['options'] : array();
		if(!empty($options))
		{
			if($scope === EntityEditorConfigScope::COMMON)
			{
				\CUserOptions::SetOption(
					$categoryName,
					$this->prepareOptionsName($configID, $scope),
					$options,
					true
				);
			}
			else
			{
				$optionName = $this->prepareOptionsName($configID, $scope);
				if($forAllUsers)
				{
					if(isset($params['delete']) && $params['delete'] === 'Y')
					{
						\CUserOptions::DeleteOptionsByName($categoryName, $optionName);
					}
					\CUserOptions::SetOption($categoryName, $optionName, $options, true);
				}
				\CUserOptions::SetOption($categoryName, $optionName, $options);
			}
		}
	}
	public function reset($configID, array $params)
	{
		$categoryName = $this->getCategoryName();

		$scope = isset($params['scope'])? mb_strtoupper($params['scope']) : EntityEditorConfigScope::UNDEFINED;
		if(!EntityEditorConfigScope::isDefined($scope))
		{
			$scope = EntityEditorConfigScope::PERSONAL;
		}

		$forAllUsers = self::canEditOtherSettings()
			&& isset($params['forAllUsers'])
			&& $params['forAllUsers'] === 'Y';

		if($scope === EntityEditorConfigScope::COMMON)
		{
			\CUserOptions::DeleteOption(
				$categoryName,
				$this->prepareName($configID, $scope),
				true,
				0
			);
			\CUserOptions::DeleteOption(
				$categoryName,
				$this->prepareOptionsName($configID, $scope),
				true,
				0
			);
		}
		else
		{
			if($forAllUsers)
			{
				\CUserOptions::DeleteOptionsByName($categoryName, $this->prepareName($configID, $scope));
				\CUserOptions::DeleteOptionsByName($categoryName, $this->prepareOptionsName($configID, $scope));
				\CUserOptions::DeleteOptionsByName($categoryName, $this->prepareScopeName($configID));
			}
			else
			{
				\CUserOptions::DeleteOption($categoryName, $this->prepareName($configID, $scope));
				\CUserOptions::DeleteOption($categoryName, $this->prepareOptionsName($configID, $scope));

				\CUserOptions::SetOption(
					$categoryName,
					$this->prepareScopeName($configID),
					EntityEditorConfigScope::PERSONAL
				);
			}
		}

	}
	public function setScope($configID, $scope)
	{
		if(!EntityEditorConfigScope::isDefined($scope))
		{
			$scope = EntityEditorConfigScope::PERSONAL;
		}

		\CUserOptions::SetOption($this->getCategoryName(), $this->prepareScopeName($configID), $scope);
	}
	public function forceCommonScopeForAll($configID)
	{
		if(!self::canEditOtherSettings())
		{
			return;
		}

		$categoryName = $this->getCategoryName();

		\CUserOptions::DeleteOptionsByName(
			$categoryName,
			$this->prepareName($configID, EntityEditorConfigScope::PERSONAL)
		);
		\CUserOptions::DeleteOptionsByName($categoryName, $this->prepareScopeName($configID));
	}
}