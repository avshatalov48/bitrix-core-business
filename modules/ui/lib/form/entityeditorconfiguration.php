<?php
namespace Bitrix\UI\Form;

use Bitrix\Main;
use Bitrix\Ui\EntityForm\Scope;

class EntityEditorConfiguration
{
	protected $categoryName;
	protected int $userId;

	private const AVAILABLE_SCOPE_TYPES = [
		'on_add',
		'on_update',
	];

	public static function canEditOtherSettings(): bool
	{
		return Main\Engine\CurrentUser::get()->canDoOperation('edit_other_settings');
	}

	public function __construct(string $categoryName = null, ?int $userId = null)
	{
		$this->categoryName = $categoryName;
		$this->userId = $userId ?? (($GLOBALS['USER'] instanceof \CUser) ? $GLOBALS['USER']->getId() : 0);
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

	public function getScope($configID, bool $scopeNamePrepared = false)
	{
		if (!$this->userId)
		{
			return EntityEditorConfigScope::UNDEFINED;
		}

		$scopeName = $configID;
		if (!$scopeNamePrepared)
		{
			$scopeName = $this->prepareScopeName($configID);
		}

		return \CUserOptions::GetOption(
			$this->getCategoryName(),
			$scopeName,
			EntityEditorConfigScope::UNDEFINED,
			$this->userId,
		);
	}

	public function get($configID, $scope)
	{
		if(!EntityEditorConfigScope::isDefined($scope))
		{
			return null;
		}

		if (!$this->userId)
		{
			return null;
		}

		return \CUserOptions::GetOption(
			$this->getCategoryName(),
			$this->prepareName($configID, $scope),
			null,
			$scope === EntityEditorConfigScope::COMMON ? 0 : $this->userId
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
			if ($this->userId)
			{
				\CUserOptions::SetOption($categoryName, $configID, $config, false, $this->userId);
			}

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
				if ($this->userId)
				{
					\CUserOptions::SetOption($categoryName, $optionName, $options, false, $this->userId);
				}
			}
			//todo check what to do with options for custom scopes
		}
	}

	public function reset($configID, array $params): void
	{
		$categoryName = $this->getCategoryName();

		$scope = isset($params['scope']) ? mb_strtoupper($params['scope']) : EntityEditorConfigScope::UNDEFINED;
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
				0,
			);
			\CUserOptions::DeleteOption(
				$categoryName,
				static::prepareOptionsName($configID, $scope),
				true,
				0,
			);
		}
		else
		{
			$scopeName = $this->prepareScopeName($configID);
			if (isset($params['type']) && $this->isAvailableScopeType((string)$params['type']))
			{
				$scopeName .= '_' . $params['type'];
			}

			if($forAllUsers)
			{
				\CUserOptions::DeleteOptionsByName($categoryName, $this->prepareName($configID, $scope));
				\CUserOptions::DeleteOptionsByName($categoryName, static::prepareOptionsName($configID, $scope));
				\CUserOptions::DeleteOptionsByName($categoryName, $scopeName);
			}
			elseif ($this->userId)
			{
				\CUserOptions::DeleteOption($categoryName, $this->prepareName($configID, $scope), false, $this->userId);
				\CUserOptions::DeleteOption($categoryName, static::prepareOptionsName($configID, $scope), false, $this->userId);

				\CUserOptions::SetOption(
					$categoryName,
					$scopeName,
					EntityEditorConfigScope::PERSONAL,
					false,
					$this->userId,
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

		if ($this->userId)
		{
			\CUserOptions::SetOption($this->getCategoryName(), $this->prepareScopeName($configID), $scope, false, $this->userId);
		}
	}

	public function forceCommonScopeForAll(string $configID, string $moduleId, ?string $type = null): void
	{
		$scopeNames = [
			$this->prepareScopeName($configID),
		];

		if ($type === 'on_add')
		{
			$scopeNames[] = Scope::getScopeNameOnAdd($moduleId, $configID);
		}

		if ($type === 'on_update')
		{
			$scopeNames[] = Scope::getScopeNameOnUpdate($moduleId, $configID);
		}
		$scopeNames = array_unique($scopeNames);

		$categoryName = $this->getCategoryName();

		\CUserOptions::DeleteOptionsByName(
			$categoryName,
			$this->prepareName($configID, EntityEditorConfigScope::PERSONAL),
		);
		foreach ($scopeNames as $scopeName)
		{
			\CUserOptions::DeleteOptionsByName($categoryName, $scopeName);
		}
	}

	private function isAvailableScopeType(string $type): bool
	{
		return in_array($type, self::AVAILABLE_SCOPE_TYPES, true);
	}
}