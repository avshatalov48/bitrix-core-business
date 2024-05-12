<?php
namespace Bitrix\UI\Form;

use Bitrix\Main;
use Bitrix\Ui\EntityForm\Scope;

class EntityEditorConfiguration
{
	protected $categoryName;

	public static function canEditOtherSettings(): bool
	{
		return Main\Engine\CurrentUser::get()->canDoOperation('edit_other_settings');
	}

	public function __construct(string $categoryName = null)
	{
		$this->categoryName = $categoryName;
	}

	protected function getCategoryName(): string
	{
		if(empty($this->categoryName))
		{
			return 'ui.form.editor';
		}

		return $this->categoryName;
	}

	protected function prepareName(string $configID, string $scope): string
	{
		if($scope === EntityEditorConfigScope::COMMON)
		{
			return "{$configID}_common";
		}

		return $configID;
	}

	protected function prepareScopeName(string $configID): string
	{
		return "{$configID}_scope";
	}

	public static function prepareOptionsName(string $configID, string $scope, int $userScopeId = 0): string
	{
		$configID = mb_strtolower($configID);
		if($scope === EntityEditorConfigScope::COMMON)
		{
			return "{$configID}_common_opts";
		}
		if($scope === EntityEditorConfigScope::CUSTOM)
		{
			return "{$configID}_custom_opts_" . $userScopeId;
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

		$userScopeId = (int)($params['userScopeId'] ?? 0);

		$forAllUsers = isset($params['forAllUsers'])
			&& $params['forAllUsers'] === 'Y'
			&& self::canEditOtherSettings()
		;

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
		elseif($scope === EntityEditorConfigScope::PERSONAL)
		{
			\CUserOptions::SetOption($categoryName, $configID, $config);
		}
		elseif($userScopeId > 0)
		{
			Scope::getInstance()->updateScopeConfig(
				$userScopeId,
				$config
			);
		}

		$options = $params['options'] ?? null;
		if(is_array($options))
		{
			$optionName = static::prepareOptionsName($configID, $scope, $userScopeId);
			if($scope === EntityEditorConfigScope::COMMON)
			{
				\CUserOptions::SetOption(
					$categoryName,
					$optionName,
					$options,
					true
				);
			}
			else
			{
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
			//todo check what to do with options for custom scopes
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
				static::prepareOptionsName($configID, $scope),
				true,
				0
			);
		}
		else
		{
			if($forAllUsers)
			{
				\CUserOptions::DeleteOptionsByName($categoryName, $this->prepareName($configID, $scope));
				\CUserOptions::DeleteOptionsByName($categoryName, static::prepareOptionsName($configID, $scope));
				\CUserOptions::DeleteOptionsByName($categoryName, $this->prepareScopeName($configID));
			}
			else
			{
				\CUserOptions::DeleteOption($categoryName, $this->prepareName($configID, $scope));
				\CUserOptions::DeleteOption($categoryName, static::prepareOptionsName($configID, $scope));

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