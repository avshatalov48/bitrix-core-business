<?php
namespace Bitrix\Landing\Site;

use \Bitrix\Landing\Role;
use \Bitrix\Landing\Site;

class Type
{
	/**
	 * Scope group.
	 */
	const SCOPE_CODE_GROUP = 'GROUP';

	/**
	 * Scope knowledge.
	 */
	const SCOPE_CODE_KNOWLEDGE = 'KNOWLEDGE';

	/**
	 * Pseudo scope for crm forms.
	 */
	const PSEUDO_SCOPE_CODE_FORMS = 'crm_forms';

	/**
	 * Current scope class name.
	 * @var Scope
	 */
	protected static $currentScopeClass = null;

	/**
	 * Scope already init.
	 * @var bool
	 */
	protected static $scopeInit = false;

	/**
	 * Returns scope class, if exist.
	 * @param string $scope Scope code.
	 * @return string|null
	 */
	protected static function getScopeClass($scope)
	{
		$scope = trim($scope);
		$class = __NAMESPACE__ . '\\Scope\\' . $scope;
		if (class_exists($class))
		{
			return $class;
		}

		return null;
	}

	/**
	 * Set global scope.
	 * @param string $scope Scope code.
	 * @param array $params Additional params.
	 * @return void
	 */
	public static function setScope($scope, array $params = [])
	{
		//self::$scopeInit ||
		if (!is_string($scope) || !$scope)
		{
			return;
		}
		//if (self::$currentScopeClass === null)
		// always clear previous scope
		if (true)
		{
			Role::setExpectedType(null);
			self::$currentScopeClass = self::getScopeClass($scope);
			if (self::$currentScopeClass)
			{
				self::$scopeInit = true;
				self::$currentScopeClass::init($params);
			}
		}
	}

	/**
	 * Clear selected scope.
	 * @return void
	 */
	public static function clearScope()
	{
		self::$scopeInit = false;
		self::$currentScopeClass = null;
	}

	/**
	 * Returns true if scope is public.
	 * @param string|null $scope Scope code.
	 * @return bool
	 */
	public static function isPublicScope(?string $scope = null): bool
	{
		$scope = $scope ? mb_strtoupper($scope) : self::getCurrentScopeId();
		return !($scope === 'KNOWLEDGE' || $scope === 'GROUP');
	}

	/**
	 * Returns publication path string.
	 * @return string|null
	 */
	public static function getPublicationPath()
	{
		if (self::$currentScopeClass !== null)
		{
			return self::$currentScopeClass::getPublicationPath();
		}

		return null;
	}

	/**
	 * Return general key for site path (ID or CODE).
	 * @return string
	 */
	public static function getKeyCode()
	{
		if (self::$currentScopeClass !== null)
		{
			return self::$currentScopeClass::getKeyCode();
		}

		return 'ID';
	}

	/**
	 * Returns domain id for new site.
	 * @return int|string
	 */
	public static function getDomainId()
	{
		if (self::$currentScopeClass !== null)
		{
			return self::$currentScopeClass::getDomainId();
		}
		return '';
	}

	/**
	 * Returns current scope id.
	 * @return string|null
	 */
	public static function getCurrentScopeId()
	{
		if (self::$currentScopeClass !== null)
		{
			return self::$currentScopeClass::getCurrentScopeId();
		}
		return null;
	}

	/**
	 * Returns filter value for 'TYPE' key.
	 * @param bool $strict If strict, returns without default.
	 * @return string|string[]
	 */
	public static function getFilterType($strict = false)
	{
		if (self::$currentScopeClass !== null)
		{
			return self::$currentScopeClass::getFilterType();
		}

		// compatibility, huh
		return $strict ? null : ['PAGE', 'STORE', 'SMN'];
	}

	/**
	 * Returns array of hook's codes, which excluded by scope.
	 * @return array
	 */
	public static function getExcludedHooks(): array
	{
		if (self::$currentScopeClass !== null)
		{
			return self::$currentScopeClass::getExcludedHooks();
		}

		return [];
	}

	/**
	 * Returns true, if type is enabled in system.
	 * @param string $code Type code.
	 * @return bool
	 */
	public static function isEnabled($code)
	{
		if (is_string($code))
		{
			$code = mb_strtoupper(trim($code));
			$types = Site::getTypes();
			if (array_key_exists($code, $types))
			{
				return true;
			}
		}

		return false;
	}
}